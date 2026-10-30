<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\CartService;

beforeEach(function () {
    User::factory()->create();
});

test('it adds an item to a cart', function () {
    $cart = Cart::factory()->create();
    $product = Product::factory()->create(['price' => 1999, 'stock_quantity' => 10]);

    $service = app(CartService::class);
    $item = $service->addItem($cart, $product, null, 2);

    expect($item->product_id)->toBe($product->id)
        ->and($item->quantity)->toBe(2)
        ->and($item->unit_price)->toBe(1999)
        ->and($item->total_price)->toBe(3998);
});

test('it keeps cents when adding a product with decimal price', function () {
    $cart = Cart::factory()->create();
    $product = Product::factory()->create(['price' => 4388.84, 'stock_quantity' => 10]);

    $service = app(CartService::class);
    $item = $service->addItem($cart, $product, null, 2);

    expect($item->getRawOriginal('unit_price'))->toBe(438884)
        ->and($item->getRawOriginal('total_price'))->toBe(877768);
});

test('it updates existing item quantity when adding the same product', function () {
    $cart = Cart::factory()->create();
    $product = Product::factory()->create(['price' => 1000, 'stock_quantity' => 10]);

    $service = app(CartService::class);
    $service->addItem($cart, $product, null, 2);
    $service->addItem($cart, $product, null, 3);

    expect($cart->cartItems)->toHaveCount(1)
        ->and($cart->cartItems->first()->quantity)->toBe(5);
});

test('it validates stock when adding item', function () {
    $cart = Cart::factory()->create();
    $product = Product::factory()->create(['price' => 1000, 'stock_quantity' => 2]);

    $service = app(CartService::class);

    expect(fn () => $service->addItem($cart, $product, null, 5))
        ->toThrow(Exception::class, 'Insufficient stock');
});

test('it updates item quantity', function () {
    $cart = Cart::factory()->create();
    $product = Product::factory()->create(['price' => 1000, 'stock_quantity' => 10]);

    $service = app(CartService::class);
    $item = $service->addItem($cart, $product, null, 2);

    $updatedItem = $service->updateItem($item, 5);

    expect($updatedItem->quantity)->toBe(5)
        ->and($updatedItem->total_price)->toBe(5000);
});

test('it removes item when quantity is zero or less', function () {
    $cart = Cart::factory()->create();
    $product = Product::factory()->create(['price' => 1000, 'stock_quantity' => 10]);

    $service = app(CartService::class);
    $item = $service->addItem($cart, $product, null, 2);

    $service->updateItem($item, 0);

    expect(CartItem::withTrashed()->find($item->id))->not->toBeNull()
        ->and($cart->fresh()->cartItems)->toBeEmpty();
});

test('it removes an item from a cart', function () {
    $cart = Cart::factory()->create();
    $product = Product::factory()->create(['price' => 1000, 'stock_quantity' => 10]);

    $service = app(CartService::class);
    $item = $service->addItem($cart, $product);

    $service->removeItem($item);

    expect($cart->fresh()->cartItems)->toBeEmpty();
});

test('it clears all items from a cart', function () {
    $cart = Cart::factory()->create();
    $product1 = Product::factory()->create(['price' => 1000, 'stock_quantity' => 10]);
    $product2 = Product::factory()->create(['price' => 2000, 'stock_quantity' => 10]);

    $service = app(CartService::class);
    $service->addItem($cart, $product1);
    $service->addItem($cart, $product2);

    $service->clear($cart);

    expect($cart->fresh()->cartItems)->toBeEmpty();
});

test('it merges two carts', function () {
    $cart1 = Cart::factory()->create();
    $cart2 = Cart::factory()->create();

    $product = Product::factory()->create(['price' => 1000, 'stock_quantity' => 20]);

    $service = app(CartService::class);
    $service->addItem($cart1, $product, null, 2);
    $service->addItem($cart2, $product, null, 3);

    $service->merge($cart2, $cart1);

    expect($cart1->fresh()->cartItems)->toHaveCount(1)
        ->and($cart1->fresh()->cartItems->first()->quantity)->toBe(5);
});

test('it converts cart to order', function () {
    $cart = Cart::factory()->create();
    $product = Product::factory()->create(['price' => 1000, 'stock_quantity' => 10]);

    $service = app(CartService::class);
    $service->addItem($cart, $product, null, 2);

    $order = $service->convertToOrder($cart);

    expect($order->subtotal)->toBe(2000)
        ->and(OrderItem::where('order_id', $order->id)->count())->toBe(1)
        ->and($cart->fresh()->cartItems)->toBeEmpty();
});

test('it cleans up expired carts', function () {
    Cart::factory()->expired()->create();
    Cart::factory()->create();

    $service = app(CartService::class);
    $deleted = $service->cleanupExpired();

    expect($deleted)->toBe(1);
});

test('it gets or creates a cart for a user', function () {
    $user = User::factory()->create();

    $service = app(CartService::class);
    $cart = $service->getOrCreateCart($user);

    expect($cart->user_id)->toBe($user->id)
        ->and($cart->is_guest)->toBeFalse();
});

test('it gets cart by token', function () {
    $cart = Cart::factory()->create();

    $service = app(CartService::class);
    $found = $service->getByToken($cart->cart_token);

    expect($found->id)->toBe($cart->id);
});

test('it adds item with variant', function () {
    $cart = Cart::factory()->create();
    $product = Product::factory()->create(['price' => 1000, 'stock_quantity' => 10]);
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 1500, 'stock_quantity' => 5]);

    $service = app(CartService::class);
    $item = $service->addItem($cart, $product, $variant);

    expect($item->product_variant_id)->toBe($variant->id)
        ->and($item->unit_price)->toBe(1500);
});

test('it validates variant stock when adding item', function () {
    $cart = Cart::factory()->create();
    $product = Product::factory()->create(['price' => 1000, 'stock_quantity' => 100]);
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 1500, 'stock_quantity' => 2]);

    $service = app(CartService::class);

    expect(fn () => $service->addItem($cart, $product, $variant, 5))
        ->toThrow(Exception::class, 'Insufficient stock');
});

test('it considers reserved quantity when validating stock', function () {
    $cart = Cart::factory()->create();
    $product = Product::factory()->create(['price' => 1000, 'stock_quantity' => 5]);

    $service = app(CartService::class);
    $service->addItem($cart, $product, null, 3);

    expect(fn () => $service->addItem($cart, $product, null, 3))
        ->toThrow(Exception::class, 'Insufficient stock');
});

test('it creates a new guest cart when cookie token is missing', function () {
    $service = app(CartService::class);
    $cart = $service->getOrCreateCart();

    expect($cart->is_guest)->toBeTrue()
        ->and($cart->cart_token)->not->toBeNull();
});

test('it returns existing guest cart when cookie token matches', function () {
    $cart = Cart::factory()->guest()->create();

    $service = app(CartService::class);
    request()->cookies->set('cart_token', $cart->cart_token);

    $found = $service->getOrCreateCart();

    expect($found->id)->toBe($cart->id);
});

test('it creates a new cart when guest cart is expired', function () {
    $expiredCart = Cart::factory()->expired()->create();

    $service = app(CartService::class);
    request()->cookies->set('cart_token', $expiredCart->cart_token);

    $newCart = $service->getOrCreateCart();

    expect($newCart->id)->not->toBe($expiredCart->id)
        ->and($newCart->isExpired)->toBeFalse();
});

test('it gets or creates an existing user cart', function () {
    $user = User::factory()->create();
    $existingCart = Cart::factory()->create([
        'user_id' => $user->id,
        'is_guest' => false,
    ]);

    $service = app(CartService::class);
    $cart = $service->getOrCreateCart($user);

    expect($cart->id)->toBe($existingCart->id)
        ->and($cart->user_id)->toBe($user->id);
});

test('it converts cart to order with custom data', function () {
    $cart = Cart::factory()->create();
    $product = Product::factory()->create(['price' => 1000, 'stock_quantity' => 10]);

    $service = app(CartService::class);
    $service->addItem($cart, $product, null, 2);

    $order = $service->convertToOrder($cart, [
        'notes' => 'Rush order',
        'payment_method' => 'cash',
    ]);

    expect($order->notes)->toBe('Rush order')
        ->and($order->payment_method)->toBe('cash')
        ->and($order->orderItems)->toHaveCount(1);
});

test('it uses product price when variant has no price', function () {
    $cart = Cart::factory()->create();
    $product = Product::factory()->create(['price' => 1000, 'stock_quantity' => 10]);
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => null, 'stock_quantity' => 5]);

    $service = app(CartService::class);
    $item = $service->addItem($cart, $product, $variant);

    expect($item->unit_price)->toBe(1000);
});
