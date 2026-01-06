<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'qty',
        'purchase_price',
        'selling_price',
        'discount_price',
        'is_active',
        'is_featured',
        'sku',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }


    public function attributes()
    {
        return $this->belongsToMany(
            Attribute::class,
            'variant_attribute_values',
            'product_variant_id', // FK على جدول product_variants
            'attribute_id'        // FK على جدول attributes
        )
        ->withPivot('attribute_value_id')
        ->withTimestamps();
    }


   public function attributeValues()
    {
        return $this->hasManyThrough(
            AttributeValue::class,
            VariantAttributeValue::class,
            'product_variant_id', // Foreign key on variant_attribute_values table
            'id', // Foreign key on attribute_values table
            'id', // Local key on product_variants table
            'attribute_value_id' // Local key on variant_attribute_values table
        );
    }

    public function getAttributeValuesStringAttribute()
    {
        $values = $this->attributeValues->pluck('value')->join(', ');
        return $values;
    }


    
    public function variantImages()
    {
        return $this->hasMany(VariantImage::class);
    }

    public function variantPrimaryImage()
    {
        return $this->hasOne(VariantImage::class)->where('is_primary', 1);
    }

}

