<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class VariantImage extends Model
{
    protected $fillable = ['product_variant_id', 'image_path', 'is_featured'];

    protected $casts = [
        'is_featured' => 'boolean',
    ];
    
    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    protected static function booted()
    {

        //if make one featured make other unfeatured
        static::saving(function ($image) {
            if ($image->is_featured) {
                $image->variant
                    ->variantImages()
                    ->where('id', '!=', $image->id)
                    ->update(['is_featured' => false]);
            }
        });

        //delete old img when update
        static::updating(function ($image) {

            // if no old img
            if (! $image->getOriginal('image_path')) {
                return;
            }

            // if new img is empty
            if (blank($image->image_path)) {
                return;
            }

            // delete old img only if img changed
            if ($image->isDirty('image_path')) {
                Storage::disk('public')->delete($image->getOriginal('image_path'));
            }
        });

        //delete img when delete
        static::deleting(function ($image) {
            if ($image->image_path) {
                Storage::disk('public')->delete($image->image_path);
            }
        });
    }

}
