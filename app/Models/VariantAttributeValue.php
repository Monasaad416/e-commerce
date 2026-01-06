<?php

// In VariantAttributeValue.php
namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class VariantAttributeValue extends Pivot
{
    protected $table = 'variant_attribute_values';
    protected $with = ['attributeValue'];


    public function attributeValue()
    {
        return $this->belongsTo(AttributeValue::class);
    }

    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }
}