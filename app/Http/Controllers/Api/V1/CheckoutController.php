<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class CheckoutController extends Controller
{
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
                . '/checkout/success?session_id={CHECKOUT_SESSION_ID}&order_id=' . $order->id,
            'cancel_url' => rtrim((string) config('services.frontend_url', config('app.url')), '/')
                .'/cart',
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
}
