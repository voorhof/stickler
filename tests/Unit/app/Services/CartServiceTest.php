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

test('it updates existing item quantity when adding the same product', function () {
    $cart = Cart::factory()->create();
    $product = Product::factory()->create(['price' => 1000, 'stock_quantity' => 10]);

    $service = app(CartService::class);
    $service->addItem($cart, $product, null, 2);
    $service->addItem($cart, $product, null, 3);

    expect($cart->items)->toHaveCount(1)
        ->and($cart->items->first()->quantity)->toBe(5);
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
        ->and($cart->fresh()->items)->toBeEmpty();
});

test('it removes an item from a cart', function () {
    $cart = Cart::factory()->create();
    $product = Product::factory()->create(['price' => 1000, 'stock_quantity' => 10]);

    $service = app(CartService::class);
    $item = $service->addItem($cart, $product);

    $service->removeItem($item);

    expect($cart->fresh()->items)->toBeEmpty();
});

test('it clears all items from a cart', function () {
    $cart = Cart::factory()->create();
    $product1 = Product::factory()->create(['price' => 1000, 'stock_quantity' => 10]);
    $product2 = Product::factory()->create(['price' => 2000, 'stock_quantity' => 10]);

    $service = app(CartService::class);
    $service->addItem($cart, $product1);
    $service->addItem($cart, $product2);

    $service->clear($cart);

    expect($cart->fresh()->items)->toBeEmpty();
});

test('it merges two carts', function () {
    $cart1 = Cart::factory()->create();
    $cart2 = Cart::factory()->create();

    $product = Product::factory()->create(['price' => 1000, 'stock_quantity' => 20]);

    $service = app(CartService::class);
    $service->addItem($cart1, $product, null, 2);
    $service->addItem($cart2, $product, null, 3);

    $service->merge($cart2, $cart1);

    expect($cart1->fresh()->items)->toHaveCount(1)
        ->and($cart1->fresh()->items->first()->quantity)->toBe(5);
});

test('it converts cart to order', function () {
    $cart = Cart::factory()->create();
    $product = Product::factory()->create(['price' => 1000, 'stock_quantity' => 10]);

    $service = app(CartService::class);
    $service->addItem($cart, $product, null, 2);

    $order = $service->convertToOrder($cart);

    expect($order->subtotal)->toBe(2000)
        ->and(OrderItem::where('order_id', $order->id)->count())->toBe(1)
        ->and($cart->fresh()->items)->toBeEmpty();
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
