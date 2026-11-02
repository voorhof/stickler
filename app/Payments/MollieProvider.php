<?php

namespace App\Payments;

use App\Contracts\PaymentProvider;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use Mollie\Api\Exceptions\InvalidSignatureException;
use Mollie\Api\Exceptions\MollieException;
use Mollie\Api\Http\Data\Money;
use Mollie\Api\Http\Requests\CreatePaymentRequest;
use Mollie\Api\Http\Requests\GetPaymentRequest;
use Mollie\Api\Types\PaymentStatus as MolliePaymentStatus;
use Mollie\Api\Webhooks\SignatureValidator;
use Mollie\Laravel\Facades\Mollie;

class MollieProvider implements PaymentProvider
{
    /**
     * Create a payment for the given order.
     *
     * @return array{payment_id: string, checkout_url: string}
     *
     * @throws MollieException
     */
    public function createPayment(Order $order): array
    {
        // Use explicitly configured webhook URL, or default to production route
        $webhookUrl = config('payment.webhook_url')
            ?? (app()->isProduction() ? route('payment.webhook.mollie') : null);

        $payment = Mollie::send(new CreatePaymentRequest(
            description: __('Order :order_number', ['order_number' => $order->order_number]),
            amount: Money::fromMinorUnits('EUR', (int) $order->getAttributes()['total']),
            redirectUrl: route('order.confirmed', ['order' => $order]),
            webhookUrl: $webhookUrl,
            metadata: [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ],
        ));

        return [
            'payment_id' => $payment->id,
            'checkout_url' => $payment->getCheckoutUrl(),
        ];
    }

    /**
     * Handle a webhook from Mollie.
     *
     * Extracts the payment ID from the payload (supports form-data, JSON, or raw ID),
     * fetches the verified payment status from Mollie API, and updates the order.
     */
    public function handleWebhook(string $payload, string $signature = ''): ?Order
    {
        $paymentId = null;

        if (str_starts_with($payload, 'tr_') || str_starts_with($payload, 'ord_')) {
            $paymentId = mb_trim($payload);
        } elseif (str_starts_with($payload, '{')) {
            $data = json_decode($payload, true);
            $paymentId = $data['id'] ?? null;
        } elseif (str_contains($payload, '=')) {
            parse_str($payload, $parsed);
            $paymentId = $parsed['id'] ?? null;
        }

        if (! $paymentId) {
            return null;
        }

        try {
            $payment = Mollie::send(new GetPaymentRequest($paymentId));
        } catch (MollieException $e) {
            report($e);

            return null;
        }

        $orderId = data_get($payment->metadata, 'order_id');

        if (! $orderId) {
            return null;
        }

        $order = Order::find($orderId);

        if (! $order) {
            return null;
        }

        $wasAlreadyPaid = ($order->payment_status === PaymentStatus::Paid);

        $isPaid = $payment->isPaid()
            || $payment->status === MolliePaymentStatus::Paid
            || $payment->status === MolliePaymentStatus::Paid->value
            || $payment->status === 'paid';

        if ($isPaid) {
            $order->update([
                'payment_status' => PaymentStatus::Paid->value,
                'status' => OrderStatus::Confirmed->value,
                'payment_provider_id' => $payment->id,
                'payment_method' => is_string($payment->method) ? $payment->method : ($payment->method?->value ?? $order->payment_method),
            ]);
        } elseif ($payment->isCanceled() || $payment->isFailed() || $payment->isExpired() || $payment->status === 'canceled' || $payment->status === 'failed' || $payment->status === 'expired') {
            $order->update([
                'payment_status' => PaymentStatus::Unpaid->value,
            ]);
        }

        $order->was_already_paid = $wasAlreadyPaid;

        return $order;
    }

    /**
     * Verify the webhook signature.
     */
    public function verifySignature(string $payload, string $signature = ''): bool
    {
        if (empty($signature)) {
            return true;
        }

        try {
            return SignatureValidator::validate($payload, config('mollie.key'), $signature);
        } catch (InvalidSignatureException) {
            return false;
        }
    }

    /**
     * Get the checkout URL for a payment.
     *
     * @throws MollieException
     */
    public function getCheckoutUrl(string $paymentId): string
    {
        $payment = Mollie::send(new GetPaymentRequest($paymentId));

        return $payment->getCheckoutUrl();
    }
}
