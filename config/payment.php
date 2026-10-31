<?php

return [
    /**
     * The default payment provider used by the application.
     */
    'default' => env('PAYMENT_PROVIDER', 'mollie'),

    /**
     * The webhook URL to receive payment updates from providers.
     * If null, falls back to route('payment.webhook.<provider>') in production.
     */
    'webhook_url' => env('PAYMENT_WEBHOOK_URL'),

    /**
     * Available payment providers and their implementations.
     */
    'providers' => [
        'mollie' => App\Payments\MollieProvider::class,
    ],
];
