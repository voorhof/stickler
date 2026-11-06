<?php

namespace App\Contracts;

use App\Exceptions\InvalidWebhookSignatureException;
use App\Models\Order;
use Throwable;

interface PaymentProvider
{
    /**
     * Create a payment for the given order.
     *
     * @return array{payment_id: string, checkout_url: string}
     *
     * @throws Throwable
     */
    public function createPayment(Order $order): array;

    /**
     * Handle a webhook from the payment provider and update the associated order.
     *
     * Returns the updated Order on success, or null if the webhook could not be processed.
     *
     * @throws InvalidWebhookSignatureException
     */
    public function handleWebhook(string $payload, string $signature = ''): ?Order;

    /**
     * Verify the webhook signature (if supported by provider).
     */
    public function verifySignature(string $payload, string $signature = ''): bool;

    /**
     * Get the checkout URL for a payment.
     *
     * @throws Throwable
     */
    public function getCheckoutUrl(string $paymentId): string;
}
