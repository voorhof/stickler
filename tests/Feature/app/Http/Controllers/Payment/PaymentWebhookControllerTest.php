<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Enums\WebhookResult;
use App\Models\User;

beforeEach(function () {
    User::factory()->create();
});

describe('payment webhook', function () {
    test('it handles standard mollie webhook form payload without csrf token and returns 200', function () {
        $paymentService = mock(App\Services\PaymentService::class)
            ->shouldReceive('handleWebhook')
            ->once()
            ->with('tr_test123', '')
            ->andReturn(WebhookResult::Success)
            ->getMock();
        app()->instance(App\Services\PaymentService::class, $paymentService);

        // Standard Mollie webhook sends form data: id=tr_xxx
        $this->post(route('payment.webhook.mollie'), ['id' => 'tr_test123'])
            ->assertOk()
            ->assertJson(['status' => 'processed']);
    });

    test('it passes signature header to payment service when provided', function () {
        $paymentService = mock(App\Services\PaymentService::class)
            ->shouldReceive('handleWebhook')
            ->once()
            ->with('tr_test123', 'test-signature-header')
            ->andReturn(WebhookResult::Success)
            ->getMock();
        app()->instance(App\Services\PaymentService::class, $paymentService);

        $this->post(
            route('payment.webhook.mollie'),
            ['id' => 'tr_test123'],
            ['X-Mollie-Signature' => 'test-signature-header'],
        )->assertOk()
            ->assertJson(['status' => 'processed']);
    });

    test('it handles json webhook payload', function () {
        $paymentService = mock(App\Services\PaymentService::class)
            ->shouldReceive('handleWebhook')
            ->once()
            ->with('tr_test123', '')
            ->andReturn(WebhookResult::Success)
            ->getMock();
        app()->instance(App\Services\PaymentService::class, $paymentService);

        $this->postJson(route('payment.webhook.mollie'), ['id' => 'tr_test123'])
            ->assertOk()
            ->assertJson(['status' => 'processed']);
    });

    test('it returns 400 when payload is empty', function () {
        $paymentService = mock(App\Services\PaymentService::class)
            ->shouldReceive('handleWebhook')
            ->once()
            ->with('', '')
            ->andReturn(WebhookResult::BadPayload)
            ->getMock();
        app()->instance(App\Services\PaymentService::class, $paymentService);

        $this->post(route('payment.webhook.mollie'))
            ->assertBadRequest()
            ->assertJson(['error' => 'Invalid payload']);
    });

    test('it returns 404 when payment is not found', function () {
        $paymentService = mock(App\Services\PaymentService::class)
            ->shouldReceive('handleWebhook')
            ->once()
            ->with('tr_unknown', '')
            ->andReturn(WebhookResult::NotFound)
            ->getMock();
        app()->instance(App\Services\PaymentService::class, $paymentService);

        $this->post(route('payment.webhook.mollie'), ['id' => 'tr_unknown'])
            ->assertNotFound()
            ->assertJson(['error' => 'Payment not found']);
    });

    test('it returns 400 when signature is invalid', function () {
        $paymentService = mock(App\Services\PaymentService::class)
            ->shouldReceive('handleWebhook')
            ->once()
            ->andReturn(WebhookResult::SignatureInvalid)
            ->getMock();
        app()->instance(App\Services\PaymentService::class, $paymentService);

        $this->post(route('payment.webhook.mollie'), ['id' => 'tr_test123'])
            ->assertBadRequest()
            ->assertJson(['error' => 'Invalid payload']);
    });

    test('it returns 500 on internal error', function () {
        $paymentService = mock(App\Services\PaymentService::class)
            ->shouldReceive('handleWebhook')
            ->once()
            ->andReturn(WebhookResult::Error)
            ->getMock();
        app()->instance(App\Services\PaymentService::class, $paymentService);

        $this->post(route('payment.webhook.mollie'), ['id' => 'tr_test123'])
            ->assertStatus(500)
            ->assertJson(['error' => 'Internal error']);
    });

    test('it rate limits after 30 requests per minute', function () {
        $paymentService = mock(App\Services\PaymentService::class)
            ->shouldReceive('handleWebhook')
            ->times(30)
            ->andReturn(WebhookResult::Success)
            ->getMock();
        app()->instance(App\Services\PaymentService::class, $paymentService);

        // First 30 requests should succeed
        for ($i = 0; $i < 30; $i++) {
            $this->post(route('payment.webhook.mollie'), ['id' => 'tr_test123'])
                ->assertOk();
        }

        // 31st request should be rate limited (429 Too Many Requests)
        $response = $this->post(route('payment.webhook.mollie'), ['id' => 'tr_test123']);
        $response->assertStatus(429);
    });
});
