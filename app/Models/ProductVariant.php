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
    public function product() { return $this->belongsTo(Product::class); }

    public function variantAttributeValues()
    {
        return $this->hasMany(VariantAttributeValue::class);
    }

    public function variantImages()
    {
        return $this->hasMany(VariantImage::class);
    }

    public function variantPrimaryImage()
    {
        return $this->hasOne(VariantImage::class)->where('is_featured', 1);
    }

    public function attributes()
    {
        return $this->belongsToMany(
            Attribute::class,
            'variant_attribute_values', // pivot table name
            'product_variant_id',      // foreign key on the pivot table
            'attribute_id'             // related key on the pivot table
        )->withPivot('attribute_value_id')
        ->withTimestamps();
    }

    public function attributeValues()
    {
        return $this->belongsToMany(
            AttributeValue::class,
            'variant_attribute_values',
            'product_variant_id',
            'attribute_value_id'
        )->withPivot('attribute_id')
        ->withTimestamps();
    }

    public function tags()
    {
        return $this->belongsToMany(
            Tag::class,
            'product_tags',
            'product_variant_id',
            'tag_id'
        )
            ->withPivot(['product_id'])
            ->withTimestamps();
    }


}

