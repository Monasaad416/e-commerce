<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Translatable\HasTranslations;

class Product extends Model
{
    use HasTranslations;

    public array $translatable = [
        'name',
        'description',
        'short_description',
        'meta_keywords',
        'meta_description',
    ];

    protected $casts = [
        'name' => 'array',
        'description' => 'array',
        'short_description' => 'array',
        'meta_keywords' => 'array',
        'meta_description' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    protected $fillable = [
        'name',
        'sku',
        'category_id',
        'purchase_price',
        'selling_price',
        'qty',
        'discount_price',
        'img',
        'thumbnail',
        'is_active',
        'is_featured',
        'meta_keywords',
        'meta_description',
        'description',
        'short_description',
    ];

    


    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $name = is_array($model->name)
                    ? ($model->name['en'] ?? collect($model->name)->first())
                    : $model->name;

                $model->slug = Str::slug($name);
            }
        });
    }


    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }


    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'product_tags');
    }


    public function productAttributeValues()
    {
        return $this->belongsToMany(AttributeValue::class, 'product_attribute_values', 'product_id', 'attribute_value_id')
                    ->withTimestamps();
    }
    public function attributes()
    {
        return $this->belongsToMany(Attribute::class, 'attribute_values')
            ->withPivot('value')
            ->withTimestamps();
    }

//         public function attributes()
//     {
//         return $this->belongsToMany(Attribute::class, 'attribute', 'product_id', 'attribute_id');
//     }

// public function attributeValues()
// {
//     return $this->belongsToMany(AttributeValue::class, 'attribute_product', 'product_id', 'attribute_value_id')
//                 ->withTimestamps();
// }

// public function attributes()
// {
//     return $this->hasManyThrough(
//         Attribute::class,
//         AttributeValue::class,
//         'id', // Foreign key on attribute_values table
//         'id', // Foreign key on attributes table
//         'id', // Local key on products table
//         'attribute_id' // Local key on attribute_values table
//     )->distinct();
// }


}
