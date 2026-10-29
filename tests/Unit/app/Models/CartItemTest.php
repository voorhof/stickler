<?php

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Observers\CartItemObserver;
use App\Policies\CartItemPolicy;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    User::factory()->create();
});

test('a cart item can be created', function () {
    $cart = Cart::factory()->create();
    $product = Product::factory()->create();

    $item = CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
    ]);

    expect($item->cart_id)->toBe($cart->id)
        ->and($item->product_id)->toBe($product->id);
});

test('cart item belongs to cart', function () {
    $cart = Cart::factory()->create();
    $item = CartItem::factory()->create(['cart_id' => $cart->id]);

    expect($item->cart)->not->toBeNull()
        ->and($item->cart->id)->toBe($cart->id);
});

test('cart item belongs to product', function () {
    $product = Product::factory()->create();
    $item = CartItem::factory()->create(['product_id' => $product->id]);

    expect($item->product)->not->toBeNull()
        ->and($item->product->id)->toBe($product->id);
});

test('cart item product can be null', function () {
    $item = CartItem::factory()->create(['product_id' => null]);

    expect($item->product_id)->toBeNull()
        ->and($item->product)->toBeNull();
});

test('cart item belongs to product variant', function () {
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create(['product_id' => $product->id]);
    $item = CartItem::factory()->withVariant()->create(['product_variant_id' => $variant->id]);

    expect($item->productVariant)->not->toBeNull()
        ->and($item->productVariant->id)->toBe($variant->id);
});

test('cart item product variant can be null', function () {
    $item = CartItem::factory()->create(['product_variant_id' => null]);

    expect($item->product_variant_id)->toBeNull()
        ->and($item->productVariant)->toBeNull();
});

test('unit price accessor converts cents to euros', function () {
    DB::table('cart_items')->insert([
        'cart_id' => Cart::factory()->create()->id,
        'product_name' => 'Test Product',
        'quantity' => 1,
        'unit_price' => 1999,
        'total_price' => 1999,
        'updated_at' => now(),
        'created_at' => now(),
    ]);
    $item = CartItem::where('product_name', 'Test Product')->first();

    expect($item->unit_price)->toBe(19.99);
});

test('unit price mutator converts euros to cents', function () {
    $item = CartItem::factory()->create();
    $item->unit_price = 25.50;
    $item->save();

    expect($item->getAttributes()['unit_price'])->toBe(2550);
});

test('total price accessor converts cents to euros', function () {
    DB::table('cart_items')->insert([
        'cart_id' => Cart::factory()->create()->id,
        'product_name' => 'Total Test',
        'quantity' => 2,
        'unit_price' => 1000,
        'total_price' => 2000,
        'updated_at' => now(),
        'created_at' => now(),
    ]);
    $item = CartItem::where('product_name', 'Total Test')->first();

    expect($item->total_price)->toBe(20);
});

test('total price mutator converts euros to cents', function () {
    $item = CartItem::factory()->create();
    $item->total_price = 12.34;
    $item->save();

    expect($item->getAttributes()['total_price'])->toBe(1234);
});

test('quantity is cast to integer', function () {
    $item = CartItem::factory()->create(['quantity' => 3]);

    expect($item->quantity)->toBeInt()
        ->and($item->quantity)->toBe(3);
});

test('cart item uses observer', function () {
    $reflectionClass = new ReflectionClass(CartItem::class);
    $attributes = $reflectionClass->getAttributes(ObservedBy::class);

    expect($attributes)->not->toBeEmpty()
        ->and($attributes[0]->getArguments()[0])->toBe(CartItemObserver::class);
});

test('cart item uses policy', function () {
    $reflectionClass = new ReflectionClass(CartItem::class);
    $attributes = $reflectionClass->getAttributes(UsePolicy::class);

    expect($attributes)->not->toBeEmpty()
        ->and($attributes[0]->getArguments()[0])->toBe(CartItemPolicy::class);
});

test('cart item supports soft deletes', function () {
    $item = CartItem::factory()->create();
    $item->delete();

    expect(CartItem::withTrashed()->find($item->id))->not->toBeNull()
        ->and(CartItem::find($item->id))->toBeNull();
});
