<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class AttributeValue extends Model
{
    use HasTranslations;

    public $translatable = ['value'];

    protected $fillable = ['value', 'attribute_id'];


    public function attribute() { return $this->belongsTo(Attribute::class); }
    public function tag() { return $this->belongsTo(Tag::class); }
}
