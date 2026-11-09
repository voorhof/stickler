<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Contracts\PaymentProvider;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\WebhookResult;
use App\Exceptions\InvalidWebhookSignatureException;
use App\Mail\PaymentConfirmation;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
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

test('it handles webhook with paid status', function () {
    $order = Order::factory()->create([
        'payment_status' => PaymentStatus::Paid,
        'status' => OrderStatus::Confirmed,
        'shipping_address' => ['email' => 'customer@example.com'],
    ]);
    $order->was_already_paid = false;
    OrderItem::factory()->create(['order_id' => $order->id]);

    $payload = 'id=tr_Test123';

    $mockProvider = mock(PaymentProvider::class);
    $mockProvider->shouldReceive('handleWebhook')
        ->once()
        ->with($payload, 'valid-signature')
        ->andReturn($order);

    Mail::fake();

    config(['payment.default' => 'mollie', 'payment.providers.mollie' => PaymentProvider::class]);
    $service = app(PaymentService::class);
    app()->instance(PaymentProvider::class, $mockProvider);

    $result = $service->handleWebhook($payload, 'valid-signature');

    expect($result)->toBe(WebhookResult::Success);

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
    $mockProvider->shouldReceive('handleWebhook')
        ->once()
        ->with($payload, '')
        ->andReturn($order);

    Mail::fake();

    config(['payment.default' => 'mollie', 'payment.providers.mollie' => PaymentProvider::class]);
    $service = app(PaymentService::class);
    app()->instance(PaymentProvider::class, $mockProvider);

    $result = $service->handleWebhook($payload);

    expect($result)->toBe(WebhookResult::Success);

    // Idempotency: no email sent for already-paid order retry
    Mail::assertNothingQueued();
});

test('it returns bad payload when payload is empty', function () {
    Mail::fake();

    config(['payment.default' => 'mollie', 'payment.providers.mollie' => PaymentProvider::class]);
    $service = app(PaymentService::class);

    $result = $service->handleWebhook('');

    expect($result)->toBe(WebhookResult::BadPayload);

    Mail::assertNothingQueued();
});

test('it sends no email when payment is not paid', function () {
    $order = Order::factory()->create([
        'payment_status' => PaymentStatus::Unpaid,
        'status' => OrderStatus::Pending,
    ]);

    $payload = 'id=tr_Test123';

    $mockProvider = mock(PaymentProvider::class);
    $mockProvider->shouldReceive('handleWebhook')
        ->once()
        ->andReturn($order);

    Mail::fake();

    config(['payment.default' => 'mollie', 'payment.providers.mollie' => PaymentProvider::class]);
    $service = app(PaymentService::class);
    app()->instance(PaymentProvider::class, $mockProvider);

    $result = $service->handleWebhook($payload, 'valid-signature');

    expect($result)->toBe(WebhookResult::Success);

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
    $mockProvider->shouldReceive('handleWebhook')
        ->once()
        ->andReturn($order);

    Mail::fake();

    config(['payment.default' => 'mollie', 'payment.providers.mollie' => PaymentProvider::class]);
    $service = app(PaymentService::class);
    app()->instance(PaymentProvider::class, $mockProvider);

    $result = $service->handleWebhook($payload, 'valid-signature');

    expect($result)->toBe(WebhookResult::Success);

    Mail::assertNothingQueued();
});

test('it returns not found when provider returns null', function () {
    $mockProvider = mock(PaymentProvider::class);
    $mockProvider->shouldReceive('handleWebhook')
        ->once()
        ->andReturn(null);

    Mail::fake();

    config(['payment.default' => 'mollie', 'payment.providers.mollie' => PaymentProvider::class]);
    $service = app(PaymentService::class);
    app()->instance(PaymentProvider::class, $mockProvider);

    $result = $service->handleWebhook('id=invalid', 'valid-signature');

    expect($result)->toBe(WebhookResult::NotFound);

    Mail::assertNothingQueued();
});

test('it throws BindingResolutionException when provider class is not configured or does not exist', function () {
    $order = Order::factory()->create();

    config(['payment.default' => 'unknown', 'payment.providers.unknown' => 'NonExistentClass']);
    $service = app(PaymentService::class);

    expect(fn () => $service->initiatePayment($order))
        ->toThrow(BindingResolutionException::class);
});

test('it returns error and reports exception when webhook processing encounters an unexpected error', function () {
    config(['payment.default' => 'invalid_provider', 'payment.providers.invalid_provider' => null]);
    $service = app(PaymentService::class);

    $result = $service->handleWebhook('id=tr_Test123');

    expect($result)->toBe(WebhookResult::Error);
});

test('it returns signature invalid when provider throws signature exception', function () {
    $mockProvider = mock(PaymentProvider::class);
    $mockProvider->shouldReceive('handleWebhook')
        ->once()
        ->andThrow(InvalidWebhookSignatureException::class);

    Mail::fake();

    config(['payment.default' => 'mollie', 'payment.providers.mollie' => PaymentProvider::class]);
    $service = app(PaymentService::class);
    app()->instance(PaymentProvider::class, $mockProvider);

    $result = $service->handleWebhook('id=tr_Test123', 'bad-signature');

    expect($result)->toBe(WebhookResult::SignatureInvalid);

    Mail::assertNothingQueued();
});

// --------------------------------------------------------------------------
// Stock Reservation and Webhook Handling
// --------------------------------------------------------------------------

test('it does not restore stock during webhook processing when order remains pending unpaid (retains stock for retry)', function () {
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
    $mockProvider->shouldReceive('handleWebhook')->once()->andReturn($order);

    Mail::fake();

    config(['payment.default' => 'mollie', 'payment.providers.mollie' => PaymentProvider::class]);
    $service = app(PaymentService::class);
    app()->instance(PaymentProvider::class, $mockProvider);

    $result = $service->handleWebhook($payload, 'valid-signature');

    expect($result)->toBe(WebhookResult::Success)
        ->and($product->fresh()->stock_quantity)->toBe(7);

    Mail::assertNothingQueued();
});

test('it restores stock when webhook cancels the order on failed payment', function () {
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

    // Simulate the provider cancelling the order on failed payment
    $mockProvider = mock(PaymentProvider::class);
    $mockProvider->shouldReceive('handleWebhook')
        ->once()
        ->andReturnUsing(function () use ($order) {
            $order->update([
                'payment_status' => PaymentStatus::Unpaid->value,
                'status' => OrderStatus::Cancelled->value,
            ]);

            return $order->fresh();
        });

    Mail::fake();

    config(['payment.default' => 'mollie', 'payment.providers.mollie' => PaymentProvider::class]);
    $service = app(PaymentService::class);
    app()->instance(PaymentProvider::class, $mockProvider);

    $result = $service->handleWebhook($payload, 'valid-signature');

    expect($result)->toBe(WebhookResult::Success)
        ->and($order->fresh()->status)->toBe(OrderStatus::Cancelled)
        ->and($product->fresh()->stock_quantity)->toBe(10);

    Mail::assertNothingQueued();
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
    $mockProvider->shouldReceive('handleWebhook')->once()->andReturn($order);

    Mail::fake();

    config(['payment.default' => 'mollie', 'payment.providers.mollie' => PaymentProvider::class]);
    $service = app(PaymentService::class);
    app()->instance(PaymentProvider::class, $mockProvider);

    $result = $service->handleWebhook($payload, 'valid-signature');

    expect($result)->toBe(WebhookResult::Success)
        ->and($product->fresh()->stock_quantity)->toBe(7);
});
