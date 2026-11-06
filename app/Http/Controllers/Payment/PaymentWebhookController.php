<?php

namespace App\Http\Controllers\Payment;

use App\Enums\WebhookResult;
use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentWebhookController extends Controller
{
    /**
     * Create a new PaymentWebhookController instance.
     */
    public function __construct(
        protected PaymentService $paymentService,
    ) {}

    /**
     * Handle the Mollie webhook.
     */
    public function handleMollie(Request $request): JsonResponse
    {
        $payload = (string) ($request->input('id') ?? $request->getContent());
        $signature = (string) $request->header('X-Mollie-Signature', '');

        $result = $this->paymentService->handleWebhook($payload, $signature);

        return response()->json(
            $result === WebhookResult::Success
                ? ['status' => 'processed']
                : ['error' => $this->errorMessage($result)],
            $result->httpStatusCode(),
        );
    }

    /**
     * Get the error message for a webhook result.
     */
    protected function errorMessage(WebhookResult $result): string
    {
        return match ($result) {
            WebhookResult::BadPayload, WebhookResult::SignatureInvalid => 'Invalid payload',
            WebhookResult::NotFound => 'Payment not found',
            WebhookResult::Error => 'Internal error',
            default => 'Unknown error',
        };
    }
}
