<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImage extends Model
{
    protected $fillable = [
        'product_id',
        'image_path',
        'is_primary'
    ];


protected $appends = ['image_url'];

public function getImageUrlAttribute()
{
    if (filter_var($this->image_path, FILTER_VALIDATE_URL)) {
        return $this->image_path;
    }
    return asset('storage/' . ltrim($this->image_path, '/'));
}



    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}