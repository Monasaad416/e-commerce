<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Tag extends Model
{
    use HasTranslations;

    public array $translatable = [
        'name',
    ];

    protected $fillable = [
        'name',
    ];


    public function products()
    {
        return $this->belongsToMany(Product::class);
    }


}
