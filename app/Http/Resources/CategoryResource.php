<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{


    public function toArray($request)
    {

        return [
            'id' => $this->id,
            'name' => $this->getTranslations('name'),
            'slug' => $this->slug,
            'description' => $this->getTranslations('description'),
            'is_active' => $this->is_active,
            'meta_keywords' => $this->getTranslations('meta_keywords'),
            'meta_description' => $this->getTranslations('meta_description'),
            'img'=> $this->img,
        ];
    }
}
