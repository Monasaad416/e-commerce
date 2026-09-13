<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Stripe;
use Stripe\Webhook;
use UnexpectedValueException;

class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret = config('services.stripe.webhook_secret');

        if (! $secret || $secret !== config('services.stripe.webhook_secret')) {
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

        Stripe::setApiKey(config('services.stripe.secret'));

        switch ($event->type) {
            case 'checkout.session.completed':
                $this->handleCheckoutSessionCompleted($event->data->object);
                break;

            case 'checkout.session.expired':
                $this->handleCheckoutSessionExpired($event->data->object);
                break;

            case 'checkout.session.async_payment_failed':
                $this->handleCheckoutSessionFailed($event->data->object);
                break;

            default:
                Log::info('Stripe webhook ignored', ['type' => $event->type]);
        }

        return response()->json(['received' => true]);
    }

    private function handleCheckoutSessionCompleted(object $session): void
    {
        $order = $this->findOrderFromSession($session);

        if (! $order) {
            Log::warning('Stripe checkout.session.completed: order not found', [
                'session_id' => $session->id ?? null,
                'client_reference_id' => $session->client_reference_id ?? null,
                'metadata' => (array) ($session->metadata ?? []),
            ]);

            return;
        }

        // Idempotent: already paid
        if ($order->payment_status === 'paid') {
            return;
        }

        if (($session->payment_status ?? null) === 'unpaid') {
            return;
        }

        $order->payment_status = 'paid';
        $order->status = 'processing';
        $order->save();

        $this->clearUserCart((int) $order->user_id);

        Log::info('Order marked paid via Stripe webhook', [
            'order_id' => $order->id,
            'session_id' => $session->id ?? null,
        ]);
    }

    private function clearUserCart(int $userId): void
    {
        $cart = Cart::query()->where('user_id', $userId)->first();

        if (! $cart) {
            return;
        }

        $cart->cartItems()->delete();
        $cart->delete();
    }

    private function handleCheckoutSessionExpired(object $session): void
    {
        $order = $this->findOrderFromSession($session);

        if (! $order || $order->payment_status === 'paid') {
            return;
        }

        $order->payment_status = 'failed';
        $order->save();
    }

    private function handleCheckoutSessionFailed(object $session): void
    {
        $order = $this->findOrderFromSession($session);

        if (! $order || $order->payment_status === 'paid') {
            return;
        }

        $order->payment_status = 'failed';
        $order->save();
    }

    private function findOrderFromSession(object $session): ?Order
    {
        $orderId = $session->metadata->order_id
            ?? $session->client_reference_id
            ?? null;

        if (! $orderId) {
            return null;
        }

        return Order::query()->find($orderId);
    }
}
