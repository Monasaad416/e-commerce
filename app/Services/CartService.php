<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Support\MediaUrl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;

class CartService
{
    public function getCart(Request $request): JsonResponse
    {
        $cart = Cart::resolveForRequest($request, false);

        if (! $cart) {
            return $this->cartResponse(
                true,
                __('front.cart_retrieved_successfully'),
                null,
                Cart::extractGuestToken($request)
            );
        }

        if (! $cart->belongsToRequest($request)) {
            return response()->json([
                'success' => false,
                'message' => __('front.unauthorized'),
            ], 403);
        }

        $cart->load($this->cartRelations());

        return $this->cartResponse(
            true,
            __('front.cart_retrieved_successfully'),
            $cart
        );
    }

    /**
     * Add/update a cart line.
     */
    public function addItem(array $validated, Request $request): JsonResponse
    {
        $cart = Cart::resolveForRequest($request, true);
        $requestedQty = (int) ($validated['qty'] ?? 1);
        $productType = $validated['type'];
        $productId = (int) $validated['product_id'];
        $variantId = null;
        $price = 0.0;
        $discountPrice = 0.0;

        if ($productType === 'variable') {
            if (empty($validated['product_variant_id'])) {
                return response()->json([
                    'success' => false,
                    'message' => __('front.variant_id_is_required_for_variable_products'),
                ], 422);
            }

            $variantId = (int) $validated['product_variant_id'];
            $variant = ProductVariant::query()
                ->whereKey($variantId)
                ->where('product_id', $productId)
                ->where('is_active', true)
                ->first();

            if (! $variant) {
                return response()->json([
                    'success' => false,
                    'message' => __('front.selected_variant_is_no_longer_available'),
                ], 422);
            }

            $existingQty = (int) ($cart->cartItems()
                ->where('product_id', $productId)
                ->where('product_variant_id', $variantId)
                ->value('qty') ?? 0);

            if ((float) $variant->qty < ($existingQty + $requestedQty)) {
                return response()->json([
                    'success' => false,
                    'message' => __('front.insufficient_stock_for_selected_variant'),
                    'data' => [
                        'available_qty' => (float) $variant->qty,
                        'requested_qty' => $existingQty + $requestedQty,
                    ],
                ], 422);
            }

            $price = (float) $variant->selling_price;
            $discountPrice = (float) ($variant->discount_price ?? 0);
        } else {
            $product = Product::query()
                ->whereKey($productId)
                ->where('is_active', true)
                ->first();

            if (! $product) {
                return response()->json([
                    'success' => false,
                    'message' => __('front.selected_product_is_no_longer_available'),
                ], 422);
            }

            $existingQty = (int) ($cart->cartItems()
                ->where('product_id', $productId)
                ->whereNull('product_variant_id')
                ->value('qty') ?? 0);

            if ((float) $product->qty < ($existingQty + $requestedQty)) {
                return response()->json([
                    'success' => false,
                    'message' => __('front.insufficient_stock_for_selected_product'),
                    'data' => [
                        'available_qty' => (float) $product->qty,
                        'requested_qty' => $existingQty + $requestedQty,
                    ],
                ], 422);
            }

            $price = (float) $product->selling_price;
            $discountPrice = (float) ($product->discount_price ?? 0);
        }

        $existingItem = $cart->cartItems()
            ->where('product_id', $productId)
            ->when(
                $variantId,
                fn ($q) => $q->where('product_variant_id', $variantId),
                fn ($q) => $q->whereNull('product_variant_id')
            )
            ->first();

        $qty = $existingItem
            ? ((int) $existingItem->qty + $requestedQty)
            : $requestedQty;

        $line = $this->lineTotals($price, $discountPrice, $qty);

        if ($existingItem) {
            $existingItem->update([
                'qty' => $qty,
                'price' => $price,
                'discount_price' => $discountPrice,
                'subtotal' => $line['subtotal'],
                'tax' => $line['tax'],
                'total' => $line['total'],
            ]);
        } else {
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $productId,
                'product_variant_id' => $variantId,
                'qty' => $qty,
                'price' => $price,
                'discount_price' => $discountPrice,
                'subtotal' => $line['subtotal'],
                'tax' => $line['tax'],
                'total' => $line['total'],
            ]);
        }

        $cart->recalculateTotals();
        $cart->load($this->cartRelations());

        return $this->cartResponse(
            true,
            __('front.cart_created_successfully'),
            $cart
        );
    }

    public function updateItem(array $validated, Request $request): JsonResponse
    {
        $cart = Cart::resolveForRequest($request, false);

        if (! $cart || ! $cart->belongsToRequest($request)) {
            return response()->json([
                'success' => false,
                'message' => __('front.unauthorized'),
            ], 403);
        }

        $cartItem = $cart->cartItems()
            ->with(['product', 'productVariant.product'])
            ->find($validated['cart_item_id']);

        if (! $cartItem) {
            return response()->json([
                'success' => false,
                'message' => __('front.cart_item_not_found'),
            ], 404);
        }

        $requestedQty = (int) $validated['qty'];

        if ($requestedQty === 0) {
            $cartItem->delete();
            $cart->recalculateTotals();
            $cart->load($this->cartRelations());

            return $this->cartResponse(
                true,
                __('front.cart_updated_successfully'),
                $cart
            );
        }

        if ($cartItem->product_variant_id) {
            $variant = ProductVariant::query()->whereKey($cartItem->product_variant_id)->first();

            if (! $variant || ! $variant->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => __('front.selected_variant_is_no_longer_available'),
                ], 422);
            }

            if ((float) $variant->qty < $requestedQty) {
                return response()->json([
                    'success' => false,
                    'message' => __('front.insufficient_stock_for_selected_variant'),
                    'data' => [
                        'available_qty' => (float) $variant->qty,
                        'requested_qty' => $requestedQty,
                    ],
                ], 422);
            }

            $price = (float) $variant->selling_price;
            $discountPrice = (float) ($variant->discount_price ?? 0);
        } else {
            $product = Product::query()->whereKey($cartItem->product_id)->first();

            if (! $product || ! $product->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => __('front.selected_product_is_no_longer_available'),
                ], 422);
            }

            if ((float) $product->qty < $requestedQty) {
                return response()->json([
                    'success' => false,
                    'message' => __('front.insufficient_stock_for_selected_product'),
                    'data' => [
                        'available_qty' => (float) $product->qty,
                        'requested_qty' => $requestedQty,
                    ],
                ], 422);
            }

            $price = (float) $product->selling_price;
            $discountPrice = (float) ($product->discount_price ?? 0);
        }

        $line = $this->lineTotals($price, $discountPrice, $requestedQty);

        $cartItem->update([
            'qty' => $requestedQty,
            'price' => $price,
            'discount_price' => $discountPrice,
            'subtotal' => $line['subtotal'],
            'tax' => $line['tax'],
            'total' => $line['total'],
        ]);

        $cart->recalculateTotals();
        $cart->load($this->cartRelations());

        return $this->cartResponse(
            true,
            __('front.cart_updated_successfully'),
            $cart
        );
    }

    public function destroy(Request $request): JsonResponse
    {
        $cart = Cart::resolveForRequest($request, false);

        if (! $cart) {
            return response()->json([
                'success' => true,
                'message' => __('front.cart_deleted_successfully'),
            ]);
        }

        if (! $cart->belongsToRequest($request)) {
            return response()->json([
                'success' => false,
                'message' => __('front.unauthorized'),
            ], 403);
        }

        $cart->cartItems()->delete();
        $cart->delete();

        return response()->json([
            'success' => true,
            'message' => __('front.cart_deleted_successfully'),
        ]);
    }

    public function removeItem(int $cartItemId, Request $request): JsonResponse
    {
        $cart = Cart::resolveForRequest($request, false);

        if (! $cart || ! $cart->belongsToRequest($request)) {
            return response()->json([
                'success' => false,
                'message' => __('front.unauthorized'),
            ], 403);
        }

        $item = $cart->cartItems()->whereKey($cartItemId)->first();

        if (! $item) {
            return response()->json([
                'success' => false,
                'message' => __('front.cart_item_not_found'),
            ], 404);
        }

        $item->delete();
        $cart->recalculateTotals();
        $cart->load($this->cartRelations());

        return $this->cartResponse(
            true,
            __('front.cart_updated_successfully'),
            $cart
        );
    }

    public function merge(Request $request): JsonResponse
    {
        $user = $request->user('sanctum');

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => __('front.unauthorized'),
            ], 401);
        }

        $guestToken = Cart::extractGuestToken($request);
        $cart = Cart::mergeGuestCartIntoUser($guestToken, $user);
        $cart->load($this->cartRelations());

        return $this->cartResponse(
            true,
            __('front.cart_updated_successfully'),
            $cart,
            null,
            true
        );
    }

    private function cartRelations(): array
    {
        return [
            'cartItems.product.images',
            'cartItems.productVariant.product',
            'cartItems.productVariant.variantPrimaryImage',
            'cartItems.productVariant.variantImages',
        ];
    }

    private function taxRate(): float
    {
        return Cart::taxRateFromSettings();
    }

    private function lineTotals(float $price, float $discountPrice, int $qty): array
    {
        $unitFinal = $discountPrice > 0 ? $discountPrice : $price;
        $subtotal = $unitFinal * $qty;
        $tax = $subtotal * $this->taxRate();

        return [
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $subtotal + $tax,
        ];
    }

    private function cartResponse(
        bool $success,
        string $message,
        ?Cart $cart,
        ?string $guestToken = null,
        bool $clearGuestCookie = false
    ): JsonResponse {
        $token = $guestToken;

        if ($cart && $cart->user_id === null) {
            $token = $cart->guest_token;
        } elseif ($cart && $cart->user_id !== null) {
            $token = null;
        }

        $response = response()->json([
            'success' => $success,
            'message' => $message,
            'data' => [
                'cart' => $cart ? $this->buildCartPayload($cart) : null,
                'cart_token' => $token,
            ],
        ]);

        if ($clearGuestCookie) {
            return $response->withCookie(Cookie::forget(Cart::GUEST_TOKEN_COOKIE));
        }

        if ($token) {
            return $response->withCookie(cookie(
                Cart::GUEST_TOKEN_COOKIE,
                $token,
                60 * 24 * 30,
                '/',
                null,
                config('session.secure', false),
                false,
                false,
                'lax'
            ));
        }

        return $response;
    }

    private function buildCartPayload(Cart $cart): array
    {
        $availableSubtotal = 0.0;
        $availableTax = 0.0;
        $availableTotal = 0.0;

        $cartItems = $cart->cartItems->map(function (CartItem $item) use (&$availableSubtotal, &$availableTax, &$availableTotal): array {
            $availability = $this->resolveItemAvailability($item);

            if ($availability['is_available']) {
                $availableSubtotal += (float) $item->subtotal;
                $availableTax += (float) $item->tax;
                $availableTotal += (float) $item->total;
            }

            $variant = $item->productVariant;
            $product = $item->product;

            if (! $product && $variant) {
                $product = $variant->product;
            }

            $productTranslations = $product ? $product->getTranslations('name') : null;
            $productName = null;
            if (is_array($productTranslations)) {
                $locale = app()->getLocale();
                $productName = $productTranslations[$locale] ?? $productTranslations['en'] ?? reset($productTranslations);
            }

            $productThumbnail = $product ? MediaUrl::public($product->thumbnail) : null;
            $variantImage = $variant && $variant->variantPrimaryImage
                ? MediaUrl::public($variant->variantPrimaryImage->image_path)
                : null;
            $imageUrl = $variantImage ?: $productThumbnail;

            return [
                'id' => $item->id,
                'product_name' => $productName,
                'product_name_translations' => $productTranslations,
                'product_id' => $item->product_id,
                'product_variant_id' => $item->product_variant_id,
                'qty' => (int) $item->qty,
                'price' => (float) $item->price,
                'discount_price' => (float) $item->discount_price,
                'subtotal' => (float) $item->subtotal,
                'tax' => (float) $item->tax,
                'total' => (float) $item->total,
                'is_available' => $availability['is_available'],
                'availability_message' => $availability['availability_message'],
                'available_qty' => $availability['available_qty'],
                'image_url' => $imageUrl,
                'product' => $product ? [
                    'id' => $product->id,
                    'name' => $productTranslations,
                    'slug' => $product->slug,
                    'type' => $product->type,
                    'thumbnail' => $productThumbnail,
                ] : null,
                'variant' => $variant ? [
                    'id' => $variant->id,
                    'sku' => $variant->sku,
                    'selling_price' => $variant->selling_price,
                    'discount_price' => $variant->discount_price,
                    'qty' => $variant->qty,
                    'featured_image' => $variantImage,
                ] : null,
            ];
        });

        return [
            'id' => $cart->id,
            'user_id' => $cart->user_id,
            'guest_token' => $cart->user_id ? null : $cart->guest_token,
            'subtotal' => $availableSubtotal,
            'tax' => $availableTax,
            'discount' => 0,
            'total' => $availableTotal,
            'cart_items' => $cartItems,
        ];
    }

    private function resolveItemAvailability(CartItem $item): array
    {
        if ($item->product_variant_id) {
            $variant = $item->productVariant;

            if (! $variant || ! $variant->is_active) {
                return [
                    'is_available' => false,
                    'availability_message' => __('front.selected_variant_is_no_longer_available'),
                    'available_qty' => null,
                ];
            }

            if ((float) $variant->qty < (float) $item->qty) {
                return [
                    'is_available' => false,
                    'availability_message' => __('front.insufficient_stock_for_selected_variant'),
                    'available_qty' => (float) $variant->qty,
                ];
            }

            return [
                'is_available' => true,
                'availability_message' => null,
                'available_qty' => null,
            ];
        }

        $product = $item->product;

        if (! $product || ! $product->is_active) {
            return [
                'is_available' => false,
                'availability_message' => __('front.selected_product_is_no_longer_available'),
                'available_qty' => null,
            ];
        }

        if ((float) $product->qty < (float) $item->qty) {
            return [
                'is_available' => false,
                'availability_message' => __('front.insufficient_stock_for_selected_product'),
                'available_qty' => (float) $product->qty,
            ];
        }

        return [
            'is_available' => true,
            'availability_message' => null,
            'available_qty' => null,
        ];
    }
}
