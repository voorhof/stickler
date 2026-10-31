<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
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
    public function handleMollie(Request $request): Response
    {
        $payload = (string) ($request->input('id') ?? $request->getContent());
        $signature = (string) $request->header('X-Mollie-Signature', '');

        $this->paymentService->handleWebhook($payload, $signature);

        return response()->noContent();
    }
}
