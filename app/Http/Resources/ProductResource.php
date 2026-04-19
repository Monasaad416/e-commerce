<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
          
        return  [
            'id' => $this->id,
            'name' => $this->getTranslations('name'),
            'description' => $this->getTranslations('description'),
            'slug' => $this->slug,
            'type' => $this->type,
            'selling_price' => $this->selling_price,
            'discount_price' => $this->discount_price,
            'thumbnail' => $this->thumbnail,
            'meta_keywords' => $this->getTranslations('meta_keywords'),
            'meta_description' => $this->meta_description,
            'is_active' => $this->is_active,
            'is_featured' => $this->is_featured,
            'qty' => $this->qty,
            'category_id' => $this->category_id,
            'category_slug' => $this->category?->slug,
            'category_name' => $this->category?->getTranslations('name') ?? [],
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'variants' => VariantResource::collection($this->whenLoaded('productVariants')),
            'images' => ImageResource::collection($this->whenLoaded('images')),
            'reviews' => ReviewResource::collection($this->whenLoaded('reviews')),
        ];
    }



}
