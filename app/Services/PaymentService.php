<?php

namespace App\Services;

use App\Contracts\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Mail\PaymentConfirmation;
use App\Models\Order;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Facades\Mail;
use Throwable;

class PaymentService
{
    /**
     * Create a new PaymentService instance.
     */
    public function __construct(
        protected Container $container,
    ) {}

    /**
     * Resolve the configured payment provider.
     *
     * @throws BindingResolutionException
     */
    protected function provider(): PaymentProvider
    {
        $providerClass = config("payment.providers.{$this->providerName()}");

        if (! is_string($providerClass) || (! class_exists($providerClass) && ! interface_exists($providerClass))) {
            throw new BindingResolutionException("Payment provider class [$providerClass] is not configured or does not exist.");
        }

        return $this->container->make($providerClass);
    }

    /**
     * Get the configured provider name.
     */
    protected function providerName(): string
    {
        return config('payment.default', 'mollie');
    }

    /**
     * Initiate payment for the given order.
     *
     * Creates a payment via the provider, saves provider info to the order,
     * and returns the checkout URL.
     *
     * @return array{payment_id: string, checkout_url: string}
     *
     * @throws BindingResolutionException
     * @throws Throwable
     */
    public function initiatePayment(Order $order): array
    {
        $result = $this->provider()->createPayment($order);

        $order->update([
            'payment_provider' => $this->providerName(),
            'payment_provider_id' => $result['payment_id'],
        ]);

        return $result;
    }

    /**
     * Handle an incoming webhook from the payment provider.
     *
     * Verifies the signature, processes the webhook, and sends a confirmation email if paid.
     *
     * Returns true if payment was confirmed.
     */
    public function handleWebhook(string $payload, string $signature = ''): bool
    {
        try {
            $provider = $this->provider();

            if (! $provider->verifySignature($payload, $signature)) {
                return false;
            }

            $order = $provider->handleWebhook($payload, $signature);

            if (! $order) {
                return false;
            }

            // Webhook Idempotency: only send email on the first confirmation, not on webhook retries
            if ($order->payment_status === PaymentStatus::Paid && ! ($order->was_already_paid ?? false)) {
                $this->sendConfirmationEmail($order);
            }

            return $order->payment_status === PaymentStatus::Paid;
        } catch (Throwable $e) {
            report($e);

            return false;
        }
    }

    /**
     * Send a payment confirmation email to the customer.
     */
    protected function sendConfirmationEmail(Order $order): void
    {
        $customerEmail = $order->shipping_address['email'] ?? null;

        if (! $customerEmail) {
            return;
        }

        $order->loadMissing('orderItems');

        Mail::to($customerEmail)->send(new PaymentConfirmation($order));
    }
}
