<?php

// In VariantAttributeValue.php
namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class VariantAttributeValue extends Pivot
{
    protected $table = 'variant_attribute_values';
    public $incrementing = true;

    protected $fillable = [
        'product_variant_id',
        'attribute_id',
        'attribute_value_id'
    ];



    public function attributeValue() { return $this->belongsTo(AttributeValue::class); }
    public function attribute() { return $this->belongsTo(Attribute::class); }
    public function productVariant() { return $this->belongsTo(ProductVariant::class); }
}

