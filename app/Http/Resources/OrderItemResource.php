<?php

namespace App\Http\Resources;

use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $product = $this->relationLoaded('product') ? $this->product : null;
        $name = $this->name;
        if (is_array($name)) {
            $locale = app()->getLocale();
            $displayName = $name[$locale] ?? $name['en'] ?? reset($name);
        } else {
            $displayName = $name;
        }

        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'product_id' => $this->product_id,
            'product_variant_id' => $this->product_variant_id,
            'name' => $displayName,
            'name_translations' => is_array($this->name) ? $this->name : null,
            'qty' => (float) $this->qty,
            'price' => (float) $this->price,
            'discount_price' => (float) $this->discount_price,
            'tax' => (float) $this->tax,
            'subtotal' => (float) $this->subtotal,
            'total' => (float) $this->total,
            'product' => $product ? [
                'id' => $product->id,
                'slug' => $product->slug,
                'type' => $product->type,
                'thumbnail' => MediaUrl::public($product->thumbnail),
            ] : null,
        ];
    }
}
