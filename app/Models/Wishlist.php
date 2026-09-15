<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wishlist extends Model
{
    protected $fillable = [
        'user_id',
        'session_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(WishlistItem::class);
    }

    public static function resolveForUser(int $userId, bool $create = true): ?self
    {
        $wishlist = static::query()->where('user_id', $userId)->first();

        if ($wishlist || ! $create) {
            return $wishlist;
        }

        return static::query()->create(['user_id' => $userId]);
    }
}
