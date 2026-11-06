<?php

namespace App\Http\Controllers\Payment;

use App\Enums\WebhookResult;
use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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
    public function handleMollie(Request $request): JsonResponse|Response
    {
        $payload = (string) ($request->input('id') ?? $request->getContent());
        $signature = (string) $request->header('X-Mollie-Signature', '');

        $result = $this->paymentService->handleWebhook($payload, $signature);

        return match ($result) {
            WebhookResult::Success => response()->json(['status' => 'processed']), // status 200
            WebhookResult::BadPayload => response()->json(['error' => 'Invalid payload'], 400),
            WebhookResult::NotFound => response()->json(['error' => 'Payment not found'], 404),
            WebhookResult::Error => response()->json(['error' => 'Internal error'], 500),
        };
    }
}
