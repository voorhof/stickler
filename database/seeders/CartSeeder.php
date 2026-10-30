<?php

/** @noinspection PhpUnhandledExceptionInspection */

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Random\RandomException;

class CartSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::isPublicPublished()->with('variants.options')->get();
        $users = User::all();

        if ($products->isEmpty()) {
            return;
        }

        // Active user carts (3)
        $this->createCartsWithItems(3, $products, $users, []);

        // Guest carts (2)
        $this->createGuestCartsWithItems(2, $products);

        // Expired carts (2)
        $this->createExpiredCartsWithItems(2, $products, $users);
    }

    /**
     * Create carts with items for registered users.
     */
    protected function createCartsWithItems(int $count, $products, $users, array $states): void
    {
        for ($i = 0; $i < $count; $i++) {
            $factory = Cart::factory();

            foreach ($states as $state) {
                $factory = $factory->{$state}();
            }

            $userId = $users->random()->id;

            $cart = $factory->create([
                'user_id' => $userId,
                'is_guest' => false,
                'created_by_user_id' => $userId,
                'updated_by_user_id' => $userId,
            ]);

            $itemCount = random_int(1, 4);
            for ($j = 0; $j < $itemCount; $j++) {
                $cart->items()->create($this->cartItemAttributes($userId));
            }

            $this->assignProductsToItems($cart->load('items'), $products);
        }
    }

    /**
     * Create guest carts without a user.
     */
    protected function createGuestCartsWithItems(int $count, $products): void
    {
        for ($i = 0; $i < $count; $i++) {
            $cart = Cart::factory()->guest()->create();

            $itemCount = random_int(1, 3);
            for ($j = 0; $j < $itemCount; $j++) {
                $cart->items()->create($this->cartItemAttributes());
            }

            $this->assignProductsToItems($cart->load('items'), $products);
        }
    }

    /**
     * Create expired carts.
     */
    protected function createExpiredCartsWithItems(int $count, $products, $users): void
    {
        for ($i = 0; $i < $count; $i++) {
            $factory = Cart::factory()->expired();

            $userId = $users->random()->id;

            $cart = $factory->create([
                'user_id' => $userId,
                'is_guest' => false,
                'created_by_user_id' => $userId,
                'updated_by_user_id' => $userId,
            ]);

            $itemCount = random_int(1, 3);
            for ($j = 0; $j < $itemCount; $j++) {
                $cart->items()->create($this->cartItemAttributes($userId));
            }

            $this->assignProductsToItems($cart->load('items'), $products);
        }
    }

    /**
     * Generate random cart item attributes without nested factories.
     *
     * @return array<string, mixed>
     *
     * @throws RandomException
     */
    protected function cartItemAttributes($userId = null): array
    {
        $quantity = random_int(1, 10);
        $unitPrice = random_int(1000, 50000);

        return [
            'product_id' => null,
            'product_variant_id' => null,
            'product_name' => fake()->words(3, true),
            'product_variant_label' => null,
            'sku' => fake()->ean8(),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $unitPrice * $quantity,
            'created_by_user_id' => $userId,
            'updated_by_user_id' => $userId,
        ];
    }

    /**
     * Assign existing products to cart items with snapshot data.
     */
    protected function assignProductsToItems(Cart $cart, $products): void
    {
        $cart->items->each(function (CartItem $item) use ($products) {
            $product = $products->random();

            $item->update([
                'product_id' => $product->id,
                'product_name' => $product->title,
                'unit_price' => $product->price,
                'sku' => $product->sku,
                'total_price' => $product->price * $item->quantity,
            ]);

            if ($product->variants->isNotEmpty()) {
                $variant = $product->variants->random();
                $label = $variant->options->pluck('name')->implode(' / ');
                $item->update([
                    'product_variant_id' => $variant->id,
                    'product_variant_label' => $label ?: null,
                ]);
            }
        });
    }
}
