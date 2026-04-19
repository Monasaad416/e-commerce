<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderItemResource;
use App\Http\Resources\OrderResource;
use App\Models\Cart;
use App\Models\Order;
use App\Models\CartItem;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderItemResource;
use App\Http\Resources\OrderResource;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => OrderResource::collection($orders),
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $cart = Cart::with('cartItems')->where('user_id', $user->id)->first();

        if (!$cart || $cart->cartItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Cart is empty'
            ], 400);
        }

        DB::beginTransaction();

        try {
            // Create the order
            $order = Order::create([
                'user_id' => $user->id,
                'address' => $user->address,
                'total' => $cart->total,
                'subtotal' => $cart->subtotal,
                'discount' => $cart->discount,
                'shipping_fee' => 0,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'shipping_status' => 'not_shipped',
                'address' => $request->shipping['address'] ?? 'kkk',
                'notes' => $request->notes ?? null,
            ]);

            // Create order items for all cart items
            $orderItems = [];
            foreach ($cart->cartItems as $item) {
                $basePrice = $item->product_variant_id ? $item->productVariant->selling_price : $item->product->selling_price;
                $discountPrice = $item->product_variant_id ? $item->productVariant->discount_price : $item->product->discount_price;

                // Use discount_price if available, otherwise use base price
                $finalPrice = $discountPrice ?: $basePrice;

                $orderItems[] = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'name' => $item->product->name,
                    'qty' => $item->qty,
                    'price' => $basePrice,
                    'discount_price' => $discountPrice ?: 0, // Ensure it's not null
                    'tax' => 0.15 * ($finalPrice * $item->qty),
                    'subtotal' => $finalPrice * $item->qty,
                    'total' => ($finalPrice * $item->qty) * 1.15,
                ]);
            }

            // Optionally, clear the cart after order
            $cart->cartItems()->delete();
            $cart->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => __('front/order_resource.order_created_successfully'),
                'data' => [
                    'order' => new OrderResource($order),
                    'items' => OrderItemResource::collection($orderItems),
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Order creation error', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => __('front/cart_resource.cart_create_failed'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
