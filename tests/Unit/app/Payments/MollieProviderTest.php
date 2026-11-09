<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Exceptions\InvalidWebhookSignatureException;
use App\Models\Order;
use App\Models\User;
use App\Payments\MollieProvider;
use Mollie\Api\Fake\MockResponse;
use Mollie\Api\Http\Data\Money;
use Mollie\Api\Http\PendingRequest;
use Mollie\Api\Http\Requests\CreatePaymentRequest;
use Mollie\Api\Http\Requests\GetPaymentRequest;
use Mollie\Api\Resources\Payment;
use Mollie\Api\Types\PaymentStatus as MolliePaymentStatus;
use Mollie\Laravel\Facades\Mollie;

beforeEach(function () {
    User::factory()->create();
});

test('it creates a payment with correct amount and metadata', function () {
    $order = Order::factory()->create(['total' => 2500, 'order_number' => 'ORD-001']);

    Mollie::fake([
        CreatePaymentRequest::class => MockResponse::created([
            'id' => 'tr_Test123',
            'mode' => 'test',
            'amount' => Money::fromMinorUnits('EUR', 2500)->toArray(),
            'description' => __('Order :order_number', ['order_number' => $order->order_number]),
            'status' => 'open',
            'profileId' => 'pfl_test123',
            'createdAt' => now()->toIso8601String(),
            '_links' => json_decode(json_encode([
                'checkout' => [
                    'href' => 'https://mollie.com/checkout/test123',
                ],
            ])),
        ], Payment::class),
    ]);

    $provider = new MollieProvider;
    $result = $provider->createPayment($order);

    expect($result)->toBe([
        'payment_id' => 'tr_Test123',
        'checkout_url' => 'https://mollie.com/checkout/test123',
    ]);

    Mollie::assertSent(CreatePaymentRequest::class);
});

test('it does not include webhook url in non-production environments', function () {
    $order = Order::factory()->create(['total' => 2500, 'order_number' => 'ORD-001']);

    Mollie::fake([
        CreatePaymentRequest::class => MockResponse::created([
            'id' => 'tr_Test123',
            'mode' => 'test',
            'amount' => Money::fromMinorUnits('EUR', 2500)->toArray(),
            'description' => __('Order :order_number', ['order_number' => $order->order_number]),
            'status' => 'open',
            'profileId' => 'pfl_test123',
            'createdAt' => now()->toIso8601String(),
            '_links' => json_decode(json_encode([
                'checkout' => [
                    'href' => 'https://mollie.com/checkout/test123',
                ],
            ])),
        ], Payment::class),
    ]);

    $provider = new MollieProvider;
    $provider->createPayment($order);

    // In testing (non-production), webhookUrl should not be set
    Mollie::assertSent(function (PendingRequest $pendingRequest) {
        /** @var CreatePaymentRequest $request */
        $request = $pendingRequest->getRequest();

        return $request->payload()->get('webhookUrl') === null;
    });
});

test('it includes webhook url in production environment', function () {
    app()->detectEnvironment(fn () => 'production');

    $order = Order::factory()->create(['total' => 2500, 'order_number' => 'ORD-001']);

    Mollie::fake([
        CreatePaymentRequest::class => MockResponse::created([
            'id' => 'tr_Test123',
            'mode' => 'test',
            'amount' => Money::fromMinorUnits('EUR', 2500)->toArray(),
            'description' => __('Order :order_number', ['order_number' => $order->order_number]),
            'status' => 'open',
            'profileId' => 'pfl_test123',
            'createdAt' => now()->toIso8601String(),
            '_links' => json_decode(json_encode([
                'checkout' => [
                    'href' => 'https://mollie.com/checkout/test123',
                ],
            ])),
        ], Payment::class),
    ]);

    $provider = new MollieProvider;
    $provider->createPayment($order);

    Mollie::assertSent(function (PendingRequest $pendingRequest) {
        /** @var CreatePaymentRequest $request */
        $request = $pendingRequest->getRequest();

        return $request->payload()->get('webhookUrl') === route('payment.webhook.mollie');
    });
});

test('it uses a signed url for the redirect url', function () {
    $order = Order::factory()->create(['total' => 2500, 'order_number' => 'ORD-001']);

    $capturedRedirectUrl = null;

    Mollie::fake([
        CreatePaymentRequest::class => function (PendingRequest $pendingRequest) use ($order, &$capturedRedirectUrl) {
            /** @var CreatePaymentRequest $request */
            $request = $pendingRequest->getRequest();
            $capturedRedirectUrl = $request->payload()->get('redirectUrl');

            return MockResponse::created([
                'id' => 'tr_Test123',
                'mode' => 'test',
                'amount' => Money::fromMinorUnits('EUR', 2500)->toArray(),
                'description' => __('Order :order_number', ['order_number' => $order->order_number]),
                'status' => 'open',
                'profileId' => 'pfl_test123',
                'createdAt' => now()->toIso8601String(),
                '_links' => json_decode(json_encode([
                    'checkout' => [
                        'href' => 'https://mollie.com/checkout/test123',
                    ],
                ])),
            ], Payment::class);
        },
    ]);

    $provider = new MollieProvider;
    $provider->createPayment($order);

    expect($capturedRedirectUrl)->not->toBeNull()
        ->and($capturedRedirectUrl)->toContain('order-confirmed/'.$order->id)
        ->and($capturedRedirectUrl)->toContain('signature=');
});

test('it uses configured payment webhook url when provided', function () {
    config(['payment.webhook_url' => 'https://custom-webhook.example.com/payment']);

    $order = Order::factory()->create(['total' => 25.00, 'order_number' => 'ORD-001']);

    Mollie::fake([
        CreatePaymentRequest::class => MockResponse::created([
            'id' => 'tr_Test123',
            'mode' => 'test',
            'amount' => Money::fromMinorUnits('EUR', 2500)->toArray(),
            'description' => __('Order :order_number', ['order_number' => $order->order_number]),
            'status' => 'open',
            'profileId' => 'pfl_test123',
            'createdAt' => now()->toIso8601String(),
            '_links' => json_decode(json_encode([
                'checkout' => [
                    'href' => 'https://mollie.com/checkout/test123',
                ],
            ])),
        ], Payment::class),
    ]);

    $provider = new MollieProvider;
    $provider->createPayment($order);

    Mollie::assertSent(function (PendingRequest $pendingRequest) {
        /** @var CreatePaymentRequest $request */
        $request = $pendingRequest->getRequest();

        return $request->payload()->get('webhookUrl') === 'https://custom-webhook.example.com/payment';
    });
});

test('it handles a paid webhook and updates order status with form payload', function () {
    $order = Order::factory()->create([
        'payment_status' => PaymentStatus::Unpaid,
        'status' => OrderStatus::Pending,
    ]);

    Mollie::fake([
        GetPaymentRequest::class => MockResponse::ok([
            'id' => 'tr_Test123',
            'mode' => 'test',
            'amount' => Money::fromMinorUnits('EUR', 2500)->toArray(),
            'description' => __('Order :order_number', ['order_number' => $order->order_number]),
            'method' => 'ideal',
            'status' => MolliePaymentStatus::Paid->value,
            'profileId' => 'pfl_test123',
            'createdAt' => now()->toIso8601String(),
            'metadata' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ],
        ], Payment::class),
    ]);

    $provider = new MollieProvider;
    $result = $provider->handleWebhook('id=tr_Test123');

    expect($result)->not->toBeNull()
        ->and($result->id)->toBe($order->id)
        ->and($result->was_already_paid)->toBeFalse();

    $order->refresh();
    expect($order->payment_status)->toBe(PaymentStatus::Paid)
        ->and($order->status)->toBe(OrderStatus::Confirmed)
        ->and($order->payment_method)->toBe('ideal')
        ->and($order->payment_provider_id)->toBe('tr_Test123');
});

test('it handles webhook with raw payment id and updates order', function () {
    $order = Order::factory()->create([
        'payment_status' => PaymentStatus::Unpaid,
        'status' => OrderStatus::Pending,
    ]);

    Mollie::fake([
        GetPaymentRequest::class => MockResponse::ok([
            'id' => 'tr_Test123',
            'mode' => 'test',
            'amount' => Money::fromMinorUnits('EUR', 2500)->toArray(),
            'description' => __('Order :order_number', ['order_number' => $order->order_number]),
            'method' => 'creditcard',
            'status' => MolliePaymentStatus::Paid->value,
            'profileId' => 'pfl_test123',
            'createdAt' => now()->toIso8601String(),
            'metadata' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ],
        ], Payment::class),
    ]);

    $provider = new MollieProvider;
    $result = $provider->handleWebhook('tr_Test123');

    expect($result)->not->toBeNull()
        ->and($result->id)->toBe($order->id);

    $order->refresh();
    expect($order->payment_status)->toBe(PaymentStatus::Paid)
        ->and($order->payment_method)->toBe('creditcard');
});

test('it updates order to unpaid when webhook payment is canceled or failed', function () {
    $order = Order::factory()->create([
        'payment_status' => PaymentStatus::Unpaid,
        'status' => OrderStatus::Pending,
    ]);

    Mollie::fake([
        GetPaymentRequest::class => MockResponse::ok([
            'id' => 'tr_Test123',
            'mode' => 'test',
            'amount' => Money::fromMinorUnits('EUR', 2500)->toArray(),
            'description' => __('Order :order_number', ['order_number' => $order->order_number]),
            'status' => MolliePaymentStatus::Canceled->value,
            'profileId' => 'pfl_test123',
            'createdAt' => now()->toIso8601String(),
            'metadata' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ],
        ], Payment::class),
    ]);

    $provider = new MollieProvider;
    $result = $provider->handleWebhook('id=tr_Test123');

    expect($result)->not->toBeNull()
        ->and($result->id)->toBe($order->id);

    $order->refresh();
    expect($order->payment_status)->toBe(PaymentStatus::Unpaid);
});

test('it returns null when webhook payload has no payment id', function () {
    $provider = new MollieProvider;
    $result = $provider->handleWebhook('');

    expect($result)->toBeNull();
});

test('it returns null when payment metadata is missing order id', function () {
    Mollie::fake([
        GetPaymentRequest::class => MockResponse::ok([
            'id' => 'tr_Test123',
            'mode' => 'test',
            'amount' => Money::fromMinorUnits('EUR', 2500)->toArray(),
            'description' => 'Test',
            'status' => MolliePaymentStatus::Paid->value,
            'profileId' => 'pfl_test123',
            'createdAt' => now()->toIso8601String(),
            'metadata' => [],
        ], Payment::class),
    ]);

    $provider = new MollieProvider;
    $result = $provider->handleWebhook('id=tr_Test123');

    expect($result)->toBeNull();
});

test('it returns null when order is not found', function () {
    Mollie::fake([
        GetPaymentRequest::class => MockResponse::ok([
            'id' => 'tr_Test123',
            'mode' => 'test',
            'amount' => Money::fromMinorUnits('EUR', 2500)->toArray(),
            'description' => 'Test',
            'status' => MolliePaymentStatus::Paid->value,
            'profileId' => 'pfl_test123',
            'createdAt' => now()->toIso8601String(),
            'metadata' => [
                'order_id' => 99999,
                'order_number' => 'NONEXISTENT',
            ],
        ], Payment::class),
    ]);

    $provider = new MollieProvider;
    $result = $provider->handleWebhook('id=tr_Test123');

    expect($result)->toBeNull();
});

test('it returns false when signature is empty (classic webhooks have no signature)', function () {
    $provider = new MollieProvider;
    $result = $provider->verifySignature('id=tr_Test123');

    expect($result)->toBeFalse();
});

test('it verifies a valid signature using hmac', function () {
    $payload = json_encode(['test' => 'data']);
    $secret = 'test_secret_key';
    $signature = hash_hmac('sha256', $payload, $secret);

    config(['mollie.key' => $secret]);

    $provider = new MollieProvider;
    $result = $provider->verifySignature($payload, $signature);

    expect($result)->toBeTrue();
});

test('it returns false when signature is invalid', function () {
    config(['mollie.key' => 'test_secret_key']);

    $provider = new MollieProvider;
    $result = $provider->verifySignature('payload', 'invalid-signature');

    expect($result)->toBeFalse();
});

test('it gets the checkout url for a payment', function () {
    Mollie::fake([
        GetPaymentRequest::class => MockResponse::ok([
            'id' => 'tr_Test123',
            'mode' => 'test',
            'amount' => Money::fromMinorUnits('EUR', 2500)->toArray(),
            'description' => 'Test payment',
            'status' => 'open',
            'profileId' => 'pfl_test123',
            'createdAt' => now()->toIso8601String(),
            '_links' => json_decode(json_encode([
                'checkout' => [
                    'href' => 'https://mollie.com/checkout/test123',
                ],
            ])),
        ], Payment::class),
    ]);

    $provider = new MollieProvider;
    $url = $provider->getCheckoutUrl('tr_Test123');

    expect($url)->toBe('https://mollie.com/checkout/test123');

    Mollie::assertSent(GetPaymentRequest::class);
});

test('it returns null and reports exception when Mollie API throws during webhook processing', function () {
    Mollie::fake([
        GetPaymentRequest::class => MockResponse::notFound('Payment not found'),
    ]);

    $provider = new MollieProvider;
    $result = $provider->handleWebhook('id=tr_Test123');

    expect($result)->toBeNull();
});

test('it rejects webhook with invalid signature', function () {
    config(['mollie.key' => 'test_secret_key']);

    $provider = new MollieProvider;

    expect(fn () => $provider->handleWebhook('id=tr_Test123', 'invalid-signature'))
        ->toThrow(InvalidWebhookSignatureException::class);
});

test('it accepts webhook with valid signature', function () {
    $order = Order::factory()->create([
        'payment_status' => PaymentStatus::Unpaid,
        'status' => OrderStatus::Pending,
    ]);

    $payload = json_encode(['id' => 'tr_Test123']);
    $secret = 'test_secret_key';
    $signature = hash_hmac('sha256', $payload, $secret);

    config(['mollie.key' => $secret]);

    Mollie::fake([
        GetPaymentRequest::class => MockResponse::ok([
            'id' => 'tr_Test123',
            'mode' => 'test',
            'amount' => Money::fromMinorUnits('EUR', 2500)->toArray(),
            'description' => __('Order :order_number', ['order_number' => $order->order_number]),
            'method' => 'ideal',
            'status' => MolliePaymentStatus::Paid->value,
            'profileId' => 'pfl_test123',
            'createdAt' => now()->toIso8601String(),
            'metadata' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ],
        ], Payment::class),
    ]);

    $provider = new MollieProvider;
    $result = $provider->handleWebhook($payload, $signature);

    expect($result)->not->toBeNull()
        ->and($result->id)->toBe($order->id);
});

test('it skips signature verification when signature is empty (classic webhooks)', function () {
    $order = Order::factory()->create([
        'payment_status' => PaymentStatus::Unpaid,
        'status' => OrderStatus::Pending,
    ]);

    Mollie::fake([
        GetPaymentRequest::class => MockResponse::ok([
            'id' => 'tr_Test123',
            'mode' => 'test',
            'amount' => Money::fromMinorUnits('EUR', 2500)->toArray(),
            'description' => __('Order :order_number', ['order_number' => $order->order_number]),
            'method' => 'ideal',
            'status' => MolliePaymentStatus::Paid->value,
            'profileId' => 'pfl_test123',
            'createdAt' => now()->toIso8601String(),
            'metadata' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ],
        ], Payment::class),
    ]);

    $provider = new MollieProvider;
    $result = $provider->handleWebhook('id=tr_Test123');

    expect($result)->not->toBeNull()
        ->and($result->id)->toBe($order->id);
});
