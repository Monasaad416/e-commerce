<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderPaymentService;
use Illuminate\Http\Request;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Throwable;

class CheckoutController extends Controller
{
    public function __construct(private OrderPaymentService $payments)
    {
    }

    public function checkoutPayment(Request $request, string $locale, Order $order)
    {
        if ((int) $order->user_id !== (int) $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => __('front.unauthorized'),
            ], 403);
        }

        $user = $order->user;
        $order->load('items');

        if ($order->items->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => __('front.order_cart_is_empty'),
            ], 400);
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        $session = Session::create([
            'mode' => 'payment',
            'client_reference_id' => (string) $order->id,
            'customer_email' => $user->email,
            'line_items' => $order->items->map(function ($item) {
                $rawName = $item->name;
                if (is_array($rawName)) {
                    $name = $rawName[app()->getLocale()] ?? $rawName['en'] ?? reset($rawName);
                } else {
                    $name = $rawName ?: ('Order item #'.$item->id);
                }

                $unitAmount = $item->discount_price > 0
                    ? $item->discount_price
                    : $item->price;

                $qty = (int) ($item->qty ?? 1);

                return [
                    'price_data' => [
                        'currency' => 'egp',
                        'product_data' => [
                            'name' => (string) $name,
                        ],
                        'unit_amount' => (int) round(((float) $unitAmount) * 100),
                    ],
                    'quantity' => max(1, $qty),
                ];
            })->values()->all(),
            'success_url' => rtrim((string) config('services.frontend_url', config('app.url')), '/')
                . '/' . $locale . '/checkout/success?session_id={CHECKOUT_SESSION_ID}&order_id=' . $order->id,
            'cancel_url' => rtrim((string) config('services.frontend_url', config('app.url')), '/')
                . '/' . $locale . '/cart',
            'metadata' => [
                'user_id' => (string) $user->id,
                'order_id' => (string) $order->id,
            ],
        ]);

        return response()->json([
            'success' => true,
            'checkout_url' => $session->url,
        ]);
    }

    /**
     * Called from the frontend success page.
     * Verifies payment with Stripe API (does not trust the browser alone).
     * Works even when local webhooks are down.
     */
    public function confirm(Request $request)
    {
        $validated = $request->validate([
            'session_id' => 'required|string',
            'order_id' => 'nullable|integer|exists:orders,id',
        ]);

        $user = $request->user();

        try {
            Stripe::setApiKey(config('services.stripe.secret'));
            $session = Session::retrieve($validated['session_id']);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => __('front.payment_verification_failed'),
            ], 502);
        }

        $orderId = $this->payments->extractOrderId($session);
        if (! empty($validated['order_id']) && $orderId && (int) $validated['order_id'] !== $orderId) {
            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => __('front.payment_session_mismatch'),
            ], 422);
        }

        $order = $orderId
            ? Order::query()->whereKey($orderId)->where('user_id', $user->id)->first()
            : null;

        if (! $order) {
            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => __('front.order_not_found_or_not_authorized'),
            ], 404);
        }

        $stripeStatus = (string) ($session->payment_status ?? 'unpaid');

        if ($stripeStatus === 'paid' || $stripeStatus === 'no_payment_required') {
            $order = $this->payments->markPaidFromCheckoutSession($session) ?? $order->fresh();

            return response()->json([
                'success' => true,
                'status' => 'paid',
                'message' => __('front.payment_confirmed'),
                'data' => [
                    'order' => new OrderResource($order),
                    'stripe_payment_status' => $stripeStatus,
                ],
            ]);
        }

        if ($stripeStatus === 'unpaid') {
            return response()->json([
                'success' => false,
                'status' => 'pending',
                'message' => __('front.payment_pending'),
                'data' => [
                    'order' => new OrderResource($order),
                    'stripe_payment_status' => $stripeStatus,
                ],
            ], 202);
        }

        return response()->json([
            'success' => false,
            'status' => 'failed',
            'message' => __('front.payment_failed'),
            'data' => [
                'order' => new OrderResource($order),
                'stripe_payment_status' => $stripeStatus,
            ],
        ], 402);
    }

    /**
     * Poll order payment status (useful while waiting for webhook).
     */
    public function paymentStatus(Request $request, string $locale, Order $order)
    {
        if ((int) $order->user_id !== (int) $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => __('front.unauthorized'),
            ], 403);
        }

        return response()->json([
            'success' => true,
            'status' => $order->payment_status,
            'data' => [
                'order' => new OrderResource($order),
            ],
            'message' => match ($order->payment_status) {
                'paid' => __('front.payment_confirmed'),
                'failed' => __('front.payment_failed'),
                default => __('front.payment_pending'),
            },
        ]);
    }
}
