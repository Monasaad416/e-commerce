<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class AttributeValue extends Model
{
    use HasTranslations;

    public $translatable = ['value'];

    protected $fillable = ['value', 'attribute_id'];


    public function tag() {
        return $this->belongsTo(Tag::class);
    }


    
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }

    public function productAttributeValues()
    {
        return $this->belongsToMany(Product::class, 'product_attribute_values', 'product_id', 'attribute_value_id')
            ->withTimestamps();
    }
}
