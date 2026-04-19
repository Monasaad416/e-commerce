<?php

namespace App\Http\Resources;

use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
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
            'product' => new ProductResource($this->product),
            'qty' => $this->qty,
            'price' => $this->price,
            'total' => $this->total,
            'subtotal' => $this->subtotal,
            'tax' => $this->tax,
            'discount' => $this->discount,


        ];
    }



}
