<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Attribute extends Model
{
    use HasTranslations;

    public $translatable = ['name'];

    protected $fillable = ['name','type'];


    public function values()
    {
        return $this->hasMany(AttributeValue::class);
    }

}
