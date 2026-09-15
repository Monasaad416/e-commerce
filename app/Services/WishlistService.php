<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use App\Support\MediaUrl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistService
{
    public function index(Request $request): JsonResponse
    {
        $wishlist = Wishlist::resolveForUser((int) $request->user()->id, false);

        if (! $wishlist) {
            return response()->json([
                'success' => true,
                'message' => __('front.wishlist_retrieved_successfully'),
                'data' => [
                    'id' => null,
                    'user_id' => $request->user()->id,
                    'items_count' => 0,
                    'items' => [],
                ],
            ]);
        }

        $wishlist->load($this->itemRelations());

        return $this->wishlistResponse(
            true,
            __('front.wishlist_retrieved_successfully'),
            $wishlist
        );
    }

    public function addItem(array $validated, Request $request): JsonResponse
    {
        $productId = (int) $validated['product_id'];

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

        $wishlist = Wishlist::resolveForUser((int) $request->user()->id, true);

        $existing = $wishlist->items()->where('product_id', $productId)->first();

        if ($existing) {
            $wishlist->load($this->itemRelations());

            return $this->wishlistResponse(
                true,
                __('front.wishlist_item_already_exists'),
                $wishlist
            );
        }

        $price = (float) (
            $product->discount_price !== null && (float) $product->discount_price > 0
                ? $product->discount_price
                : $product->selling_price
        );

        $wishlist->items()->create([
            'product_id' => $productId,
            'price' => $price,
        ]);

        $wishlist->load($this->itemRelations());

        return $this->wishlistResponse(
            true,
            __('front.wishlist_item_added_successfully'),
            $wishlist,
            201
        );
    }

    public function removeItem(int $wishlistItemId, Request $request): JsonResponse
    {
        $wishlist = Wishlist::resolveForUser((int) $request->user()->id, false);

        if (! $wishlist) {
            return response()->json([
                'success' => false,
                'message' => __('front.wishlist_item_not_found'),
            ], 404);
        }

        $item = $wishlist->items()->whereKey($wishlistItemId)->first();

        if (! $item) {
            return response()->json([
                'success' => false,
                'message' => __('front.wishlist_item_not_found'),
            ], 404);
        }

        $item->delete();
        $wishlist->load($this->itemRelations());

        return $this->wishlistResponse(
            true,
            __('front.wishlist_item_removed_successfully'),
            $wishlist
        );
    }

    public function removeByProduct(int $productId, Request $request): JsonResponse
    {
        $wishlist = Wishlist::resolveForUser((int) $request->user()->id, false);

        if (! $wishlist) {
            return response()->json([
                'success' => false,
                'message' => __('front.wishlist_item_not_found'),
            ], 404);
        }

        $item = $wishlist->items()->where('product_id', $productId)->first();

        if (! $item) {
            return response()->json([
                'success' => false,
                'message' => __('front.wishlist_item_not_found'),
            ], 404);
        }

        $item->delete();
        $wishlist->load($this->itemRelations());

        return $this->wishlistResponse(
            true,
            __('front.wishlist_item_removed_successfully'),
            $wishlist
        );
    }

    private function wishlistResponse(bool $success, string $message, Wishlist $wishlist, int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => $success,
            'message' => $message,
            'data' => $this->buildWishlistPayload($wishlist),
        ], $status);
    }

    private function buildWishlistPayload(Wishlist $wishlist): array
    {
        $items = $wishlist->items->map(function (WishlistItem $item): array {
            $product = $item->product;
            $translations = $product ? $product->getTranslations('name') : null;
            $locale = app()->getLocale();
            $name = is_array($translations)
                ? ($translations[$locale] ?? $translations['en'] ?? reset($translations))
                : null;

            $thumbnail = $product ? MediaUrl::public($product->thumbnail) : null;
            $currentPrice = $product
                ? (float) (
                    $product->discount_price !== null && (float) $product->discount_price > 0
                        ? $product->discount_price
                        : $product->selling_price
                )
                : (float) $item->price;

            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'price' => (float) $item->price,
                'current_price' => $currentPrice,
                'product_name' => $name,
                'product_name_translations' => $translations,
                'image_url' => $thumbnail,
                'product' => $product ? [
                    'id' => $product->id,
                    'name' => $translations,
                    'slug' => $product->slug,
                    'type' => $product->type,
                    'selling_price' => (float) $product->selling_price,
                    'discount_price' => (float) ($product->discount_price ?? 0),
                    'thumbnail' => $thumbnail,
                    'is_active' => (bool) $product->is_active,
                ] : null,
            ];
        })->values();

        return [
            'id' => $wishlist->id,
            'user_id' => $wishlist->user_id,
            'items_count' => $items->count(),
            'items' => $items,
        ];
    }

    private function itemRelations(): array
    {
        return ['items.product'];
    }
}
