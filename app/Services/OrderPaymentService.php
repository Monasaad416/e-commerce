<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class OrderPaymentService
{
    /**
     * Mark order paid from a Stripe Checkout Session object (webhook or API retrieve).
     * Idempotent. Returns the order, or null if order cannot be resolved.
     */
    public function markPaidFromCheckoutSession(object $session): ?Order
    {
        $orderId = $this->extractOrderId($session);
        $order = $orderId ? Order::query()->find($orderId) : null;

        if (! $order) {
            Log::warning('Stripe session: order not found', [
                'session_id' => $session->id ?? null,
                'extracted_order_id' => $orderId,
                'client_reference_id' => $session->client_reference_id ?? null,
                'metadata_order_id' => data_get($session, 'metadata.order_id'),
            ]);

            return null;
        }

        if ($order->payment_status === 'paid') {
            return $order;
        }

        $stripePaymentStatus = (string) ($session->payment_status ?? '');

        if ($stripePaymentStatus === 'unpaid') {
            Log::info('Stripe session still unpaid', [
                'order_id' => $order->id,
                'session_id' => $session->id ?? null,
            ]);

            return $order;
        }

        $order->payment_status = 'paid';
        $order->status = 'processing';
        $order->save();

        $this->clearUserCart((int) $order->user_id);

        Log::info('Order marked paid', [
            'order_id' => $order->id,
            'session_id' => $session->id ?? null,
            'stripe_payment_status' => $stripePaymentStatus,
        ]);

        return $order->fresh();
    }

    public function markFailedFromCheckoutSession(object $session): ?Order
    {
        $orderId = $this->extractOrderId($session);
        $order = $orderId ? Order::query()->find($orderId) : null;

        if (! $order || $order->payment_status === 'paid') {
            return $order;
        }

        $order->payment_status = 'failed';
        $order->save();

        return $order;
    }

    public function extractOrderId(object $session): ?int
    {
        $raw = data_get($session, 'metadata.order_id')
            ?? data_get($session, 'client_reference_id')
            ?? null;

        if ($raw === null || $raw === '') {
            return null;
        }

        return (int) $raw;
    }

    public function clearUserCart(int $userId): void
    {
        $cart = Cart::query()->where('user_id', $userId)->first();

        if (! $cart) {
            return;
        }

        $cart->cartItems()->delete();
        $cart->delete();
    }
}
