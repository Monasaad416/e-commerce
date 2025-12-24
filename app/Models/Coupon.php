<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'type',
        'value',
        'min_purchase',
        'usage_limit',
        'per_user_limit',
        'start_date',
        'end_date',
        'is_active',
    ];
}
