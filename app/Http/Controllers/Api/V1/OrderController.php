<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Cart;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->with(['items.product'])
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
        $validated = $request->validate([
            'shipping.address' => 'required|string|max:1000',
            'notes' => 'nullable|string|max:2000',
        ]);

        $cart = Cart::with(['cartItems.product', 'cartItems.productVariant.product'])
            ->where('user_id', $user->id)
            ->first();

        if (!$cart || $cart->cartItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => __('front.order_cart_is_empty'),
                'error_code' => 'ORDER_CART_EMPTY',
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Create the order
            $order = Order::create([
                'user_id' => $user->id,
                'total' => 0,
                'subtotal' => 0,
                'discount' => 0,
                'shipping_fee' => 0,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'shipping_status' => 'not_shipped',
                'address' => $validated['shipping']['address'],
                'notes' => $validated['notes'] ?? null,
            ]);

            // Create order items for all cart items
            $orderSubtotal = 0.0;
            $orderTax = 0.0;
            foreach ($cart->cartItems as $item) {
                $requestedQty = (float) $item->qty;

                if ($item->product_variant_id) {
                    $variant = ProductVariant::query()
                        ->whereKey($item->product_variant_id)
                        ->lockForUpdate()
                        ->first();

                    if (!$variant) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => __('front.selected_variant_is_no_longer_available'),
                            'error_code' => 'ORDER_VARIANT_NOT_AVAILABLE',
                        ], 422);
                    }

                    if ((float) $variant->qty < $requestedQty) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => __('front.insufficient_stock_for_selected_variant'),
                            'error_code' => 'ORDER_VARIANT_STOCK_NOT_ENOUGH',
                            'data' => [
                                'product_variant_id' => $variant->id,
                                'available_qty' => (float) $variant->qty,
                                'requested_qty' => $requestedQty,
                            ],
                        ], 422);
                    }

                    $variant->decrement('qty', $requestedQty);
                    $basePrice = $variant->selling_price;
                    $discountPrice = $variant->discount_price;
                    $itemName = $variant->product?->name ?? $item->product?->name ?? [];
                } else {
                    $product = Product::query()
                        ->whereKey($item->product_id)
                        ->lockForUpdate()
                        ->first();

                    if (!$product) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => __('front.selected_product_is_no_longer_available'),
                            'error_code' => 'ORDER_PRODUCT_NOT_AVAILABLE',
                        ], 422);
                    }

                    if ((float) $product->qty < $requestedQty) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => __('front.insufficient_stock_for_selected_product'),
                            'error_code' => 'ORDER_PRODUCT_STOCK_NOT_ENOUGH',
                            'data' => [
                                'product_id' => $product->id,
                                'available_qty' => (float) $product->qty,
                                'requested_qty' => $requestedQty,
                            ],
                        ], 422);
                    }

                    $product->decrement('qty', $requestedQty);
                    $basePrice = $product->selling_price;
                    $discountPrice = $product->discount_price;
                    $itemName = $product->name ?? [];
                }

                // Use discount_price if available, otherwise use base price
                $finalPrice = $discountPrice ?: $basePrice;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'name' => $itemName,
                    'qty' => $requestedQty,
                    'price' => $basePrice,
                    'discount_price' => $discountPrice ?: 0, // Ensure it's not null
                    'tax' => 0.15 * ($finalPrice * $requestedQty),
                    'subtotal' => $finalPrice * $requestedQty,
                    'total' => ($finalPrice * $requestedQty) * 1.15,
                ]);

                $lineSubtotal = $finalPrice * $requestedQty;
                $lineTax = 0.15 * $lineSubtotal;
                $orderSubtotal += $lineSubtotal;
                $orderTax += $lineTax;
            }

            $order->update([
                'subtotal' => $orderSubtotal,
                'discount' => 0,
                'total' => $orderSubtotal + $orderTax,
            ]);

            // Keep cart until payment succeeds (Stripe webhook).
            // If user cancels checkout they can return to /cart with items intact.

            DB::commit();

            $order->load(['items.product']);

            return response()->json([
                'success' => true,
                'message' => __('front.order_created_successfully'),
                'data' => new OrderResource($order),
            ]);

        } catch (Throwable $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            Log::error('Order creation error', [
                'user_id' => $user?->id,
                'cart_id' => $cart?->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => __('front.order_create_failed'),
                'error_code' => 'ORDER_CREATE_FAILED',
            ], 500);
        }
    }

    public function show(int $orderId, Request $request)
    {
        $order = Order::where('id', $orderId)
            ->where('user_id', $request->user()->id)
            ->with(['items.product'])
            ->first();

        if (! $order) {
            return response()->json([
                'success' => false,
                'message' => __('front.order_not_found_or_not_authorized'),
                'error_code' => 'ORDER_NOT_FOUND',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new OrderResource($order),
        ]);
    }
}
