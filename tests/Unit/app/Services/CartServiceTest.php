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
    $product = Product::factory()->create(['price' => 1999, 'is_discounted' => false, 'stock_quantity' => 10]);

    $service = app(CartService::class);
    $item = $service->addItem($cart, $product, null, 2);

    expect($item->product_id)->toBe($product->id)
        ->and($item->quantity)->toBe(2)
        ->and($item->unit_price)->toBe(1999)
        ->and($item->total_price)->toBe(3998);
});

test('it keeps cents when adding a product with decimal price', function () {
    $cart = Cart::factory()->create();
    $product = Product::factory()->create(['price' => 4388.84, 'is_discounted' => false, 'stock_quantity' => 10]);

    $service = app(CartService::class);
    $item = $service->addItem($cart, $product, null, 2);

    expect($item->getRawOriginal('unit_price'))->toBe(438884)
        ->and($item->getRawOriginal('total_price'))->toBe(877768);
});

test('it updates existing item quantity when adding the same product', function () {
    $cart = Cart::factory()->create();
    $product = Product::factory()->create(['price' => 1000, 'is_discounted' => false, 'stock_quantity' => 10]);

    $service = app(CartService::class);
    $service->addItem($cart, $product, null, 2);
    $service->addItem($cart, $product, null, 3);

    expect($cart->cartItems)->toHaveCount(1)
        ->and($cart->cartItems->first()->quantity)->toBe(5);
});

test('it validates stock when adding item', function () {
    $cart = Cart::factory()->create();
    $product = Product::factory()->create(['price' => 1000, 'is_discounted' => false, 'stock_quantity' => 2]);

    $service = app(CartService::class);

    expect(fn () => $service->addItem($cart, $product, null, 5))
        ->toThrow(Exception::class, __('Insufficient stock for this product'));
});

test('it updates item quantity', function () {
    $cart = Cart::factory()->create();
    $product = Product::factory()->create(['price' => 1000, 'is_discounted' => false, 'stock_quantity' => 10]);

    $service = app(CartService::class);
    $item = $service->addItem($cart, $product, null, 2);

    $updatedItem = $service->updateItem($item, 5);

    expect($updatedItem->quantity)->toBe(5)
        ->and($updatedItem->total_price)->toBe(5000);
});

test('it allows updating item quantity to exact stock available', function () {
    $cart = Cart::factory()->create();
    $product = Product::factory()->create(['price' => 1000, 'is_discounted' => false, 'stock_quantity' => 4]);

    $service = app(CartService::class);
    $item = $service->addItem($cart, $product);

    // Updating to 4 (exact stock) should succeed, not throw
    $updatedItem = $service->updateItem($item, 4);

    expect($updatedItem->quantity)->toBe(4)
        ->and($updatedItem->total_price)->toBe(4000);
});

test('it validates stock when updating item quantity', function () {
    $cart = Cart::factory()->create();
    $product = Product::factory()->create(['price' => 1000, 'is_discounted' => false, 'stock_quantity' => 3]);

    $service = app(CartService::class);
    $item = $service->addItem($cart, $product);

    // Updating to 5 when only 3 in stock should fail
    expect(fn () => $service->updateItem($item, 5))
        ->toThrow(Exception::class, __('Insufficient stock for this product'));
});

test('it validates stock when updating item with other cart items reserved', function () {
    $cart = Cart::factory()->create();
    $product = Product::factory()->create(['price' => 1000, 'is_discounted' => false, 'stock_quantity' => 5]);

    $service = app(CartService::class);

    // Add a different product to the cart
    $otherProduct = Product::factory()->create(['price' => 2000, 'is_discounted' => false, 'stock_quantity' => 10]);
    $service->addItem($cart, $otherProduct);

    // Add the main product with quantity 2
    $item = $service->addItem($cart, $product, null, 2);

    // Updating to 5 (exact stock, no other items of this product) should succeed
    $updatedItem = $service->updateItem($item, 5);

    expect($updatedItem->quantity)->toBe(5);
});

test('it removes item when quantity is zero or less', function () {
    $cart = Cart::factory()->create();
    $product = Product::factory()->create(['price' => 1000, 'is_discounted' => false, 'stock_quantity' => 10]);

    $service = app(CartService::class);
    $item = $service->addItem($cart, $product, null, 2);

    $service->updateItem($item, 0);

    expect(CartItem::withTrashed()->find($item->id))->not->toBeNull()
        ->and($cart->fresh()->cartItems)->toBeEmpty();
});

test('it removes an item from a cart', function () {
    $cart = Cart::factory()->create();
    $product = Product::factory()->create(['price' => 1000, 'is_discounted' => false, 'stock_quantity' => 10]);

    $service = app(CartService::class);
    $item = $service->addItem($cart, $product);

    $service->removeItem($item);

    expect($cart->fresh()->cartItems)->toBeEmpty();
});

test('it clears all items from a cart', function () {
    $cart = Cart::factory()->create();
    $product1 = Product::factory()->create(['price' => 1000, 'is_discounted' => false, 'stock_quantity' => 10]);
    $product2 = Product::factory()->create(['price' => 2000, 'is_discounted' => false, 'stock_quantity' => 10]);

    $service = app(CartService::class);
    $service->addItem($cart, $product1);
    $service->addItem($cart, $product2);

    $service->clear($cart);

    expect($cart->fresh()->cartItems)->toBeEmpty();
});

test('it merges two carts', function () {
    $cart1 = Cart::factory()->create();
    $cart2 = Cart::factory()->create();

    $product = Product::factory()->create(['price' => 1000, 'is_discounted' => false, 'stock_quantity' => 20]);

    $service = app(CartService::class);
    $service->addItem($cart1, $product, null, 2);
    $service->addItem($cart2, $product, null, 3);

    $service->merge($cart2, $cart1);

    expect($cart1->fresh()->cartItems)->toHaveCount(1)
        ->and($cart1->fresh()->cartItems->first()->quantity)->toBe(5);
});

test('it converts cart to order', function () {
    $cart = Cart::factory()->create();
    $product = Product::factory()->create(['price' => 1000, 'is_discounted' => false, 'tax_rate' => null, 'stock_quantity' => 10]);

    $service = app(CartService::class);
    $service->addItem($cart, $product, null, 2);

    $order = $service->convertToOrder($cart);

    // subtotal is now net (excl. VAT), tax_amount is VAT, total is gross
    // 2000 / 1.21 = 1652.8925... (calculated at cents level for precision)
    expect($order->total)->toBe(2000)
        ->and($order->subtotal)->toBe(1652.89)
        ->and($order->tax_amount)->toBe(347.11)
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
    $product = Product::factory()->create(['price' => 1000, 'is_discounted' => false, 'stock_quantity' => 10]);
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 1500, 'is_discounted' => false, 'stock_quantity' => 5]);

    $service = app(CartService::class);
    $item = $service->addItem($cart, $product, $variant);

    expect($item->product_variant_id)->toBe($variant->id)
        ->and($item->unit_price)->toBe(1500);
});

test('it validates variant stock when adding item', function () {
    $cart = Cart::factory()->create();
    $product = Product::factory()->create(['price' => 1000, 'is_discounted' => false, 'stock_quantity' => 100]);
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 1500, 'is_discounted' => false, 'stock_quantity' => 2]);

    $service = app(CartService::class);

    expect(fn () => $service->addItem($cart, $product, $variant, 5))
        ->toThrow(Exception::class, __('Insufficient stock for this product'));
});

test('it considers reserved quantity when validating stock', function () {
    $cart = Cart::factory()->create();
    $product = Product::factory()->create(['price' => 1000, 'is_discounted' => false, 'stock_quantity' => 5]);

    $service = app(CartService::class);
    $service->addItem($cart, $product, null, 3);

    expect(fn () => $service->addItem($cart, $product, null, 3))
        ->toThrow(Exception::class, __('Insufficient stock for this product'));
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
    $product = Product::factory()->create(['price' => 1000, 'is_discounted' => false, 'stock_quantity' => 10]);

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
    $product = Product::factory()->create(['price' => 1000, 'is_discounted' => false, 'stock_quantity' => 10]);
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => null, 'is_discounted' => false, 'stock_quantity' => 5]);

    $service = app(CartService::class);
    $item = $service->addItem($cart, $product, $variant);

    expect($item->unit_price)->toBe(1000);
});

test('it saves customer data to the cart', function () {
    $cart = Cart::factory()->create(['customer_data' => null]);

    $service = app(CartService::class);
    $service->saveCustomerData($cart, [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'phone' => '+32470123456',
    ]);

    $cart->refresh();
    expect($cart->customer_data['name'])->toBe('John Doe')
        ->and($cart->customer_data['email'])->toBe('john@example.com')
        ->and($cart->customer_data['phone'])->toBe('+32470123456');
});

test('it merges customer data with existing customer data', function () {
    $cart = Cart::factory()->create(['customer_data' => ['name' => 'Existing Name']]);

    $service = app(CartService::class);
    $service->saveCustomerData($cart, [
        'email' => 'john@example.com',
        'phone' => '+32470123456',
    ]);

    $cart->refresh();
    expect($cart->customer_data['name'])->toBe('Existing Name')
        ->and($cart->customer_data['email'])->toBe('john@example.com')
        ->and($cart->customer_data['phone'])->toBe('+32470123456');
});

test('it transfers customer data from cart to order on conversion', function () {
    $cart = Cart::factory()->create([
        'customer_data' => [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'shipping_address' => ['street' => 'Main St 1', 'city' => 'Brussels', 'postal_code' => '1000', 'country' => 'Belgium'],
            'billing_address' => ['street' => 'Other St 2', 'city' => 'Antwerp', 'postal_code' => '2000', 'country' => 'Belgium'],
            'notes' => 'Please leave at the door',
        ],
    ]);
    $product = Product::factory()->create(['price' => 1000, 'is_discounted' => false, 'stock_quantity' => 10]);

    $service = app(CartService::class);
    $service->addItem($cart, $product, null, 2);

    $order = $service->convertToOrder($cart);

    expect($order->shipping_address)->toBe(['street' => 'Main St 1', 'city' => 'Brussels', 'postal_code' => '1000', 'country' => 'Belgium', 'email' => 'john@example.com'])
        ->and($order->billing_address)->toBe(['street' => 'Other St 2', 'city' => 'Antwerp', 'postal_code' => '2000', 'country' => 'Belgium'])
        ->and($order->notes)->toBe('Please leave at the door');
});

test('it handles missing customer data on conversion', function () {
    $cart = Cart::factory()->create(['customer_data' => null]);
    $product = Product::factory()->create(['price' => 1000, 'is_discounted' => false, 'stock_quantity' => 10]);

    $service = app(CartService::class);
    $service->addItem($cart, $product);

    $order = $service->convertToOrder($cart);

    expect($order->shipping_address)->toBeNull()
        ->and($order->billing_address)->toBeNull()
        ->and($order->notes)->toBeNull();
});

test('it handles partial customer data on conversion', function () {
    $cart = Cart::factory()->create([
        'customer_data' => [
            'shipping_address' => ['street' => 'Main St 1', 'city' => 'Brussels', 'postal_code' => '1000', 'country' => 'Belgium'],
        ],
    ]);
    $product = Product::factory()->create(['price' => 1000, 'is_discounted' => false, 'stock_quantity' => 10]);

    $service = app(CartService::class);
    $service->addItem($cart, $product);

    $order = $service->convertToOrder($cart);

    expect($order->shipping_address)->toBe(['street' => 'Main St 1', 'city' => 'Brussels', 'postal_code' => '1000', 'country' => 'Belgium'])
        ->and($order->billing_address)->toBeNull()
        ->and($order->notes)->toBeNull();
});

test('it uses discount price when product is discounted', function () {
    $cart = Cart::factory()->create();
    $product = Product::factory()->create([
        'price' => 1000,
        'discount_price' => 750,
        'is_discounted' => true,
        'stock_quantity' => 10,
    ]);

    $service = app(CartService::class);
    $item = $service->addItem($cart, $product, null, 2);

    expect($item->unit_price)->toBe(750)
        ->and($item->total_price)->toBe(1500);
});

test('it uses original price when product is not discounted', function () {
    $cart = Cart::factory()->create();
    $product = Product::factory()->create([
        'price' => 1000,
        'discount_price' => 750,
        'is_discounted' => false,
        'stock_quantity' => 10,
    ]);

    $service = app(CartService::class);
    $item = $service->addItem($cart, $product);

    expect($item->unit_price)->toBe(1000);
});

test('it uses discount price when variant is discounted', function () {
    $cart = Cart::factory()->create();
    $product = Product::factory()->create(['price' => 1000, 'is_discounted' => false, 'stock_quantity' => 10]);
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'price' => 1200,
        'discount_price' => 900,
        'is_discounted' => true,
        'stock_quantity' => 5,
    ]);

    $service = app(CartService::class);
    $item = $service->addItem($cart, $product, $variant);

    expect($item->unit_price)->toBe(900);
});

test('it uses variant price when variant is not discounted but product is', function () {
    $cart = Cart::factory()->create();
    $product = Product::factory()->create([
        'price' => 1000,
        'discount_price' => 750,
        'is_discounted' => true,
        'stock_quantity' => 10,
    ]);
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'price' => 1200,
        'discount_price' => null,
        'is_discounted' => false,
        'stock_quantity' => 5,
    ]);

    $service = app(CartService::class);
    $item = $service->addItem($cart, $product, $variant);

    expect($item->unit_price)->toBe(1200);
});

test('it uses product discount price when variant has no price and product is discounted', function () {
    $cart = Cart::factory()->create();
    $product = Product::factory()->create([
        'price' => 1000,
        'discount_price' => 750,
        'is_discounted' => true,
        'stock_quantity' => 10,
    ]);
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'price' => null,
        'discount_price' => null,
        'is_discounted' => false,
        'stock_quantity' => 5,
    ]);

    $service = app(CartService::class);
    $item = $service->addItem($cart, $product, $variant);

    expect($item->unit_price)->toBe(750);
});

// --------------------------------------------------------------------------
// Stock Reservation & Restoration
// --------------------------------------------------------------------------

test('it reserves stock on checkout', function () {
    $cart = Cart::factory()->create();
    $product = Product::factory()->create(['price' => 1000, 'is_discounted' => false, 'stock_quantity' => 10]);

    $service = app(CartService::class);
    $service->addItem($cart, $product, null, 3);

    $order = $service->convertToOrder($cart);

    expect($order->orderItems)->toHaveCount(1)
        ->and($product->fresh()->stock_quantity)->toBe(7);
});

test('it reserves variant stock on checkout', function () {
    $cart = Cart::factory()->create();
    $product = Product::factory()->create(['price' => 1000, 'is_discounted' => false, 'stock_quantity' => 100]);
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 1500, 'is_discounted' => false, 'stock_quantity' => 5]);

    $service = app(CartService::class);
    $service->addItem($cart, $product, $variant, 2);

    $order = $service->convertToOrder($cart);

    expect($order->orderItems)->toHaveCount(1)
        ->and($variant->fresh()->stock_quantity)->toBe(3)
        ->and($product->fresh()->stock_quantity)->toBe(100);
});

test('it skips zero stock items when converting to order', function () {
    $cart = Cart::factory()->create();
    $product = Product::factory()->create(['price' => 1000, 'is_discounted' => false, 'stock_quantity' => 2]);

    $service = app(CartService::class);
    $service->addItem($cart, $product);

    // Simulate stock dropping to 0 after item was added to cart
    $product->update(['stock_quantity' => 0]);

    // The out-of-stock item is removed, and since the cart is now empty,
    // the conversion still proceeds but with no items
    $order = $service->convertToOrder($cart);

    expect($order->orderItems)->toHaveCount(0)
        ->and($product->fresh()->stock_quantity)->toBe(0);
});

test('it fails to convert cart to order when stock is insufficient for quantity at checkout', function () {
    $cart = Cart::factory()->create();
    $product = Product::factory()->create(['price' => 1000, 'is_discounted' => false, 'stock_quantity' => 5]);

    $service = app(CartService::class);
    $service->addItem($cart, $product, null, 3);

    // Simulate stock dropping below the requested quantity (but not to 0)
    $product->update(['stock_quantity' => 1]);

    expect(fn () => $service->convertToOrder($cart))
        ->toThrow(Exception::class, __('Insufficient stock for this product'))
        ->and($product->fresh()->stock_quantity)->toBe(1)
        ->and($cart->fresh()->cartItems)->toHaveCount(1);

    // Stock should not have changed (transaction rolled back)
});

test('it restores stock for order items', function () {
    $product = Product::factory()->create(['price' => 1000, 'is_discounted' => false, 'stock_quantity' => 10]);

    $cart = Cart::factory()->create();
    $service = app(CartService::class);
    $service->addItem($cart, $product, null, 3);
    $order = $service->convertToOrder($cart);

    expect($product->fresh()->stock_quantity)->toBe(7);

    $service->restoreStockForOrder($order);

    expect($product->fresh()->stock_quantity)->toBe(10);
});

test('it restores variant stock for order items', function () {
    $product = Product::factory()->create(['price' => 1000, 'is_discounted' => false, 'stock_quantity' => 100]);
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 1500, 'is_discounted' => false, 'stock_quantity' => 5]);

    $cart = Cart::factory()->create();
    $service = app(CartService::class);
    $service->addItem($cart, $product, $variant, 2);
    $order = $service->convertToOrder($cart);

    expect($variant->fresh()->stock_quantity)->toBe(3);

    $service->restoreStockForOrder($order);

    expect($variant->fresh()->stock_quantity)->toBe(5)
        ->and($product->fresh()->stock_quantity)->toBe(100);
});

test('it reserves stock for multiple items on checkout', function () {
    $cart = Cart::factory()->create();
    $product1 = Product::factory()->create(['price' => 1000, 'is_discounted' => false, 'stock_quantity' => 10]);
    $product2 = Product::factory()->create(['price' => 2000, 'is_discounted' => false, 'stock_quantity' => 5]);

    $service = app(CartService::class);
    $service->addItem($cart, $product1, null, 3);
    $service->addItem($cart, $product2, null, 2);

    $order = $service->convertToOrder($cart);

    expect($order->orderItems)->toHaveCount(2)
        ->and($product1->fresh()->stock_quantity)->toBe(7)
        ->and($product2->fresh()->stock_quantity)->toBe(3);
});

// --------------------------------------------------------------------------
// Product & ProductVariant Stock Helpers
// --------------------------------------------------------------------------

test('product isInStock returns true when stock is positive', function () {
    $product = Product::factory()->create(['stock_quantity' => 5]);

    expect($product->isInStock())->toBeTrue()
        ->and($product->isOutOfStock())->toBeFalse()
        ->and($product->canFulfillQuantity(3))->toBeTrue()
        ->and($product->canFulfillQuantity(5))->toBeTrue()
        ->and($product->canFulfillQuantity(6))->toBeFalse();
});

test('product isInStock returns false when stock is zero', function () {
    $product = Product::factory()->create(['stock_quantity' => 0]);

    expect($product->isInStock())->toBeFalse()
        ->and($product->isOutOfStock())->toBeTrue()
        ->and($product->canFulfillQuantity(1))->toBeFalse();
});

test('variant isInStock returns true when stock is positive', function () {
    $product = Product::factory()->create(['stock_quantity' => 10]);
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'stock_quantity' => 3]);

    expect($variant->isInStock())->toBeTrue()
        ->and($variant->isOutOfStock())->toBeFalse()
        ->and($variant->canFulfillQuantity(2))->toBeTrue()
        ->and($variant->canFulfillQuantity(4))->toBeFalse();
});

test('variant isInStock returns false when stock is zero', function () {
    $product = Product::factory()->create(['stock_quantity' => 10]);
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'stock_quantity' => 0]);

    expect($variant->isInStock())->toBeFalse()
        ->and($variant->isOutOfStock())->toBeTrue();
});

// --------------------------------------------------------------------------
// Remove Out of Stock Items
// --------------------------------------------------------------------------

test('it removes out of stock product items from cart', function () {
    $cart = Cart::factory()->create();
    $inStockProduct = Product::factory()->create(['price' => 1000, 'is_discounted' => false, 'stock_quantity' => 10]);
    $outOfStockProduct = Product::factory()->create(['price' => 2000, 'is_discounted' => false, 'stock_quantity' => 0]);

    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $inStockProduct->id,
        'product_name' => $inStockProduct->title,
        'quantity' => 1,
        'unit_price' => 1000,
        'total_price' => 1000,
    ]);
    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $outOfStockProduct->id,
        'product_name' => $outOfStockProduct->title,
        'quantity' => 1,
        'unit_price' => 2000,
        'total_price' => 2000,
    ]);

    $cart->refresh();
    expect($cart->cartItems)->toHaveCount(2);

    $service = app(CartService::class);
    $removed = $service->removeOutOfStockItems($cart);

    expect($removed)->toBe(1)
        ->and($cart->fresh()->cartItems)->toHaveCount(1)
        ->and($cart->fresh()->cartItems->first()->product_id)->toBe($inStockProduct->id);
});

test('it removes out of stock variant items from cart', function () {
    $cart = Cart::factory()->create();
    $product = Product::factory()->create(['price' => 1000, 'is_discounted' => false, 'stock_quantity' => 100]);
    $inStockVariant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 1500, 'is_discounted' => false, 'stock_quantity' => 5]);
    $outOfStockVariant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 1500, 'is_discounted' => false, 'stock_quantity' => 0]);

    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'product_variant_id' => $inStockVariant->id,
        'product_name' => $product->title,
        'quantity' => 1,
        'unit_price' => 1500,
        'total_price' => 1500,
    ]);
    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'product_variant_id' => $outOfStockVariant->id,
        'product_name' => $product->title,
        'quantity' => 1,
        'unit_price' => 1500,
        'total_price' => 1500,
    ]);

    $service = app(CartService::class);
    $removed = $service->removeOutOfStockItems($cart);

    expect($removed)->toBe(1)
        ->and($cart->fresh()->cartItems)->toHaveCount(1)
        ->and($cart->fresh()->cartItems->first()->product_variant_id)->toBe($inStockVariant->id);
});

test('it removes deleted product items from cart', function () {
    $cart = Cart::factory()->create();
    $product = Product::factory()->create(['price' => 1000, 'is_discounted' => false, 'stock_quantity' => 10]);

    $service = app(CartService::class);
    $service->addItem($cart, $product);

    $product->delete();

    $removed = $service->removeOutOfStockItems($cart);

    expect($removed)->toBe(1)
        ->and($cart->fresh()->cartItems)->toHaveCount(0);
});

test('it returns zero when no items are out of stock', function () {
    $cart = Cart::factory()->create();
    $product = Product::factory()->create(['price' => 1000, 'is_discounted' => false, 'stock_quantity' => 10]);

    $service = app(CartService::class);
    $service->addItem($cart, $product);

    $removed = $service->removeOutOfStockItems($cart);

    expect($removed)->toBe(0)
        ->and($cart->fresh()->cartItems)->toHaveCount(1);
});

test('it removes out of stock items before converting to order', function () {
    $cart = Cart::factory()->create();
    $inStockProduct = Product::factory()->create(['price' => 1000, 'is_discounted' => false, 'stock_quantity' => 10]);
    $outOfStockProduct = Product::factory()->create(['price' => 2000, 'is_discounted' => false, 'stock_quantity' => 0]);

    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $inStockProduct->id,
        'product_name' => $inStockProduct->title,
        'quantity' => 2,
        'unit_price' => 1000,
        'total_price' => 2000,
    ]);
    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $outOfStockProduct->id,
        'product_name' => $outOfStockProduct->title,
        'quantity' => 1,
        'unit_price' => 2000,
        'total_price' => 2000,
    ]);

    $service = app(CartService::class);
    $order = $service->convertToOrder($cart);

    expect($order->orderItems)->toHaveCount(1)
        ->and($order->orderItems->first()->product_id)->toBe($inStockProduct->id)
        ->and($inStockProduct->fresh()->stock_quantity)->toBe(8);
});

test('it converts to order with remaining items after removing out of stock variant', function () {
    $cart = Cart::factory()->create();
    $product = Product::factory()->create(['price' => 1000, 'is_discounted' => false, 'stock_quantity' => 100]);
    $inStockVariant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 1500, 'is_discounted' => false, 'stock_quantity' => 5]);
    $outOfStockVariant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 1200, 'is_discounted' => false, 'stock_quantity' => 0]);

    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'product_variant_id' => $inStockVariant->id,
        'product_name' => $product->title,
        'quantity' => 1,
        'unit_price' => 1500,
        'total_price' => 1500,
    ]);
    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'product_variant_id' => $outOfStockVariant->id,
        'product_name' => $product->title,
        'quantity' => 2,
        'unit_price' => 1200,
        'total_price' => 2400,
    ]);

    $service = app(CartService::class);
    $order = $service->convertToOrder($cart);

    expect($order->orderItems)->toHaveCount(1)
        ->and($order->orderItems->first()->product_variant_id)->toBe($inStockVariant->id)
        ->and($inStockVariant->fresh()->stock_quantity)->toBe(4);
});

test('it reduces quantity when stock drops below cart quantity', function () {
    $cart = Cart::factory()->create();
    $product = Product::factory()->create(['price' => 1000, 'is_discounted' => false, 'stock_quantity' => 4]);

    $service = app(CartService::class);
    $service->addItem($cart, $product, null, 4);

    // Simulate another buyer purchasing some stock
    $product->stock_quantity = 2;
    $product->save();

    $adjusted = $service->adjustOverstockedItems($cart);

    expect($adjusted)->toBe(1);
    $cartItem = $cart->fresh()->cartItems->first();
    expect($cartItem->quantity)->toBe(2)
        ->and($cartItem->total_price)->toBe(2000);
});

test('it removes item when stock drops to zero', function () {
    $cart = Cart::factory()->create();
    $product = Product::factory()->create(['price' => 1000, 'is_discounted' => false, 'stock_quantity' => 4]);

    $service = app(CartService::class);
    $service->addItem($cart, $product, null, 2);

    // Simulate stock selling out completely
    $product->stock_quantity = 0;
    $product->save();

    $adjusted = $service->adjustOverstockedItems($cart);

    expect($adjusted)->toBe(1)
        ->and($cart->fresh()->cartItems)->toHaveCount(0);
});

test('it reduces variant quantity when variant stock drops', function () {
    $cart = Cart::factory()->create();
    $product = Product::factory()->create(['price' => 1000, 'is_discounted' => false, 'stock_quantity' => 100]);
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 1500, 'is_discounted' => false, 'stock_quantity' => 4]);

    $service = app(CartService::class);
    $service->addItem($cart, $product, $variant, 4);

    // Simulate another buyer purchasing some of this variant
    $variant->stock_quantity = 1;
    $variant->save();

    $adjusted = $service->adjustOverstockedItems($cart);

    expect($adjusted)->toBe(1);
    $cartItem = $cart->fresh()->cartItems->first();
    expect($cartItem->quantity)->toBe(1)
        ->and($cartItem->total_price)->toBe(1500);
});

test('it does not adjust when quantity is within stock', function () {
    $cart = Cart::factory()->create();
    $product = Product::factory()->create(['price' => 1000, 'is_discounted' => false, 'stock_quantity' => 10]);

    $service = app(CartService::class);
    $service->addItem($cart, $product, null, 2);

    $adjusted = $service->adjustOverstockedItems($cart);

    expect($adjusted)->toBe(0);
    $cartItem = $cart->fresh()->cartItems->first();
    expect($cartItem->quantity)->toBe(2)
        ->and($cartItem->total_price)->toBe(2000);
});

test('it removes item when product is deleted', function () {
    $cart = Cart::factory()->create();
    $product = Product::factory()->create(['price' => 1000, 'is_discounted' => false, 'stock_quantity' => 10]);

    $service = app(CartService::class);
    $service->addItem($cart, $product);

    $product->delete();

    $adjusted = $service->adjustOverstockedItems($cart);

    expect($adjusted)->toBe(1)
        ->and($cart->fresh()->cartItems)->toHaveCount(0);
});

test('it adjusts multiple items independently', function () {
    $cart = Cart::factory()->create();
    $product1 = Product::factory()->create(['price' => 1000, 'is_discounted' => false, 'stock_quantity' => 4]);
    $product2 = Product::factory()->create(['price' => 2000, 'is_discounted' => false, 'stock_quantity' => 10]);

    $service = app(CartService::class);
    $service->addItem($cart, $product1, null, 4);
    $service->addItem($cart, $product2, null, 3);

    // Product1 stock drops, product2 is fine
    $product1->stock_quantity = 2;
    $product1->save();

    $adjusted = $service->adjustOverstockedItems($cart);

    expect($adjusted)->toBe(1);
    $items = $cart->fresh()->cartItems;
    expect($items)->toHaveCount(2);
    $item1 = $items->firstWhere('product_id', $product1->id);
    $item2 = $items->firstWhere('product_id', $product2->id);
    expect($item1->quantity)->toBe(2)
        ->and($item2->quantity)->toBe(3);
});

// --------------------------------------------------------------------------
// Reserve Stock For Order
// --------------------------------------------------------------------------

test('it reserves stock for order items', function () {
    $product = Product::factory()->create(['price' => 1000, 'is_discounted' => false, 'stock_quantity' => 10]);

    $order = App\Models\Order::factory()->create();
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_name' => $product->title,
        'quantity' => 3,
        'unit_price' => 10,
        'total_price' => 30,
    ]);

    $service = app(CartService::class);
    $service->reserveStockForOrder($order);

    expect($product->fresh()->stock_quantity)->toBe(7);
});

test('it reserves variant stock for order items', function () {
    $product = Product::factory()->create(['price' => 1000, 'is_discounted' => false, 'stock_quantity' => 100]);
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 1500, 'is_discounted' => false, 'stock_quantity' => 5]);

    $order = App\Models\Order::factory()->create();
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_variant_id' => $variant->id,
        'product_name' => $product->title,
        'quantity' => 2,
        'unit_price' => 15,
        'total_price' => 30,
    ]);

    $service = app(CartService::class);
    $service->reserveStockForOrder($order);

    expect($variant->fresh()->stock_quantity)->toBe(3)
        ->and($product->fresh()->stock_quantity)->toBe(100);
});

test('it fails to reserve stock for order when insufficient stock', function () {
    $product = Product::factory()->create(['price' => 1000, 'is_discounted' => false, 'stock_quantity' => 1]);

    $order = App\Models\Order::factory()->create();
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_name' => $product->title,
        'quantity' => 5,
        'unit_price' => 10,
        'total_price' => 50,
    ]);

    $service = app(CartService::class);

    expect(fn () => $service->reserveStockForOrder($order))
        ->toThrow(Exception::class, __('Insufficient stock for this product'))
        ->and($product->fresh()->stock_quantity)->toBe(1);

});
