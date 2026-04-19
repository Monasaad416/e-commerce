<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CartResource;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{

    public function index(Request $request)
    {
        $cart = Cart::findOrFail($request->cart_id)->load('cartItems.product');

        return response()->json([
            'success' => true,
            'message' => __('front/cart_resource.cart_retrieved_successfully'),
            'data' => [
                'cart' => CartResource::collection($cart),
            ]
        ]);
    }
    public function store(Request $request)
    {

        try {

            \Log::info('cart info', $request->all());
            if (!$request->user()) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            // DB::beginTransaction();
            $cart = Cart::where('user_id', $request->user()->id)->latest()->first();
            if (!$cart) {
                $cart = Cart::create([
                    'user_id' => $request->user()->id,
                    'subtotal' => 0,
                    'tax' => 0,
                    'discount' => 0,
                    'total' => 0,
                ]);

                \Log::info('cart created', ['cart' => $cart]);
            } else {
                \Log::info('cart found', ['cart' => $cart]);
            }

            // Determine product type and set appropriate IDs
            $productType = $request->type; // or get from product
            $productId = null;
            $variantId = null;

            if ($productType === 'simple') {
                $productId = $request->product_id;
                $variantId = null;
            } else {
                $productId = $request->product_id; // still send product_id for reference
                $variantId = $request->product_variant_id;
            }


            $cartItem = CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $productId,
                'product_variant_id' => $variantId,
                'qty' => $request->qty ?? 1,
                'price' => $request->price,
                'subtotal' => $request->price,
                'tax' => $request->price * 0.15,
                'discount_price' => $request->discount_price,
                'total' => $request->discount_price > 0 ? $request->discount_price * 0.15 : $request->price * 0.15,
            ]);

            \Log::info('cart item created', ['cart_item' => $cartItem]);
            // DB::commit();
            return response()->json([
                'success' => true,
                'message' => __('front/cart_resource.cart_created_successfully'),
                'data' => new CartResource($cart),
            ]);
        } catch (\Exception $e) {
            \Log::error('cart create error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => __('front/cart_resource.cart_create_failed'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, Cart $cart)
    {
        $cart->update([
            'subtotal' => $request->subtotal,
            'tax' => $request->tax,
            'discount' => $request->discount,
            'total' => $request->total,
        ]);
        return response()->json([
            'success' => true,
            'message' => __('front/cart_resource.cart_updated_successfully'),
            'data' => new CartResource($cart),
        ]);
    }

    public function destroy(Request $request)
    {
        try {
            $cart = Cart::where('user_id', $request->user()->id)->first();
            \Log::info('cart found', ['cart' => $cart]);
            if (!$cart) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cart already empty',
                ]);
            }

            $cart->cartItems()->delete();


            $cart->delete();

            return response()->json([
                'success' => true,
                'message' => 'Cart deleted successfully',
            ]);

        } catch (\Exception $e) {
            \Log::error('Cart delete error', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Cart delete failed',
            ], 500);
        }
    }

    public function removeItem(Request $request)
    {
        $item = CartItem::findOrFail($request->cart_item_id);

        if ($item->cart->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $item->delete();

        return response()->json([
            'message' => 'Item removed successfully'
        ]);
    }

}
