<?php

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;

test('guest cart is merged into user cart on login', function () {
    User::factory()->create();
    $product = Product::factory()->create();

    // Create a guest cart with an item
    $guestCart = Cart::factory()->guest()->create();
    CartItem::factory()->create([
        'cart_id' => $guestCart->id,
        'product_id' => $product->id,
        'product_name' => $product->title,
        'sku' => $product->sku,
        'unit_price' => $product->price,
        'total_price' => $product->price,
    ]);

    $user = User::factory()->create();

    // Login with the guest cart token as a cookie
    $this->withCookie('cart_token', $guestCart->cart_token)
        ->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

    // The user should have a cart with the merged item
    $userCart = Cart::where('user_id', $user->id)->first();
    expect($userCart)->not->toBeNull()
        ->and($userCart->cartItems->count())->toBe(1)
        ->and($userCart->cartItems->first()->product_id)->toBe($product->id);

    // The guest cart should be empty after merge
    $guestCart->refresh();
    expect($guestCart->cartItems->count())->toBe(0);
});

test('guest cart is merged into user cart on registration', function () {
    User::factory()->create();
    $product = Product::factory()->create();

    // Create a guest cart with an item
    $guestCart = Cart::factory()->guest()->create();
    CartItem::factory()->create([
        'cart_id' => $guestCart->id,
        'product_id' => $product->id,
        'product_name' => $product->title,
        'sku' => $product->sku,
        'unit_price' => $product->price,
        'total_price' => $product->price,
    ]);

    // Register with the guest cart token as a cookie
    $this->withCookie('cart_token', $guestCart->cart_token)
        ->post(route('register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms' => 'yes',
        ]);

    // The newly registered user should have a cart with the merged item
    $user = User::where('email', 'test@example.com')->first();
    expect($user)->not->toBeNull();
    $userCart = Cart::where('user_id', $user->id)->first();
    expect($userCart)->not->toBeNull()
        ->and($userCart->cartItems->count())->toBe(1)
        ->and($userCart->cartItems->first()->product_id)->toBe($product->id);

    // The guest cart should be empty after merge
    $guestCart->refresh();
    expect($guestCart->cartItems->count())->toBe(0);
});

test('guest cart items are combined with existing user cart items on login', function () {
    User::factory()->create();
    $product = Product::factory()->create();

    $user = User::factory()->create();

    // Create a user cart with an item
    $userCart = Cart::factory()->create([
        'user_id' => $user->id,
        'is_guest' => false,
    ]);
    CartItem::factory()->create([
        'cart_id' => $userCart->id,
        'product_id' => $product->id,
        'product_name' => $product->title,
        'sku' => $product->sku,
        'quantity' => 2,
        'unit_price' => $product->price,
        'total_price' => $product->price * 2,
    ]);

    // Create a guest cart with the same product
    $guestCart = Cart::factory()->guest()->create();
    CartItem::factory()->create([
        'cart_id' => $guestCart->id,
        'product_id' => $product->id,
        'product_name' => $product->title,
        'sku' => $product->sku,
        'quantity' => 1,
        'unit_price' => $product->price,
        'total_price' => $product->price,
    ]);

    $this->withCookie('cart_token', $guestCart->cart_token)
        ->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

    // Quantities should be combined
    $userCart->refresh();
    expect($userCart->cartItems->count())->toBe(1)
        ->and($userCart->cartItems->first()->quantity)->toBe(3);
});

test('nothing happens when there is no cart token cookie', function () {
    $user = User::factory()->create();

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    // No guest cart should be affected
    expect(Cart::where('is_guest', true)->count())->toBe(0);
});

test('nothing happens when the guest cart is empty', function () {
    $user = User::factory()->create();
    $guestCart = Cart::factory()->guest()->create();

    $this->withCookie('cart_token', $guestCart->cart_token)
        ->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

    expect($guestCart->cartItems->count())->toBe(0);
});

test('nothing happens when the cart is not a guest cart', function () {
    User::factory()->create();
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $otherUserCart = Cart::factory()->create([
        'user_id' => $otherUser->id,
        'is_guest' => false,
    ]);

    // Create a cart for the current user so we can check it wasn't modified
    $userCart = Cart::factory()->create([
        'user_id' => $user->id,
        'is_guest' => false,
    ]);

    $this->withCookie('cart_token', $otherUserCart->cart_token)
        ->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

    // No merge should happen since it's not a guest cart
    $userCart->refresh();
    expect($userCart->cartItems->count())->toBe(0);
});

test('cart token cookie is updated to user cart token after merge', function () {
    User::factory()->create();
    $product = Product::factory()->create();

    $guestCart = Cart::factory()->guest()->create();
    CartItem::factory()->create([
        'cart_id' => $guestCart->id,
        'product_id' => $product->id,
        'product_name' => $product->title,
        'sku' => $product->sku,
        'unit_price' => $product->price,
        'total_price' => $product->price,
    ]);

    $user = User::factory()->create();

    $response = $this->withCookie('cart_token', $guestCart->cart_token)
        ->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

    $userCart = Cart::where('user_id', $user->id)->first();

    // The response should have the new cart_token cookie set to the user's cart token
    $response->assertCookie('cart_token', $userCart->cart_token);
});

test('guest cart with different product is merged correctly', function () {
    User::factory()->create();
    $product1 = Product::factory()->create();
    $product2 = Product::factory()->create();

    $user = User::factory()->create();

    // User cart has product 1
    $userCart = Cart::factory()->create([
        'user_id' => $user->id,
        'is_guest' => false,
    ]);
    CartItem::factory()->create([
        'cart_id' => $userCart->id,
        'product_id' => $product1->id,
        'product_name' => $product1->title,
        'sku' => $product1->sku,
        'unit_price' => $product1->price,
        'total_price' => $product1->price,
    ]);

    // Guest cart has product 2
    $guestCart = Cart::factory()->guest()->create();
    CartItem::factory()->create([
        'cart_id' => $guestCart->id,
        'product_id' => $product2->id,
        'product_name' => $product2->title,
        'sku' => $product2->sku,
        'unit_price' => $product2->price,
        'total_price' => $product2->price,
    ]);

    $this->withCookie('cart_token', $guestCart->cart_token)
        ->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

    $userCart->refresh();
    expect($userCart->cartItems->count())->toBe(2)
        ->and($userCart->cartItems->pluck('product_id')->sort()->toArray())
        ->toBe([$product1->id, $product2->id]);
});
