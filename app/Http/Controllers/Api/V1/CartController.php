<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCartRequest;
use App\Http\Requests\UpdateCartRequest;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private CartService $cartService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return $this->cartService->getCart($request);
    }

    public function store(StoreCartRequest $request): JsonResponse
    {
        return $this->cartService->addItem($request->validated(), $request);
    }

    public function update(UpdateCartRequest $request): JsonResponse
    {
        return $this->cartService->updateItem($request->validated(), $request);
    }

    public function destroy(Request $request): JsonResponse
    {
        return $this->cartService->destroy($request);
    }

    public function removeItem(Request $request, int $cart_item_id): JsonResponse
    {
        return $this->cartService->removeItem($cart_item_id, $request);
    }

    public function merge(Request $request): JsonResponse
    {
        return $this->cartService->merge($request);
    }
}
