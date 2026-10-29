<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

class CartService
{
    /**
     * Default cart expiration in days.
     */
    protected int $defaultExpirationDays = 30;

    /**
     * Cache for cart lookups within a single request to avoid duplicate queries.
     */
    protected ?array $cartCache = null;

    /**
     * Get or create a cart for the given user or a guest cart.
     */
    public function getOrCreateCart(?User $user = null): Cart
    {
        if ($user) {
            return Cart::firstOrCreate(
                ['user_id' => $user->id, 'is_guest' => false],
                ['expires_at' => now()->addDays($this->defaultExpirationDays)],
            );
        }

        $cartToken = request()->cookie('cart_token');

        if ($cartToken) {
            $cart = $this->getByToken($cartToken);

            if ($cart && ! $cart->isExpired) {
                return $cart;
            }
        }

        return Cart::create([
            'is_guest' => true,
            'expires_at' => now()->addDays($this->defaultExpirationDays),
        ]);
    }

    /**
     * Find the current non-expired cart without creating one.
     */
    public function findCurrentCart(?User $user = null): ?Cart
    {
        if ($user) {
            return Cart::where('user_id', $user->id)
                ->where('is_guest', false)
                ->first();
        }

        $cartToken = request()->cookie('cart_token');

        if (! $cartToken) {
            return null;
        }

        $cart = $this->getByToken($cartToken);

        return $cart && ! $cart->isExpired ? $cart : null;
    }

    /**
     * Get a cart by its token, cached within the request lifecycle.
     */
    public function getByToken(string $cartToken): ?Cart
    {
        if ($this->cartCache === null) {
            $this->cartCache = ['token' => $cartToken, 'cart' => Cart::where('cart_token', $cartToken)->first()];
        }

        if ($this->cartCache['token'] !== $cartToken) {
            return Cart::where('cart_token', $cartToken)->first();
        }

        return $this->cartCache['cart'];
    }

    /**
     * Add an item to the cart with stock validation.
     *
     * @throws Exception If insufficient stock is available.
     */
    public function addItem(Cart $cart, Product $product, ?ProductVariant $variant = null, int $quantity = 1): CartItem
    {
        $this->validateStock($product, $variant, $quantity, $cart);

        $existingItem = $cart->items()
            ->where('product_id', $product->id)
            ->where('product_variant_id', $variant?->id)
            ->first();

        if ($existingItem) {
            $existingItem->quantity += $quantity;
            $existingItem->total_price = $existingItem->unit_price * $existingItem->quantity;
            $existingItem->save();

            return $existingItem;
        }

        $unitPrice = $this->resolveUnitPrice($product, $variant);

        return CartItem::forceCreate([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant?->id,
            'product_name' => $product->title,
            'product_variant_label' => $variant?->label,
            'sku' => $variant?->sku ?? $product->sku,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $unitPrice * $quantity,
        ]);
    }

    /**
     * Update a cart item's quantity with stock validation.
     *
     * @throws Exception If insufficient stock is available.
     */
    public function updateItem(CartItem $item, int $quantity): CartItem
    {
        if ($quantity <= 0) {
            $this->removeItem($item);

            return $item;
        }

        $product = $item->product;
        $variant = $item->productVariant;

        if ($product) {
            $this->validateStock($product, $variant, $quantity, $item->cart);
        }

        $item->quantity = $quantity;
        $item->total_price = $item->unit_price * $quantity;
        $item->save();

        return $item;
    }

    /**
     * Remove an item from the cart.
     */
    public function removeItem(CartItem $item): void
    {
        $item->delete();
    }

    /**
     * Clear all items from a cart.
     */
    public function clear(Cart $cart): void
    {
        $cart->items()->delete();
    }

    /**
     * Merge two carts, combining quantities for matching items.
     */
    public function merge(Cart $source, Cart $target): void
    {
        $target->merge($source);
    }

    /**
     * Convert a cart to an order.
     *
     * @throws Throwable
     */
    public function convertToOrder(Cart $cart, array $data = []): Order
    {
        return DB::transaction(function () use ($cart, $data) {
            $order = Order::create(array_merge([
                'user_id' => $cart->user_id,
                'subtotal' => $cart->items->sum('total_price'),
                'tax_amount' => 0,
                'shipping_amount' => 0,
            ], $data));

            /** @var Collection<CartItem> $items */
            $items = $cart->items;
            foreach ($items as $cartItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'product_variant_id' => $cartItem->product_variant_id,
                    'product_name' => $cartItem->product_name,
                    'product_variant_label' => $cartItem->product_variant_label,
                    'sku' => $cartItem->sku,
                    'quantity' => $cartItem->quantity,
                    'unit_price' => $cartItem->unit_price,
                    'total_price' => $cartItem->total_price,
                ]);
            }

            $cart->items()->delete();

            return $order;
        });
    }

    /**
     * Delete all expired carts.
     */
    public function cleanupExpired(): int
    {
        return Cart::expired()->delete();
    }

    /**
     * Delete all carts that have not been updated within the given number of days.
     */
    public function cleanupOlderThan(int $maxAgeInDays): int
    {
        return Cart::where('updated_at', '<', now()->subDays($maxAgeInDays))->delete();
    }

    /**
     * Validate that sufficient stock is available for the given quantity.
     *
     * @throws Exception If insufficient stock is available.
     */
    protected function validateStock(Product $product, ?ProductVariant $variant, int $quantity, ?Cart $cart = null): void
    {
        $stockQuantity = $variant?->stock_quantity ?? $product->stock_quantity;

        $reservedQuantity = $this->getReservedQuantity($product, $variant, $cart);

        if ($stockQuantity - $reservedQuantity < $quantity) {
            throw new Exception("Insufficient stock for $product->title. Available: ".($stockQuantity - $reservedQuantity).", Requested: $quantity.");
        }
    }

    /**
     * Get the quantity already reserved in the cart for the given product/variant.
     */
    protected function getReservedQuantity(Product $product, ?ProductVariant $variant, ?Cart $cart = null): int
    {
        return (int) $cart?->items()
            ->where('product_id', $product->id)
            ->where('product_variant_id', $variant?->id)
            ->sum('quantity');

    }

    /**
     * Resolve the unit price for a product, optionally using a variant's price.
     */
    protected function resolveUnitPrice(Product $product, ?ProductVariant $variant = null): float
    {
        if ($variant && $variant->price !== null) {
            return (float) $variant->price;
        }

        return $product->price;
    }
}
