<?php

namespace App\Http\Resources;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return  [
            'order_id' => Order::find($this->order_id),
            'product_id' => $this->product_id,
            'product_variant_id' => $this->product_variant_id,
            'qty' => $this->qty,
            'price' => $this->price,
            'discount_price' => $this->discount_price,
            'subtotal',
            'total',
        ];
    }

}
