<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Product::with([
                'category',
                'tags',
                'productVariants.tags',
                'productVariants.variantAttributeValues.attribute',
                'productVariants.variantAttributeValues.attributeValue',
                'productVariants.variantPrimaryImage',
                'productVariants.variantImages',
                'productVariants.variantAttributeValues',
                'images'
            ]);
            // Filter by category slug if provided
            if ($request->filled('slug')) {
                $category = Category::where('slug', $request->slug)->first();
                if ($category) {
                    $query->where('category_id', $category->id);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Category not found',
                        'data' => [],
                    ], 404);
                }
            }

            $products = $query->paginate(12);

            return response()->json([
                'success' => true,
                'message' => 'Products retrieved successfully',
                'data' => [
                    'products' => ProductResource::collection($products),
                    'pagination' => [
                        'current_page' => $products->currentPage(),
                        'last_page' => $products->lastPage(),
                        'per_page' => $products->perPage(),
                        'total' => $products->total(),
                    ],
                ],
                'status' => 200
            ]);

        } catch (\Throwable $e) {
            Log::error('Product listing failed', [
                'slug_filter' => $request->slug,
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve products',
                'data' => [],
                'status' => 500
            ], 500);
        }
    }

    public function show(Request $request)
    {
        try {
            $slug = $request->slug;
            $product = Product::with([
                'category',
                'tags',
                'productVariants.tags',
                'productVariants.variantAttributeValues.attribute',
                'productVariants.variantAttributeValues.attributeValue',
                'productVariants.variantPrimaryImage',
                'productVariants.variantImages',
                'productVariants.variantAttributeValues',
                'images'
            ])->where('slug', $slug)->first();

           

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product ffffffff not found',
                    'data' => [],
                    'status' => 404
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Product retrieved successfully',
                'data' => new ProductResource($product),
                'status' => 200
            ]);

        } catch (\Throwable $e) {
            Log::error('Product details failed', [
                'slug' => $request->slug,
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve product',
                'data' => [],
                'status' => 500
            ], 500);
        }
    }
}
