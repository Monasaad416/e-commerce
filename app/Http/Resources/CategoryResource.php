<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{


    public function toArray($request)
    {
        $appUrl = config('app.url');
        
        return [
            'id' => $this->id,
            'name' => $this->getTranslations('name'),
            'slug' => $this->getTranslations('slug'),
            'description' => $this->getTranslations('description'),
            'is_active' => $this->is_active,
            'meta_keywords' => $this->getTranslations('meta_keywords'),
            'meta_description' => $this->getTranslations('meta_description'),
            'media' => $this->getMedia('images')->map(function($media) {
                return [
                    'url' => '/storage/' . $media->id . '/' . $media->file_name,
                    'thumb' => '/storage/' . $media->id . '/conversions/' . pathinfo($media->file_name, PATHINFO_FILENAME) . '-thumb.jpg',
                    'file_name' => $media->file_name,
                    'mime_type' => $media->mime_type,
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
