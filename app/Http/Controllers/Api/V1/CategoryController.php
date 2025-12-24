<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;



class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::paginate(10);

        return CategoryResource::collection($categories)
            ->response()
            ->setStatusCode(200);
    }



   public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validate([
                'name' => 'required|array',
                'name.en' => 'required|string|unique:categories,name->en',
                'name.ar' => 'required|string|unique:categories,name->ar',
                'description' => 'nullable|array',
                'meta_keywords' => 'nullable|array',
                'meta_description' => 'nullable|array',
                'is_active' => 'boolean',
                'img' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            ]);

            $validated['slug'] = [
                'en' => Str::slug($validated['name']['en']),
                'ar' => Str::slug($validated['name']['ar']),
            ];

            $category = Category::create($validated);


        $image = ImageManager::gd()->read($request->file('img'))
            ->resize(1000, null, fn($c) => $c->aspectRatio())
            ->encode(new WebpEncoder(85));

        // add image to media library
            $category->addMediaFromStream($image->__toString())
            ->usingFileName(Str::slug($validated['name']['en']).'.webp')

            ->toMediaCollection('images');

                    DB::commit();

                    return response()->json([
                        'message' => 'Category created successfully',
                        'category' => $category->load('media'),
                    ]);

                } catch (\Exception $e) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Failed to create category',
                        'error' => $e->getMessage(),
                    ], 500);
                }
            }


        public function update(Request $request, $id)
        {
            DB::beginTransaction();

            try {
                $category = Category::findOrFail($id);
                $validated = $request->validate([
                    'id' => 'required|exists:categories,id',
                    'name' => 'required|array',
                    'name.en' => 'required|string|unique:categories,name->en',
                    'name.ar' => 'required|string|unique:categories,name->ar',
                    'description' => 'nullable|array',
                    'meta_keywords' => 'nullable|array',
                    'meta_description' => 'nullable|array',
                    'is_active' => 'boolean',
                    'img' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
                ]);

                $category->update($validated);

                if ($request->hasFile('img')) {
                    $image = ImageManager::gd()->read($request->file('img'))
                        ->resize(1000, null, fn($c) => $c->aspectRatio())
                        ->encode(new WebpEncoder(85));

                    // add image to media library
                        $category->addMediaFromStream($image->__toString())
                        ->usingFileName(Str::slug($validated['name']['en']).'.webp')

                        ->toMediaCollection('images');
                }   

                DB::commit();

                return response()->json([
                    'message' => 'Category updated successfully',
                    'category' => $category->load('media'),
                ]);
                
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Failed to update category',
                    'error' => $e->getMessage(),
                ], 500);
            }

            DB::commit();
        }
}
