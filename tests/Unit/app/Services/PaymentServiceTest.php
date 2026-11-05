<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Contracts\PaymentProvider;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Mail\PaymentConfirmation;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    User::factory()->create();
});

test('it initiates payment and saves provider info to order', function () {
    $order = Order::factory()->create();

    $mockProvider = mock(PaymentProvider::class);
    $mockProvider->shouldReceive('createPayment')
        ->once()
        ->with($order)
        ->andReturn([
            'payment_id' => 'tr_Test123',
            'checkout_url' => 'https://mollie.com/checkout/test123',
        ]);

    config(['payment.default' => 'mollie', 'payment.providers.mollie' => PaymentProvider::class]);

    $service = app(PaymentService::class);
    app()->instance(PaymentProvider::class, $mockProvider);

    $result = $service->initiatePayment($order);

    expect($result)->toBe([
        'payment_id' => 'tr_Test123',
        'checkout_url' => 'https://mollie.com/checkout/test123',
    ]);

    $order->refresh();
    expect($order->payment_provider)->toBe('mollie')
        ->and($order->payment_provider_id)->toBe('tr_Test123');
});

test('it handles webhook with valid signature and paid status', function () {
    $order = Order::factory()->create([
        'payment_status' => PaymentStatus::Paid,
        'status' => OrderStatus::Confirmed,
        'shipping_address' => ['email' => 'customer@example.com'],
    ]);
    $order->was_already_paid = false;
    OrderItem::factory()->create(['order_id' => $order->id]);

    $payload = 'id=tr_Test123';

    $mockProvider = mock(PaymentProvider::class);
    $mockProvider->shouldReceive('verifySignature')
        ->once()
        ->with($payload, 'valid-signature')
        ->andReturn(true);
    $mockProvider->shouldReceive('handleWebhook')
        ->once()
        ->with($payload, 'valid-signature')
        ->andReturn($order);

    Mail::fake();

    config(['payment.default' => 'mollie', 'payment.providers.mollie' => PaymentProvider::class]);
    $service = app(PaymentService::class);
    app()->instance(PaymentProvider::class, $mockProvider);

    $result = $service->handleWebhook($payload, 'valid-signature');

    expect($result)->toBeTrue();

    Mail::assertQueued(PaymentConfirmation::class, function ($mail) use ($order) {
        return $mail->hasTo('customer@example.com')
            && $mail->order->is($order);
    });
});

test('it does not send duplicate confirmation email on webhook retry for already paid order', function () {
    $order = Order::factory()->create([
        'payment_status' => PaymentStatus::Paid,
        'status' => OrderStatus::Confirmed,
        'shipping_address' => ['email' => 'customer@example.com'],
    ]);
    $order->was_already_paid = true;
    OrderItem::factory()->create(['order_id' => $order->id]);

    $payload = 'id=tr_Test123';

    $mockProvider = mock(PaymentProvider::class);
    $mockProvider->shouldReceive('verifySignature')
        ->once()
        ->with($payload, '')
        ->andReturn(true);
    $mockProvider->shouldReceive('handleWebhook')
        ->once()
        ->with($payload, '')
        ->andReturn($order);

    Mail::fake();

    config(['payment.default' => 'mollie', 'payment.providers.mollie' => PaymentProvider::class]);
    $service = app(PaymentService::class);
    app()->instance(PaymentProvider::class, $mockProvider);

    $result = $service->handleWebhook($payload);

    expect($result)->toBeTrue();

    // Idempotency: no email sent for already-paid order retry
    Mail::assertNothingQueued();
});

test('it returns false when webhook signature is invalid', function () {
    $mockProvider = mock(PaymentProvider::class);
    $mockProvider->shouldReceive('verifySignature')
        ->once()
        ->andReturn(false);
    $mockProvider->shouldNotReceive('handleWebhook');

    Mail::fake();

    config(['payment.default' => 'mollie', 'payment.providers.mollie' => PaymentProvider::class]);
    $service = app(PaymentService::class);
    app()->instance(PaymentProvider::class, $mockProvider);

    $result = $service->handleWebhook('payload', 'invalid-signature');

    expect($result)->toBeFalse();

    Mail::assertNothingQueued();
});

test('it returns false and sends no email when payment is not paid', function () {
    $order = Order::factory()->create([
        'payment_status' => PaymentStatus::Unpaid,
        'status' => OrderStatus::Pending,
    ]);

    $payload = 'id=tr_Test123';

    $mockProvider = mock(PaymentProvider::class);
    $mockProvider->shouldReceive('verifySignature')
        ->once()
        ->andReturn(true);
    $mockProvider->shouldReceive('handleWebhook')
        ->once()
        ->andReturn($order);

    Mail::fake();

    config(['payment.default' => 'mollie', 'payment.providers.mollie' => PaymentProvider::class]);
    $service = app(PaymentService::class);
    app()->instance(PaymentProvider::class, $mockProvider);

    $result = $service->handleWebhook($payload, 'valid-signature');

    expect($result)->toBeFalse();

    Mail::assertNothingQueued();
});

test('it skips email when order has no customer email', function () {
    $order = Order::factory()->create([
        'payment_status' => PaymentStatus::Paid,
        'status' => OrderStatus::Confirmed,
        'shipping_address' => ['street' => 'Teststraat 1', 'city' => 'Teststad'],
    ]);
    $order->was_already_paid = false;

    $payload = 'id=tr_Test123';

    $mockProvider = mock(PaymentProvider::class);
    $mockProvider->shouldReceive('verifySignature')
        ->once()
        ->andReturn(true);
    $mockProvider->shouldReceive('handleWebhook')
        ->once()
        ->andReturn($order);

    Mail::fake();

    config(['payment.default' => 'mollie', 'payment.providers.mollie' => PaymentProvider::class]);
    $service = app(PaymentService::class);
    app()->instance(PaymentProvider::class, $mockProvider);

    $result = $service->handleWebhook($payload, 'valid-signature');

    expect($result)->toBeTrue();

    Mail::assertNothingQueued();
});

test('it returns false when provider returns null', function () {
    $mockProvider = mock(PaymentProvider::class);
    $mockProvider->shouldReceive('verifySignature')
        ->once()
        ->andReturn(true);
    $mockProvider->shouldReceive('handleWebhook')
        ->once()
        ->andReturn(null);

    Mail::fake();

    config(['payment.default' => 'mollie', 'payment.providers.mollie' => PaymentProvider::class]);
    $service = app(PaymentService::class);
    app()->instance(PaymentProvider::class, $mockProvider);

    $result = $service->handleWebhook('id=invalid', 'valid-signature');

    expect($result)->toBeFalse();

    Mail::assertNothingQueued();
});

test('it throws BindingResolutionException when provider class is not configured or does not exist', function () {
    $order = Order::factory()->create();

    config(['payment.default' => 'unknown', 'payment.providers.unknown' => 'NonExistentClass']);
    $service = app(PaymentService::class);

    expect(fn () => $service->initiatePayment($order))
        ->toThrow(BindingResolutionException::class);
});

test('it returns false and reports exception when webhook processing encounters an unexpected error', function () {
    config(['payment.default' => 'invalid_provider', 'payment.providers.invalid_provider' => null]);
    $service = app(PaymentService::class);

    $result = $service->handleWebhook('id=tr_Test123');

    expect($result)->toBeFalse();
});

// --------------------------------------------------------------------------
// Stock Restoration on Payment Failure
// --------------------------------------------------------------------------

test('it restores product stock when payment fails', function () {
    $product = Product::factory()->create(['price' => 1000, 'is_discounted' => false, 'stock_quantity' => 10]);

    // Simulate stock already reserved at checkout
    $product->update(['stock_quantity' => 7]);

    $order = Order::factory()->create([
        'payment_status' => PaymentStatus::Unpaid,
        'status' => OrderStatus::Pending,
    ]);
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_variant_id' => null,
        'quantity' => 3,
    ]);

    $payload = 'id=tr_Test123';

    $mockProvider = mock(PaymentProvider::class);
    $mockProvider->shouldReceive('verifySignature')->once()->andReturn(true);
    $mockProvider->shouldReceive('handleWebhook')->once()->andReturn($order);

    Mail::fake();

    config(['payment.default' => 'mollie', 'payment.providers.mollie' => PaymentProvider::class]);
    $service = app(PaymentService::class);
    app()->instance(PaymentProvider::class, $mockProvider);

    $result = $service->handleWebhook($payload, 'valid-signature');

    expect($result)->toBeFalse()
        ->and($product->fresh()->stock_quantity)->toBe(10);

    Mail::assertNothingQueued();
});

test('it restores variant stock when payment fails', function () {
    $product = Product::factory()->create(['price' => 1000, 'is_discounted' => false, 'stock_quantity' => 100]);
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'price' => 1500,
        'is_discounted' => false,
        'stock_quantity' => 3,
    ]);

    $order = Order::factory()->create([
        'payment_status' => PaymentStatus::Unpaid,
        'status' => OrderStatus::Pending,
    ]);
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_variant_id' => $variant->id,
        'quantity' => 2,
    ]);

    $payload = 'id=tr_Test123';

    $mockProvider = mock(PaymentProvider::class);
    $mockProvider->shouldReceive('verifySignature')->once()->andReturn(true);
    $mockProvider->shouldReceive('handleWebhook')->once()->andReturn($order);

    Mail::fake();

    config(['payment.default' => 'mollie', 'payment.providers.mollie' => PaymentProvider::class]);
    $service = app(PaymentService::class);
    app()->instance(PaymentProvider::class, $mockProvider);

    $result = $service->handleWebhook($payload, 'valid-signature');

    expect($result)->toBeFalse()
        ->and($variant->fresh()->stock_quantity)->toBe(5)
        ->and($product->fresh()->stock_quantity)->toBe(100);
});

test('it does not restore stock when payment is successful', function () {
    $product = Product::factory()->create(['price' => 1000, 'is_discounted' => false, 'stock_quantity' => 7]);

    $order = Order::factory()->create([
        'payment_status' => PaymentStatus::Paid,
        'status' => OrderStatus::Confirmed,
        'shipping_address' => ['email' => 'customer@example.com'],
    ]);
    $order->was_already_paid = false;
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_variant_id' => null,
        'quantity' => 3,
    ]);

    $payload = 'id=tr_Test123';

    $mockProvider = mock(PaymentProvider::class);
    $mockProvider->shouldReceive('verifySignature')->once()->andReturn(true);
    $mockProvider->shouldReceive('handleWebhook')->once()->andReturn($order);

    Mail::fake();

    config(['payment.default' => 'mollie', 'payment.providers.mollie' => PaymentProvider::class]);
    $service = app(PaymentService::class);
    app()->instance(PaymentProvider::class, $mockProvider);

    $result = $service->handleWebhook($payload, 'valid-signature');

    expect($result)->toBeTrue()
        ->and($product->fresh()->stock_quantity)->toBe(7);
});
