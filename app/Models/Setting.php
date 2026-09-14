<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    protected $casts = [
        'value' => 'array',
    ];

    public function getValueAttribute(string $value)
    {
        return json_decode($value, true);
    }

    public function setValueAttribute(string $value)
    {
        $this->attributes['value'] = json_encode($value);
    }
}
