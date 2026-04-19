<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VariantResource extends JsonResource
{
    public function toArray($request)
    {
        $lang = $request->get('lang', app()->getLocale());

        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'selling_price' => $this->selling_price,
            'discount_price' => $this->discount_price,
            'qty' => $this->qty,
'attribute_values' => $this->whenLoaded('variantAttributeValues', function () use ($lang) {
    return $this->variantAttributeValues->map(function ($value) use ($lang) {
        return [
            'id' => $value->id,
            'value' => $value->attributeValue?->getTranslation('value', $lang),
            'attribute_id' => $value->attribute_id,
            'attribute_name' => $value->attribute?->getTranslation('name', $lang),
        ];
    })->all();
}) ?? [],


            'primary_image' => $this->variantPrimaryImage ? asset('storage/' . $this->variantPrimaryImage->image_path) : null,
            'images' => $this->variantImages->map(function ($image) {
                return [
                    'id' => $image->id,
                    'url' => asset('storage/' . $image->image_path),
                    'is_featured' => (bool) $image->is_featured,
                ];
            }),
        ];
    }
}
