<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Models\User;

beforeEach(function () {
    User::factory()->create();
});

describe('payment webhook', function () {
    test('it handles standard mollie webhook form payload without csrf token and returns 204', function () {
        $paymentService = mock(App\Services\PaymentService::class)
            ->shouldReceive('handleWebhook')
            ->once()
            ->with('tr_test123', '')
            ->andReturn(true)
            ->getMock();
        app()->instance(App\Services\PaymentService::class, $paymentService);

        // Standard Mollie webhook sends form data: id=tr_xxx
        $this->post(route('payment.webhook.mollie'), ['id' => 'tr_test123'])
            ->assertNoContent();
    });

    test('it passes signature header to payment service when provided', function () {
        $paymentService = mock(App\Services\PaymentService::class)
            ->shouldReceive('handleWebhook')
            ->once()
            ->with('tr_test123', 'test-signature-header')
            ->andReturn(true)
            ->getMock();
        app()->instance(App\Services\PaymentService::class, $paymentService);

        $this->post(
            route('payment.webhook.mollie'),
            ['id' => 'tr_test123'],
            ['X-Mollie-Signature' => 'test-signature-header'],
        )->assertNoContent();
    });

    test('it handles json webhook payload', function () {
        $paymentService = mock(App\Services\PaymentService::class)
            ->shouldReceive('handleWebhook')
            ->once()
            ->with('tr_test123', '')
            ->andReturn(true)
            ->getMock();
        app()->instance(App\Services\PaymentService::class, $paymentService);

        $this->postJson(route('payment.webhook.mollie'), ['id' => 'tr_test123'])
            ->assertNoContent();
    });
});
