<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Attribute extends Model
{
    use HasTranslations;

    public $translatable = ['name'];

    protected $fillable = ['name','type'];

    public function productAttributeValues()
    {
        return $this->hasMany(AttributeValue::class);
    }

    // public function values()
    // {
    //     return $this->hasMany(AttributeValue::class);
    // }

    // public function products()
    // {
    //     return $this->hasManyThrough(
    //         Product::class,
    //         AttributeValue::class,
    //         'attribute_id',
    //         'id',
    //         'id',
    //         'product_id'
    //     )->distinct();
    // }


    public function values()
    {
        return $this->hasMany(AttributeValue::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'attribute_values')
            ->withPivot('value')
            ->withTimestamps();
    }
}
