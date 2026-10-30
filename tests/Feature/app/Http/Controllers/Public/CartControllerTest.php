<?php

/** @noinspection PhpUnhandledExceptionInspection */

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
            'sku' => 'VARIANT-SKU',
        ]);

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
            ->toThrow(Exception::class, 'Insufficient stock');
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

        $response->assertRedirect(route('order.confirmed', ['order' => $order]))
            ->assertSessionHas('success', __('Order created successfully'));

        expect($order->orderItems)->toHaveCount(1)
            ->and($order->shipping_address)->toBe(['street' => 'Main St 1', 'city' => 'Brussels', 'postal_code' => '1000', 'country' => 'Belgium'])
            ->and($order->billing_address)->toBe(['street' => 'Main St 1', 'city' => 'Brussels', 'postal_code' => '1000', 'country' => 'Belgium'])
            ->and($order->notes)->toBe('Please leave at the door')
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
});

describe('thank you page', function () {
    test('it shows the thank you page with order details', function () {
        $product = Product::factory()->create(['price' => 2999]);
        $order = Order::factory()->create(['subtotal' => 5998]);
        App\Models\OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->title,
            'quantity' => 2,
            'unit_price' => 2999,
            'total_price' => 5998,
        ]);

        $response = $this->get(route('order.confirmed', $order));

        $response->assertSuccessful()
            ->assertViewIs('public.checkout.thankyou')
            ->assertSeeText('#'.$order->id)
            ->assertSeeText($product->title);
    });

    test('it sets head metadata on thank you page', function () {
        $order = Order::factory()->create();

        $this->get(route('order.confirmed', $order));

        $html = Head::toHtml();

        expect($html)->toContain(e(__('Thank you for your order')));
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
