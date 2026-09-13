<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\OrderPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use UnexpectedValueException;

class StripeWebhookController extends Controller
{
    public function __construct(private OrderPaymentService $payments)
    {
    }

    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret = config('services.stripe.webhook_secret');

        if (! is_string($secret) || $secret === '' || $secret === 'your-stripe-webhook-secret') {
            Log::error('Stripe webhook secret is not configured');

            return response()->json(['message' => 'Webhook not configured'], 500);
        }

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (UnexpectedValueException $e) {
            Log::warning('Stripe webhook invalid payload', ['error' => $e->getMessage()]);

            return response()->json(['message' => 'Invalid payload'], 400);
        } catch (SignatureVerificationException $e) {
            Log::warning('Stripe webhook invalid signature', ['error' => $e->getMessage()]);

            return response()->json(['message' => 'Invalid signature'], 400);
        }

        Log::info('Stripe webhook received', [
            'type' => $event->type,
            'id' => $event->id,
        ]);

        switch ($event->type) {
            case 'checkout.session.completed':
                $this->payments->markPaidFromCheckoutSession($event->data->object);
                break;

            case 'checkout.session.expired':
            case 'checkout.session.async_payment_failed':
                $this->payments->markFailedFromCheckoutSession($event->data->object);
                break;
        }

        return response()->json(['received' => true]);
    }
}
