<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Translatable\HasTranslations;

class Category extends Model implements HasMedia
{
    use InteractsWithMedia;
    use HasFactory;
    use HasTranslations;

    public array $translatable = ['name','slug','description','meta_keywords','meta_description'];

    protected $fillable = [
        'name',
        'description',
        'slug',
        'img',
        'thumbnail',
        'meta_keywords',
        'meta_description',
        'is_active',
        'parent_id'
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


    public static function rules(): array
    {
        return [
            'name.ar' => 'required|string|max:255',
            'name.en' => 'nullable|string|max:255',
            'description.ar' => 'required|string',
            'description.en' => 'nullable|string',
            'slug' => 'required|string|max:255|unique:categories,slug',
            'is_active' => 'boolean',
            'parent_id' => 'nullable|exists:categories,id',
        ];
    }


    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(300)
            ->height(300)
            ->sharpen(10)
            ->performOnCollections('images')
            ->nonQueued();
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
