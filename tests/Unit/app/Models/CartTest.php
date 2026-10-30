<?php

/** @noinspection PhpPossiblePolymorphicInvocationInspection */

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use App\Observers\CartObserver;
use App\Policies\CartPolicy;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;

beforeEach(function () {
    User::factory()->create();
});

test('a cart can be created', function () {
    $cart = Cart::factory()->create();

    expect($cart->cart_token)->not->toBeNull()
        ->and($cart->is_guest)->toBeTrue()
        ->and($cart->expires_at)->not->toBeNull();
});

test('cart token is auto generated on creation', function () {
    $cart = Cart::factory()->create(['cart_token' => null]);

    expect($cart->cart_token)->not->toBeNull()
        ->and($cart->cart_token)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i');
});

test('cart token is unique', function () {
    $cart1 = Cart::factory()->create();
    $cart2 = Cart::factory()->create();

    expect($cart1->cart_token)->not->toBe($cart2->cart_token);
});

test('cart belongs to user', function () {
    $user = User::factory()->create();
    $cart = Cart::factory()->forUser()->create(['user_id' => $user->id]);

    expect($cart->user)->not->toBeNull()
        ->and($cart->user->id)->toBe($user->id)
        ->and($cart->is_guest)->toBeFalse();
});

test('cart user can be null for guest carts', function () {
    $cart = Cart::factory()->guest()->create();

    expect($cart->user_id)->toBeNull()
        ->and($cart->user)->toBeNull()
        ->and($cart->is_guest)->toBeTrue();
});

test('cart has many items', function () {
    $cart = Cart::factory()->create();
    CartItem::factory()->count(3)->create(['cart_id' => $cart->id]);

    expect($cart->cartItems)->toHaveCount(3);
});

test('cart items are deleted when cart is deleted', function () {
    $cart = Cart::factory()->create();
    CartItem::factory()->count(2)->create(['cart_id' => $cart->id]);

    $cart->delete();

    expect(CartItem::withTrashed()->where('cart_id', $cart->id)->count())->toBe(0);
});

test('cart total returns sum of item total prices', function () {
    $cart = Cart::factory()->create();
    CartItem::factory()->create(['cart_id' => $cart->id, 'unit_price' => 10.00, 'quantity' => 2, 'total_price' => 20.00]);
    CartItem::factory()->create(['cart_id' => $cart->id, 'unit_price' => 5.00, 'quantity' => 1, 'total_price' => 5.00]);

    expect($cart->total)->toBe(25);
});

test('cart total quantity returns sum of item quantities', function () {
    $cart = Cart::factory()->create();
    CartItem::factory()->create(['cart_id' => $cart->id, 'quantity' => 2]);
    CartItem::factory()->create(['cart_id' => $cart->id, 'quantity' => 3]);

    expect($cart->totalQuantity())->toBe(5);
});

test('cart is empty when it has no items', function () {
    $cart = Cart::factory()->create();

    expect($cart->isEmpty())->toBeTrue();
});

test('cart is not empty when it has items', function () {
    $cart = Cart::factory()->create();
    CartItem::factory()->create(['cart_id' => $cart->id]);

    expect($cart->isEmpty())->toBeFalse();
});

test('cart is expired when expires at is in the past', function () {
    $cart = Cart::factory()->expired()->create();

    expect($cart->isExpired)->toBeTrue();
});

test('cart is not expired when expires at is in the future', function () {
    $cart = Cart::factory()->create();

    expect($cart->isExpired)->toBeFalse();
});

test('cart is not expired when expires at is null', function () {
    $cart = Cart::factory()->create(['expires_at' => null]);

    expect($cart->isExpired)->toBeFalse();
});

test('expired scope returns only expired carts', function () {
    Cart::factory()->expired()->create();
    Cart::factory()->create();

    expect(Cart::expired()->count())->toBe(1);
});

test('cart merge combines items from another cart', function () {
    $cart1 = Cart::factory()->create();
    $cart2 = Cart::factory()->create();

    $product = Product::factory()->create();
    CartItem::factory()->create(['cart_id' => $cart1->id, 'product_id' => $product->id, 'quantity' => 2, 'unit_price' => 1000, 'total_price' => 2000]);
    CartItem::factory()->create(['cart_id' => $cart2->id, 'product_id' => $product->id, 'quantity' => 1, 'unit_price' => 1000, 'total_price' => 1000]);

    $cart1->merge($cart2);
    $cart1->refresh();

    expect($cart1->cartItems)->toHaveCount(1)
        ->and($cart1->cartItems->first()->quantity)->toBe(3);
});

test('cart merge moves unique items', function () {
    $cart1 = Cart::factory()->create();
    $cart2 = Cart::factory()->create();

    $product1 = Product::factory()->create();
    $product2 = Product::factory()->create();

    CartItem::factory()->create(['cart_id' => $cart1->id, 'product_id' => $product1->id, 'quantity' => 1, 'unit_price' => 1000, 'total_price' => 1000]);
    CartItem::factory()->create(['cart_id' => $cart2->id, 'product_id' => $product2->id, 'quantity' => 1, 'unit_price' => 2000, 'total_price' => 2000]);

    $cart1->merge($cart2);
    $cart1->refresh();

    expect($cart1->cartItems)->toHaveCount(2);
});

test('cart uses observer', function () {
    $reflectionClass = new ReflectionClass(Cart::class);
    $attributes = $reflectionClass->getAttributes(ObservedBy::class);

    expect($attributes)->not->toBeEmpty()
        ->and($attributes[0]->getArguments()[0])->toBe(CartObserver::class);
});

test('cart uses policy', function () {
    $reflectionClass = new ReflectionClass(Cart::class);
    $attributes = $reflectionClass->getAttributes(UsePolicy::class);

    expect($attributes)->not->toBeEmpty()
        ->and($attributes[0]->getArguments()[0])->toBe(CartPolicy::class);
});

test('is guest is cast to boolean', function () {
    $cart = Cart::factory()->create();

    expect(is_bool($cart->is_guest))->toBeTrue();
});

test('expires at is cast to datetime', function () {
    $cart = Cart::factory()->create();

    expect($cart->expires_at)->toBeInstanceOf(DateTimeInterface::class);
});

test('customer data is cast to array', function () {
    $cart = Cart::factory()->create([
        'customer_data' => [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ],
    ]);

    expect($cart->customer_data)->toBeArray()
        ->and($cart->customer_data['name'])->toBe('John Doe');
});

test('customer data is null when not set', function () {
    $cart = Cart::factory()->create(['customer_data' => null]);

    expect($cart->customer_data)->toBeNull();
});

test('customer name accessor returns name from customer data', function () {
    $cart = Cart::factory()->create(['customer_data' => ['name' => 'John Doe']]);

    expect($cart->customerName())->toBe('John Doe');
});

test('customer name accessor returns null when not set', function () {
    $cart = Cart::factory()->create(['customer_data' => null]);

    expect($cart->customerName())->toBeNull();
});

test('customer email accessor returns email from customer data', function () {
    $cart = Cart::factory()->create(['customer_data' => ['email' => 'john@example.com']]);

    expect($cart->customerEmail())->toBe('john@example.com');
});

test('customer phone accessor returns phone from customer data', function () {
    $cart = Cart::factory()->create(['customer_data' => ['phone' => '+32470123456']]);

    expect($cart->customerPhone())->toBe('+32470123456');
});

test('customer shipping address accessor returns address from customer data', function () {
    $address = ['street' => 'Main St 1', 'city' => 'Brussels', 'postal_code' => '1000', 'country' => 'Belgium'];
    $cart = Cart::factory()->create(['customer_data' => ['shipping_address' => $address]]);

    expect($cart->customerShippingAddress())->toBe($address);
});

test('customer billing address accessor returns address from customer data', function () {
    $address = ['street' => 'Other St 2', 'city' => 'Antwerp', 'postal_code' => '2000', 'country' => 'Belgium'];
    $cart = Cart::factory()->create(['customer_data' => ['billing_address' => $address]]);

    expect($cart->customerBillingAddress())->toBe($address);
});

test('customer notes accessor returns notes from customer data', function () {
    $cart = Cart::factory()->create(['customer_data' => ['notes' => 'Please leave at the door']]);

    expect($cart->customerNotes())->toBe('Please leave at the door');
});

test('customer notes accessor returns null when not set', function () {
    $cart = Cart::factory()->create(['customer_data' => ['name' => 'John Doe']]);

    expect($cart->customerNotes())->toBeNull();
});
