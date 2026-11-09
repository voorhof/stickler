<?php

/** @noinspection PhpUnhandledExceptionInspection, PhpPossiblePolymorphicInvocationInspection */

use App\Enums\PaymentStatus;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Laravel\Head\Facades\Head;

beforeEach(function () {
    User::factory()->create();
});

describe('cart index', function () {
    test('it displays an empty cart', function () {
        $response = $this->get(route('cart.index'));

        $response->assertSuccessful()
            ->assertViewIs('public.cart.index')
            ->assertSeeText(__('Your cart is empty'));
    });

    test('it sets head metadata', function () {
        $this->get(route('cart.index'));

        $html = Head::toHtml();

        expect($html)->toContain('<title>'.config('app.name').' | '.e(__('Cart')).'</title>');
    });

    test('it displays cart items', function () {
        $product = Product::factory()->create(['price' => 2999]);
        $cart = Cart::factory()->create();
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_name' => $product->title,
            'sku' => $product->sku,
            'quantity' => 2,
            'unit_price' => 2999,
            'total_price' => 5998,
        ]);

        $response = $this->withCookie('cart_token', $cart->cart_token)
            ->get(route('cart.index'));

        $response->assertSuccessful()
            ->assertViewIs('public.cart.index')
            ->assertViewHas('cart', fn (Cart $viewCart) => $viewCart->id === $cart->id)
            ->assertSeeText($product->title);
    });

    test('it displays product images in cart', function () {
        $product = Product::factory()->create(['price' => 2999]);
        $cart = Cart::factory()->create();
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_name' => $product->title,
            'sku' => $product->sku,
            'quantity' => 1,
            'unit_price' => 2999,
            'total_price' => 2999,
        ]);

        $response = $this->withCookie('cart_token', $cart->cart_token)
            ->get(route('cart.index'));

        $response->assertSuccessful()
            ->assertViewHas('cart', fn (Cart $viewCart) => $viewCart->cartItems->first()->relationLoaded('product'));
    });

    test('it displays unit and total price of cart items', function () {
        $cart = Cart::factory()->create();
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_name' => 'Priced Product',
            'quantity' => 2,
            'unit_price' => 19.99,
            'total_price' => 39.98,
        ]);

        $this->withCookie('cart_token', $cart->cart_token)
            ->get(route('cart.index'))
            ->assertSuccessful()
            ->assertSeeInOrder(['19.99 €', '39.98 €']);
    });

    test('it displays variant label for variant items and sku for all items', function () {
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);
        $cart = Cart::factory()->create();
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'product_name' => 'Variant Product',
            'product_variant_label' => 'Red / M',
            'sku' => 'VARIANT-SKU-123',
        ]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => null,
            'product_name' => 'Main Product',
            'product_variant_label' => null,
            'sku' => 'MAIN-SKU-456',
        ]);

        $this->withCookie('cart_token', $cart->cart_token)
            ->get(route('cart.index'))
            ->assertSuccessful()
            ->assertSeeText('Red / M')
            ->assertSeeText('VARIANT-SKU-123')
            ->assertSeeText('MAIN-SKU-456');
    });

    test('it links cart item product name to the product page', function () {
        $product = Product::factory()->create();
        $cart = Cart::factory()->create();
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_name' => $product->title,
        ]);

        $this->withCookie('cart_token', $cart->cart_token)
            ->get(route('cart.index'))
            ->assertSuccessful()
            ->assertSee('href="'.route('products.show', $product).'"', false);
    });

    test('it bypasses the response cache for visitors with a cart cookie', function () {
        config(['responsecache.enabled' => true]);
        $profile = app(App\Http\CacheProfiles\CacheAllSuccessfulGetRequestsWithoutCart::class);

        $visitorWithoutCart = Illuminate\Http\Request::create('/');
        $visitorWithCart = Illuminate\Http\Request::create('/', 'GET', [], ['cart_token' => 'some-token']);

        expect($profile->enabled($visitorWithoutCart))->toBeTrue()
            ->and($profile->enabled($visitorWithCart))->toBeFalse();
    });

    test('it removes out of stock items and shows warning', function () {
        $inStockProduct = Product::factory()->create(['stock_quantity' => 10, 'price' => 1999]);
        $outOfStockProduct = Product::factory()->create(['stock_quantity' => 0, 'price' => 2999]);

        $cart = Cart::factory()->create();
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $inStockProduct->id,
            'product_name' => $inStockProduct->title,
            'quantity' => 1,
            'unit_price' => 1999,
            'total_price' => 1999,
        ]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $outOfStockProduct->id,
            'product_name' => $outOfStockProduct->title,
            'quantity' => 1,
            'unit_price' => 2999,
            'total_price' => 2999,
        ]);

        $response = $this->withCookie('cart_token', $cart->cart_token)
            ->get(route('cart.index'));

        $response->assertRedirect()
            ->assertSessionHas('warning');

        expect($cart->fresh()->cartItems)->toHaveCount(1)
            ->and($cart->fresh()->cartItems->first()->product_id)->toBe($inStockProduct->id);
    });

    test('it does not show warning when all items are in stock', function () {
        $product = Product::factory()->create(['stock_quantity' => 10, 'price' => 1999]);

        $cart = Cart::factory()->create();
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_name' => $product->title,
            'quantity' => 1,
            'unit_price' => 1999,
            'total_price' => 1999,
        ]);

        $response = $this->withCookie('cart_token', $cart->cart_token)
            ->get(route('cart.index'));

        $response->assertSuccessful()
            ->assertSessionMissing('warning');
    });

    test('it reduces quantity and shows warning when stock drops', function () {
        $product = Product::factory()->create(['stock_quantity' => 4, 'price' => 1999]);

        $cart = Cart::factory()->create();
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_name' => $product->title,
            'quantity' => 4,
            'unit_price' => 1999,
            'total_price' => 7996,
        ]);

        // Simulate another buyer purchasing some stock
        $product->stock_quantity = 2;
        $product->save();

        $response = $this->withCookie('cart_token', $cart->cart_token)
            ->get(route('cart.index'));

        $response->assertRedirect()
            ->assertSessionHas('warning');

        expect($cart->fresh()->cartItems->first()->quantity)->toBe(2);
    });
});

describe('cart add', function () {
    test('it adds a product to the cart', function () {
        $product = Product::factory()->create(['stock_quantity' => 10]);

        $response = $this->post(route('cart.add', $product), ['quantity' => 1]);

        $response->assertRedirect(route('cart.index'))
            ->assertSessionHas('success', __('Product added to cart'));

        expect(Cart::count())->toBe(1)
            ->and(Cart::first()->cartItems)->toHaveCount(1);
    });

    test('it adds a product variant to the cart', function () {
        $product = Product::factory()->create(['stock_quantity' => 10]);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'stock_quantity' => 5,
            'price' => 3999,
            'is_discounted' => false,
            'sku' => 'VARIANT-SKU',
        ]);
        $variant->load('options');

        $service = app(App\Services\CartService::class);
        $cart = $service->getOrCreateCart();
        $item = $service->addItem($cart, $product, $variant);

        expect($item->product_variant_id)->toBe($variant->id)
            ->and($item->product_variant_label)->toBe($variant->label)
            ->and($item->unit_price)->toBe(3999)
            ->and($item->sku)->toBe('VARIANT-SKU');
    });

    test('it rejects adding the main product when it has variants', function () {
        $product = Product::factory()->create(['stock_quantity' => 10]);
        ProductVariant::factory()->create(['product_id' => $product->id, 'stock_quantity' => 5]);

        $response = $this->post(route('cart.add', $product), ['quantity' => 1]);

        $response->assertRedirect()
            ->assertSessionHas('error', __('Please choose a variant'));

        expect(CartItem::count())->toBe(0);
    });

    test('it returns 404 when adding a variant of another product', function () {
        $product = Product::factory()->create(['stock_quantity' => 10]);
        $otherProduct = Product::factory()->create(['stock_quantity' => 10]);
        $foreignVariant = ProductVariant::factory()->create([
            'product_id' => $otherProduct->id,
            'stock_quantity' => 5,
        ]);

        $response = $this->post(route('cart.add', [$product, $foreignVariant]), ['quantity' => 1]);

        $response->assertNotFound();

        expect(CartItem::count())->toBe(0);
    });

    test('it adds a variant of the product via http', function () {
        $product = Product::factory()->create(['stock_quantity' => 10]);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'stock_quantity' => 5,
        ]);

        $response = $this->post(route('cart.add', [$product, $variant]), ['quantity' => 1]);

        $response->assertRedirect(route('cart.index'))
            ->assertSessionHas('success', __('Product added to cart'));

        expect(CartItem::where('product_variant_id', $variant->id)->count())->toBe(1);
    });

    test('it sets cart_token cookie for guest users', function () {
        $product = Product::factory()->create(['stock_quantity' => 10]);

        $response = $this->post(route('cart.add', $product));

        $response->assertCookie('cart_token');
    });

    test('it throws exception when stock is insufficient', function () {
        $product = Product::factory()->create(['stock_quantity' => 0, 'price' => 1999]);

        $service = app(App\Services\CartService::class);
        $cart = $service->getOrCreateCart();

        expect(fn () => $service->addItem($cart, $product))
            ->toThrow(Exception::class, __('Insufficient stock for this product'));
    });

    test('it shows error notification when adding out of stock product', function () {
        $product = Product::factory()->create(['stock_quantity' => 0, 'price' => 1999]);

        $response = $this->post(route('cart.add', $product), ['quantity' => 1]);

        $response->assertRedirect()
            ->assertSessionHas('error', __('Insufficient stock for this product'));

        expect(CartItem::count())->toBe(0);
    });
});

describe('cart update', function () {
    test('it updates a cart item quantity', function () {
        $product = Product::factory()->create(['stock_quantity' => 10, 'price' => 2999]);
        $cart = Cart::factory()->create();
        $item = CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 2999,
            'total_price' => 2999,
        ]);

        $response = $this->withCookie('cart_token', $cart->cart_token)
            ->post(route('cart.items.update', $item), ['quantity' => 3]);

        $response->assertRedirect(route('cart.index'))
            ->assertSessionHas('success', __('Cart updated'));

        $item->refresh();
        expect($item->quantity)->toBe(3)
            ->and($item->total_price)->toBe(8997);
    });

    test('it returns 403 when updating item from another cart', function () {
        $product = Product::factory()->create();
        $cart1 = Cart::factory()->create();
        $cart2 = Cart::factory()->create();
        $item = CartItem::factory()->create([
            'cart_id' => $cart1->id,
            'product_id' => $product->id,
        ]);

        $response = $this->withCookie('cart_token', $cart2->cart_token)
            ->post(route('cart.items.update', $item), ['quantity' => 2]);

        $response->assertForbidden();
    });

    test('it shows error notification when updating quantity above available stock', function () {
        $product = Product::factory()->create(['stock_quantity' => 2, 'price' => 2999]);
        $cart = Cart::factory()->create();
        $item = CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 2999,
            'total_price' => 2999,
        ]);

        $response = $this->withCookie('cart_token', $cart->cart_token)
            ->post(route('cart.items.update', $item), ['quantity' => 10]);

        $response->assertRedirect()
            ->assertSessionHas('error', __('Insufficient stock for this product'));

        $item->refresh();
        expect($item->quantity)->toBe(1);
    });
});

describe('cart remove', function () {
    test('it removes an item from the cart', function () {
        $product = Product::factory()->create();
        $cart = Cart::factory()->create();
        $item = CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
        ]);

        $response = $this->withCookie('cart_token', $cart->cart_token)
            ->post(route('cart.items.remove', $item));

        $response->assertRedirect(route('cart.index'))
            ->assertSessionHas('success', __('Item removed from cart'));

        expect(CartItem::onlyTrashed()->where('id', $item->id)->exists())->toBeTrue();
    });

    test('it returns 403 when removing item from another cart', function () {
        $product = Product::factory()->create();
        $cart1 = Cart::factory()->create();
        $cart2 = Cart::factory()->create();
        $item = CartItem::factory()->create([
            'cart_id' => $cart1->id,
            'product_id' => $product->id,
        ]);

        $response = $this->withCookie('cart_token', $cart2->cart_token)
            ->post(route('cart.items.remove', $item));

        $response->assertForbidden();
    });
});

describe('checkout confirmation page', function () {
    test('it shows the confirmation page with cart items', function () {
        $product = Product::factory()->create(['price' => 2999]);
        $cart = Cart::factory()->create();
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_name' => $product->title,
            'quantity' => 2,
            'unit_price' => 2999,
            'total_price' => 5998,
        ]);

        $response = $this->withCookie('cart_token', $cart->cart_token)
            ->get(route('checkout.index'));

        $response->assertSuccessful()
            ->assertViewIs('public.checkout.index')
            ->assertSeeText($product->title);
    });

    test('it sets head metadata on checkout page', function () {
        $product = Product::factory()->create();
        $cart = Cart::factory()->create();
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_name' => $product->title,
        ]);

        $this->withCookie('cart_token', $cart->cart_token)
            ->get(route('checkout.index'));

        $html = Head::toHtml();

        expect($html)->toContain('<title>'.config('app.name').' | '.e(__('Checkout')).'</title>');
    });

    test('it redirects to cart index when cart is empty', function () {
        $cart = Cart::factory()->create();

        $response = $this->withCookie('cart_token', $cart->cart_token)
            ->get(route('checkout.index'));

        $response->assertRedirect(route('cart.index'))
            ->assertSessionHas('error', __('Your cart is empty'));
    });

    test('it pre-fills customer info for logged in users', function () {
        $user = User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
        $product = Product::factory()->create();
        $cart = Cart::factory()->create([
            'user_id' => $user->id,
            'is_guest' => false,
            'customer_data' => [
                'name' => 'John Doe',
                'email' => 'john@example.com',
            ],
        ]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_name' => $product->title,
        ]);

        $response = $this->actingAs($user)
            ->get(route('checkout.index'));

        $response->assertSuccessful()
            ->assertSee('John Doe')
            ->assertSee('john@example.com');
    });

    test('it reduces quantity and shows warning on checkout when stock drops', function () {
        $product = Product::factory()->create(['stock_quantity' => 4, 'price' => 1999]);

        $cart = Cart::factory()->create();
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_name' => $product->title,
            'quantity' => 4,
            'unit_price' => 1999,
            'total_price' => 7996,
        ]);

        // Simulate another buyer purchasing some stock
        $product->stock_quantity = 2;
        $product->save();

        $response = $this->withCookie('cart_token', $cart->cart_token)
            ->get(route('checkout.index'));

        $response->assertRedirect()
            ->assertSessionHas('warning');

        expect($cart->fresh()->cartItems->first()->quantity)->toBe(2);
    });

    test('it redirects to cart when all items become out of stock at checkout', function () {
        $product = Product::factory()->create(['stock_quantity' => 4, 'price' => 1999]);

        $cart = Cart::factory()->create();
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_name' => $product->title,
            'quantity' => 4,
            'unit_price' => 1999,
            'total_price' => 7996,
        ]);

        // Simulate stock selling out completely
        $product->stock_quantity = 0;
        $product->save();

        $response = $this->withCookie('cart_token', $cart->cart_token)
            ->get(route('checkout.index'));

        $response->assertRedirect(route('cart.index'))
            ->assertSessionHas('error', __('Your cart is empty'));
    });
});

describe('order creation', function () {
    test('it creates an order from the cart with customer data', function () {
        $product = Product::factory()->create(['price' => 2999, 'stock_quantity' => 10]);
        $cart = Cart::factory()->create();
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_name' => $product->title,
            'sku' => $product->sku,
            'quantity' => 2,
            'unit_price' => 2999,
            'total_price' => 5998,
        ]);

        $paymentService = mock(App\Services\PaymentService::class)->shouldReceive('initiatePayment')
            ->once()
            ->andReturnUsing(function (Order $order) {
                $order->update([
                    'payment_provider' => 'mollie',
                    'payment_provider_id' => 'tr_test123',
                ]);

                return [
                    'payment_id' => 'tr_test123',
                    'checkout_url' => 'https://mollie.com/checkout/test123',
                ];
            })
            ->getMock();
        app()->instance(App\Services\PaymentService::class, $paymentService);

        $response = $this->withCookie('cart_token', $cart->cart_token)
            ->post(route('checkout.store'), [
                'name' => 'Jane Doe',
                'email' => 'jane@example.com',
                'phone' => '+32470123456',
                'shipping_street' => 'Main St 1',
                'shipping_city' => 'Brussels',
                'shipping_postal_code' => '1000',
                'shipping_country' => 'Belgium',
                'billing_same_as_shipping' => 1,
                'notes' => 'Please leave at the door',
            ]);

        $order = Order::first();
        expect($order)->not->toBeNull();

        $response->assertRedirect('https://mollie.com/checkout/test123');

        expect($order->orderItems)->toHaveCount(1)
            ->and($order->shipping_address)->toBe(['street' => 'Main St 1', 'city' => 'Brussels', 'postal_code' => '1000', 'country' => 'Belgium', 'email' => 'jane@example.com'])
            ->and($order->billing_address)->toBe(['street' => 'Main St 1', 'city' => 'Brussels', 'postal_code' => '1000', 'country' => 'Belgium'])
            ->and($order->notes)->toBe('Please leave at the door')
            ->and($order->payment_provider)->toBe('mollie')
            ->and($order->payment_provider_id)->toBe('tr_test123')
            ->and($cart->refresh()->cartItems)->toHaveCount(0);

    });

    test('it creates separate billing address when checkbox is unchecked', function () {
        $product = Product::factory()->create(['price' => 2999, 'stock_quantity' => 10]);
        $cart = Cart::factory()->create();
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_name' => $product->title,
            'quantity' => 1,
            'unit_price' => 2999,
            'total_price' => 2999,
        ]);

        $paymentService = mock(App\Services\PaymentService::class)->shouldReceive('initiatePayment')
            ->once()
            ->andReturn([
                'payment_id' => 'tr_test456',
                'checkout_url' => 'https://mollie.com/checkout/test456',
            ])
            ->getMock();
        app()->instance(App\Services\PaymentService::class, $paymentService);

        $this->withCookie('cart_token', $cart->cart_token)
            ->post(route('checkout.store'), [
                'name' => 'Jane Doe',
                'email' => 'jane@example.com',
                'phone' => '+32470123456',
                'shipping_street' => 'Main St 1',
                'shipping_city' => 'Brussels',
                'shipping_postal_code' => '1000',
                'shipping_country' => 'Belgium',
                'billing_street' => 'Other St 2',
                'billing_city' => 'Antwerp',
                'billing_postal_code' => '2000',
                'billing_country' => 'Belgium',
            ]);

        $order = Order::first();
        expect($order->shipping_address['street'])->toBe('Main St 1')
            ->and($order->billing_address['street'])->toBe('Other St 2');
    });

    test('it returns validation errors for missing fields', function () {
        $product = Product::factory()->create();
        $cart = Cart::factory()->create();
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_name' => $product->title,
        ]);

        $response = $this->withCookie('cart_token', $cart->cart_token)
            ->post(route('checkout.store'), [
                'name' => '',
                'email' => 'invalid',
                'phone' => '',
                'shipping_street' => '',
                'shipping_city' => '',
                'shipping_postal_code' => '',
                'shipping_country' => '',
            ]);

        $response->assertSessionHasErrors(['name', 'email', 'shipping_street', 'shipping_city', 'shipping_postal_code', 'shipping_country']);
        expect(Order::count())->toBe(0);
    });

    test('it requires billing fields when checkbox is unchecked', function () {
        $product = Product::factory()->create();
        $cart = Cart::factory()->create();
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_name' => $product->title,
        ]);

        $response = $this->withCookie('cart_token', $cart->cart_token)
            ->post(route('checkout.store'), [
                'name' => 'Jane Doe',
                'email' => 'jane@example.com',
                'phone' => '+32470123456',
                'shipping_street' => 'Main St 1',
                'shipping_city' => 'Brussels',
                'shipping_postal_code' => '1000',
                'shipping_country' => 'Belgium',
                'billing_street' => '',
                'billing_city' => '',
                'billing_postal_code' => '',
                'billing_country' => '',
            ]);

        $response->assertSessionHasErrors(['billing_street', 'billing_city', 'billing_postal_code', 'billing_country']);
        expect(Order::count())->toBe(0);
    });

    test('it redirects to cart index when cart becomes empty before submit', function () {
        $cart = Cart::factory()->create();

        $response = $this->withCookie('cart_token', $cart->cart_token)
            ->post(route('checkout.store'), [
                'name' => 'Jane Doe',
                'email' => 'jane@example.com',
                'phone' => '+32470123456',
                'shipping_street' => 'Main St 1',
                'shipping_city' => 'Brussels',
                'shipping_postal_code' => '1000',
                'shipping_country' => 'Belgium',
                'billing_same_as_shipping' => 1,
            ]);

        $response->assertRedirect(route('cart.index'))
            ->assertSessionHas('error', __('Your cart is empty'));
    });

    test('it handles order conversion failure gracefully when stock is insufficient', function () {
        $product = Product::factory()->create(['price' => 2999, 'stock_quantity' => 1]);
        $cart = Cart::factory()->create();
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_name' => $product->title,
            'quantity' => 5,
            'unit_price' => 2999,
            'total_price' => 14995,
        ]);

        $response = $this->withCookie('cart_token', $cart->cart_token)
            ->post(route('checkout.store'), [
                'name' => 'Jane Doe',
                'email' => 'jane@example.com',
                'phone' => '+32470123456',
                'shipping_street' => 'Main St 1',
                'shipping_city' => 'Brussels',
                'shipping_postal_code' => '1000',
                'shipping_country' => 'Belgium',
                'billing_same_as_shipping' => 1,
            ]);

        $response->assertRedirect(route('cart.index'))
            ->assertSessionHas('error', __('Insufficient stock for this product'));
        expect(Order::count())->toBe(0);
    });

    test('it shows generic error message for non-stock conversion failures', function () {
        $product = Product::factory()->create(['price' => 2999, 'stock_quantity' => 10]);
        $cart = Cart::factory()->create();
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_name' => $product->title,
            'quantity' => 1,
            'unit_price' => 2999,
            'total_price' => 2999,
        ]);

        $cartService = mock(App\Services\CartService::class);
        $cartService->shouldReceive('getOrCreateCart')->once()->andReturn($cart);
        $cartService->shouldReceive('saveCustomerData')->once();
        $cartService->shouldReceive('convertToOrder')
            ->once()
            ->andThrow(new RuntimeException('SQLSTATE[23505] duplicate key value violates unique constraint'));
        app()->instance(App\Services\CartService::class, $cartService);

        $response = $this->withCookie('cart_token', $cart->cart_token)
            ->post(route('checkout.store'), [
                'name' => 'Jane Doe',
                'email' => 'jane@example.com',
                'phone' => '+32470123456',
                'shipping_street' => 'Main St 1',
                'shipping_city' => 'Brussels',
                'shipping_postal_code' => '1000',
                'shipping_country' => 'Belgium',
                'billing_same_as_shipping' => 1,
            ]);

        $response->assertRedirect(route('cart.index'))
            ->assertSessionHas('error', __('Unable to process order. Please check item availability.'));
        expect(Order::count())->toBe(0);
    });

    test('it handles payment initiation failure gracefully by redirecting to thank you page with error message', function () {
        $product = Product::factory()->create(['price' => 2999, 'stock_quantity' => 10]);
        $cart = Cart::factory()->create();
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_name' => $product->title,
            'quantity' => 1,
            'unit_price' => 2999,
            'total_price' => 2999,
        ]);

        $paymentService = mock(App\Services\PaymentService::class)
            ->shouldReceive('initiatePayment')
            ->once()
            ->andThrow(new Exception('Mollie API unavailable'))
            ->getMock();
        app()->instance(App\Services\PaymentService::class, $paymentService);

        $response = $this->withCookie('cart_token', $cart->cart_token)
            ->post(route('checkout.store'), [
                'name' => 'Jane Doe',
                'email' => 'jane@example.com',
                'phone' => '+32470123456',
                'shipping_street' => 'Main St 1',
                'shipping_city' => 'Brussels',
                'shipping_postal_code' => '1000',
                'shipping_country' => 'Belgium',
                'billing_same_as_shipping' => 1,
            ]);

        $order = Order::first();
        expect($order)->not->toBeNull();

        $response->assertRedirect(route('order.confirmed', $order))
            ->assertSessionHas('error', __('Payment initialization failed. You can try paying again below.'));
    });
});

describe('thank you page', function () {
    test('it shows the thank you page with order details for authenticated users', function () {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 2999]);
        $order = Order::factory()->create(['user_id' => $user->id, 'subtotal' => 5998, 'payment_status' => PaymentStatus::Unpaid]);
        App\Models\OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->title,
            'quantity' => 2,
            'unit_price' => 2999,
            'total_price' => 5998,
        ]);

        $response = $this->actingAs($user)
            ->get(route('order.confirmed', $order));

        $response->assertSuccessful()
            ->assertViewIs('public.checkout.thankyou')
            ->assertSeeText($order->order_number)
            ->assertSeeText($product->title)
            ->assertSeeText(__('Pay now'));
    });

    test('it shows the thank you page for guests with the last order id in session', function () {
        $product = Product::factory()->create(['price' => 2999]);
        $order = Order::factory()->create(['user_id' => null, 'subtotal' => 5998]);
        App\Models\OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->title,
            'quantity' => 2,
            'unit_price' => 2999,
            'total_price' => 5998,
        ]);

        $response = $this->withSession(['last_guest_order_id' => $order->id])
            ->get(route('order.confirmed', $order));

        $response->assertSuccessful()
            ->assertViewIs('public.checkout.thankyou')
            ->assertSeeText($order->order_number);
    });

    test('it returns 403 when authenticated user views another users order', function () {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user2->id]);

        $response = $this->actingAs($user1)
            ->get(route('order.confirmed', $order));

        $response->assertForbidden();
    });

    test('it returns 403 when guest views an order without session data', function () {
        $order = Order::factory()->create(['user_id' => null]);

        $response = $this->get(route('order.confirmed', $order));

        $response->assertForbidden();
    });

    test('it returns 403 when guest views a different order than the last one', function () {
        $order1 = Order::factory()->create(['user_id' => null]);
        $order2 = Order::factory()->create(['user_id' => null]);

        $response = $this->withSession(['last_guest_order_id' => $order1->id])
            ->get(route('order.confirmed', $order2));

        $response->assertForbidden();
    });

    test('it shows the thank you page for guests with a signed url', function () {
        $product = Product::factory()->create(['price' => 2999]);
        $order = Order::factory()->create(['user_id' => null, 'subtotal' => 5998]);
        App\Models\OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->title,
            'quantity' => 2,
            'unit_price' => 2999,
            'total_price' => 5998,
        ]);

        $signedUrl = Illuminate\Support\Facades\URL::signedRoute('order.confirmed', ['order' => $order]);

        $response = $this->get($signedUrl);

        $response->assertSuccessful()
            ->assertViewIs('public.checkout.thankyou')
            ->assertSeeText($order->order_number);
    });

    test('it sets head metadata on thank you page', function () {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->get(route('order.confirmed', $order));

        $html = Head::toHtml();

        expect($html)->toContain(e(__('Thank you for your order')));
    });
});

describe('retry payment', function () {
    test('authenticated user can retry payment for their own order', function () {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'payment_status' => PaymentStatus::Unpaid,
        ]);

        $paymentService = mock(App\Services\PaymentService::class)
            ->shouldReceive('initiatePayment')
            ->once()
            ->with(Mockery::on(fn ($arg) => $arg->is($order)))
            ->andReturn([
                'payment_id' => 'tr_retry123',
                'checkout_url' => 'https://mollie.com/checkout/retry123',
            ])
            ->getMock();
        app()->instance(App\Services\PaymentService::class, $paymentService);

        $response = $this->actingAs($user)
            ->post(route('checkout.pay', $order));

        $response->assertRedirect('https://mollie.com/checkout/retry123');
    });

    test('guest user with session can retry payment', function () {
        $order = Order::factory()->create([
            'user_id' => null,
            'payment_status' => PaymentStatus::Unpaid,
        ]);

        $paymentService = mock(App\Services\PaymentService::class)
            ->shouldReceive('initiatePayment')
            ->once()
            ->andReturn([
                'payment_id' => 'tr_retry456',
                'checkout_url' => 'https://mollie.com/checkout/retry456',
            ])
            ->getMock();
        app()->instance(App\Services\PaymentService::class, $paymentService);

        $response = $this->withSession(['last_guest_order_id' => $order->id])
            ->post(route('checkout.pay', $order));

        $response->assertRedirect('https://mollie.com/checkout/retry456');
    });

    test('redirects with message if order is already paid', function () {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'payment_status' => PaymentStatus::Paid,
        ]);

        $response = $this->actingAs($user)
            ->post(route('checkout.pay', $order));

        $response->assertRedirect(route('order.confirmed', $order))
            ->assertSessionHas('success', __('Order is already paid'));
    });

    test('redirects with error if order is cancelled', function () {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'payment_status' => PaymentStatus::Unpaid,
            'status' => App\Enums\OrderStatus::Cancelled,
        ]);

        $response = $this->actingAs($user)
            ->post(route('checkout.pay', $order));

        $response->assertRedirect(route('order.confirmed', $order))
            ->assertSessionHas('error', __('This order has been cancelled and cannot be paid. Please place a new order.'));
    });

    test('returns 403 when authenticated user retries another users order', function () {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user2->id,
            'payment_status' => PaymentStatus::Unpaid,
        ]);

        $response = $this->actingAs($user1)
            ->post(route('checkout.pay', $order));

        $response->assertForbidden();
    });

    test('returns 403 when guest retries payment without session data', function () {
        $order = Order::factory()->create([
            'user_id' => null,
            'payment_status' => PaymentStatus::Unpaid,
        ]);

        $response = $this->post(route('checkout.pay', $order));

        $response->assertForbidden();
    });

    test('guest can retry payment with a signed url', function () {
        $order = Order::factory()->create([
            'user_id' => null,
            'payment_status' => PaymentStatus::Unpaid,
        ]);

        $paymentService = mock(App\Services\PaymentService::class)
            ->shouldReceive('initiatePayment')
            ->once()
            ->with(Mockery::on(fn ($arg) => $arg->is($order)))
            ->andReturn([
                'payment_id' => 'tr_signed_retry',
                'checkout_url' => 'https://mollie.com/checkout/signed-retry',
            ])
            ->mock();

        app()->instance(App\Services\PaymentService::class, $paymentService);

        $signedUrl = Illuminate\Support\Facades\URL::signedRoute('checkout.pay', ['order' => $order]);

        $response = $this->post($signedUrl);

        $response->assertRedirect('https://mollie.com/checkout/signed-retry');
    });

    test('handles retry payment failure gracefully', function () {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'payment_status' => PaymentStatus::Unpaid,
        ]);

        $paymentService = mock(App\Services\PaymentService::class)
            ->shouldReceive('initiatePayment')
            ->once()
            ->andThrow(new Exception('Mollie API error'))
            ->getMock();
        app()->instance(App\Services\PaymentService::class, $paymentService);

        $response = $this->actingAs($user)
            ->post(route('checkout.pay', $order));

        $response->assertRedirect(route('order.confirmed', $order))
            ->assertSessionHas('error', __('Payment initialization failed. Please try again.'));
    });
});

describe('guest login/register option', function () {
    test('guest cart page shows login and register links', function () {
        $product = Product::factory()->create(['stock_quantity' => 10]);
        $cart = Cart::factory()->guest()->create();
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_name' => $product->title,
            'quantity' => 1,
            'unit_price' => $product->price,
            'total_price' => $product->price,
        ]);

        $response = $this->withCookie('cart_token', $cart->cart_token)
            ->get(route('cart.index'));

        $response->assertSuccessful()
            ->assertSee(__('Already have an account?'))
            ->assertSee(__('Log in'))
            ->assertSee(__('create an account to track your order'))
            ->assertSee(__('Proceed as guest'))
            ->assertSee(route('login'))
            ->assertSee(route('register'));
    });

    test('authenticated cart page shows user info', function () {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 10]);
        $cart = Cart::factory()->create(['user_id' => $user->id, 'is_guest' => false]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_name' => $product->title,
            'quantity' => 1,
            'unit_price' => $product->price,
            'total_price' => $product->price,
        ]);

        $response = $this->actingAs($user)
            ->get(route('cart.index'));

        $response->assertSuccessful()
            ->assertSee(__('Logged in as'))
            ->assertSee($user->name)
            ->assertSee(__('Proceed to checkout'));
    });

    test('guest checkout page shows login and register links', function () {
        $product = Product::factory()->create(['stock_quantity' => 10]);
        $cart = Cart::factory()->guest()->create();
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_name' => $product->title,
            'quantity' => 1,
            'unit_price' => $product->price,
            'total_price' => $product->price,
        ]);

        $response = $this->withCookie('cart_token', $cart->cart_token)
            ->get(route('checkout.index'));

        $response->assertSuccessful()
            ->assertSee(__('Already have an account?'))
            ->assertSee(__('Log in'))
            ->assertSee(__('create an account to track your order'))
            ->assertSee(route('login'))
            ->assertSee(route('register'));
    });

    test('authenticated checkout page does not show login option', function () {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 10]);
        $cart = Cart::factory()->create(['user_id' => $user->id, 'is_guest' => false]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_name' => $product->title,
            'quantity' => 1,
            'unit_price' => $product->price,
            'total_price' => $product->price,
        ]);

        $response = $this->actingAs($user)
            ->get(route('checkout.index'));

        $response->assertSuccessful()
            ->assertDontSee(__('Already have an account?'))
            ->assertDontSee(__('Proceed as guest'));
    });
});

describe('guest cart persistence', function () {
    test('guest cart persists across requests via cookie', function () {
        $product = Product::factory()->create(['stock_quantity' => 10]);

        $this->post(route('cart.add', $product));

        $cart = Cart::first();
        $response = $this->withCookie('cart_token', $cart->cart_token)
            ->get(route('cart.index'));

        $response->assertSuccessful()
            ->assertViewHas('cart', fn (Cart $viewCart) => $viewCart->id === $cart->id);

        expect($cart->cartItems)->toHaveCount(1);
    });
});

describe('rate limiting', function () {
    test('cart add endpoint is rate limited', function () {
        $product = Product::factory()->create(['stock_quantity' => 100]);

        // Make 10 requests (within the limit)
        for ($i = 0; $i < 10; $i++) {
            $response = $this->post(route('cart.add', $product));
            $response->assertRedirect();
        }

        // The 11th request should be rate limited
        $response = $this->post(route('cart.add', $product));
        $response->assertStatus(429);
    });

    test('checkout store endpoint is rate limited', function () {
        $user = User::factory()->create();

        $validData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '+32470123456',
            'shipping_street' => 'Test Street 1',
            'shipping_city' => 'Brussels',
            'shipping_postal_code' => '1000',
            'shipping_country' => 'Belgium',
            'billing_same_as_shipping' => 'on',
        ];

        // Make 10 requests (within the limit) — they may fail for other reasons (empty cart)
        // but should not be rate limited until the 11th
        for ($i = 0; $i < 10; $i++) {
            $this->actingAs($user)
                ->post(route('checkout.store'), $validData);
        }

        // The 11th request should be rate limited
        $response = $this
            ->actingAs($user)
            ->post(route('checkout.store'), $validData);
        $response->assertStatus(429);
    });

    test('cart update endpoint is rate limited', function () {
        $product = Product::factory()->create(['stock_quantity' => 100]);
        $cart = Cart::factory()->guest()->create();
        $item = CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_name' => $product->title,
            'quantity' => 1,
            'unit_price' => $product->price,
            'total_price' => $product->price,
        ]);

        // Make 10 requests (within the limit)
        for ($i = 0; $i < 10; $i++) {
            $this
                ->withCookie('cart_token', $cart->cart_token)
                ->post(route('cart.items.update', $item), ['quantity' => 1]);
        }

        // The 11th request should be rate limited
        $response = $this->withCookie('cart_token', $cart->cart_token)
            ->post(route('cart.items.update', $item), ['quantity' => 1]);
        $response->assertStatus(429);
    });
});
