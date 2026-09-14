<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWishlistRequest;
use App\Services\WishlistService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function __construct(private WishlistService $wishlistService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return $this->wishlistService->index($request);
    }

    public function store(StoreWishlistRequest $request): JsonResponse
    {
        return $this->wishlistService->addItem($request->validated(), $request);
    }

    public function removeItem(Request $request, int $wishlist_item_id): JsonResponse
    {
        return $this->wishlistService->removeItem($wishlist_item_id, $request);
    }

    public function removeByProduct(Request $request, int $product_id): JsonResponse
    {
        return $this->wishlistService->removeByProduct($product_id, $request);
    }
}
