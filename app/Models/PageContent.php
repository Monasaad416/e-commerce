<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;

class PageContent extends Model
{
    protected $fillable = ["page", "content"];
    
    protected $casts = [
        'content' => AsArrayObject::class, 
    ];

    protected $appends = ["content_object"];

    public function getContentObjectAttribute() {
        return (object) $this->content;
    }
}