<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    
    public array $translatable = [
        'name',
    ];
    
    protected $casts = [
        'name' => 'array',
    ];
    
    protected $fillable = [
        'order_id',
        'product_id',
        'product_variant_id',
        'name',
        'qty',
        'price',
        'tax',
        'discount_price',
        'subtotal',
        'total',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
