<?php

namespace App\Http\Resources;

use App\Support\MediaUrl;
use Illuminate\Http\Resources\Json\JsonResource;

class VariantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {

        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'selling_price' => $this->selling_price,
            'discount_price' => $this->discount_price,
            'qty' => $this->qty,
            'attribute_values' => $this->variantAttributeValues->map(function ($value) {
                return [
                    'id' => $value->id,
                    'value' => $value->attributeValue?->getTranslations('value'), 
                    'attribute_id' => $value->attribute_id,
                    'attribute_name' => $value->attribute?->getTranslations('name'), 
                ];
            })->all(),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'featured_image' => $this->variantPrimaryImage
                ? MediaUrl::public($this->variantPrimaryImage->image_path)
                : null,
            'images' => $this->variantImages->map(function ($image) {
                return [
                    'id' => $image->id,
                    'url' => MediaUrl::public($image->image_path),
                    'is_featured' => (bool) $image->is_featured,
                ];
            }),
        ];
    }

}
