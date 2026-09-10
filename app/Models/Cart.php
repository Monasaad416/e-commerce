<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Cart extends Model
{
    public const GUEST_TOKEN_HEADER = 'X-Cart-Token';

    public const GUEST_TOKEN_COOKIE = 'cart_token';

    protected $fillable = [
        'user_id',
        'guest_token',
        'subtotal',
        'tax',
        'discount',
        'total',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'tax' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public static function extractGuestToken(Request $request): ?string
    {
        $token = $request->header(self::GUEST_TOKEN_HEADER)
            ?? $request->cookie(self::GUEST_TOKEN_COOKIE)
            ?? $request->input('cart_token');

        if (! is_string($token) || trim($token) === '') {
            return null;
        }

        return substr(trim($token), 0, 64);
    }

    public static function newGuestToken(): string
    {
        return (string) Str::uuid();
    }

    /**
     * Resolve the cart for an authenticated user or guest token.
     * Uses Sanctum optionally via auth('sanctum')->user().
     */
    public static function resolveForRequest(Request $request, bool $create = true): ?self
    {
        $user = $request->user('sanctum');

        if ($user) {
            $cart = static::query()->where('user_id', $user->id)->first();

            if ($cart || ! $create) {
                return $cart;
            }

            return static::query()->create([
                'user_id' => $user->id,
                'guest_token' => null,
                'subtotal' => 0,
                'tax' => 0,
                'discount' => 0,
                'total' => 0,
            ]);
        }

        $token = static::extractGuestToken($request);

        if ($token) {
            $cart = static::query()
                ->whereNull('user_id')
                ->where('guest_token', $token)
                ->first();

            if ($cart || ! $create) {
                return $cart;
            }

            return static::query()->create([
                'user_id' => null,
                'guest_token' => $token,
                'subtotal' => 0,
                'tax' => 0,
                'discount' => 0,
                'total' => 0,
            ]);
        }

        if (! $create) {
            return null;
        }

        return static::query()->create([
            'user_id' => null,
            'guest_token' => static::newGuestToken(),
            'subtotal' => 0,
            'tax' => 0,
            'discount' => 0,
            'total' => 0,
        ]);
    }

    public function belongsToRequest(Request $request): bool
    {
        $user = $request->user('sanctum');

        if ($user) {
            return (int) $this->user_id === (int) $user->id;
        }

        $token = static::extractGuestToken($request);

        return $token !== null
            && $this->user_id === null
            && hash_equals((string) $this->guest_token, $token);
    }

    public function recalculateTotals(): void
    {
        $items = $this->cartItems()->get();

        $subtotal = (float) $items->sum('subtotal');
        $tax = (float) $items->sum('tax');
        $total = (float) $items->sum('total');

        $this->forceFill([
            'subtotal' => $subtotal,
            'tax' => $tax,
            'discount' => 0,
            'total' => $total,
        ])->save();
    }

    /**
     * Merge a guest cart into the authenticated user's cart.
     */
    public static function mergeGuestCartIntoUser(?string $guestToken, User $user): self
    {
        return DB::transaction(function () use ($guestToken, $user) {
            $userCart = static::query()->firstOrCreate(
                ['user_id' => $user->id],
                [
                    'guest_token' => null,
                    'subtotal' => 0,
                    'tax' => 0,
                    'discount' => 0,
                    'total' => 0,
                ]
            );

            if (! $guestToken) {
                return $userCart->fresh(['cartItems']);
            }

            $guestCart = static::query()
                ->whereNull('user_id')
                ->where('guest_token', $guestToken)
                ->with('cartItems')
                ->first();

            if (! $guestCart || $guestCart->id === $userCart->id) {
                return $userCart->fresh(['cartItems']);
            }

            foreach ($guestCart->cartItems as $item) {
                $existing = $userCart->cartItems()
                    ->where('product_id', $item->product_id)
                    ->where('product_variant_id', $item->product_variant_id)
                    ->first();

                if ($existing) {
                    $qty = (int) $existing->qty + (int) $item->qty;
                    $unitFinal = (float) $existing->discount_price > 0
                        ? (float) $existing->discount_price
                        : (float) $existing->price;
                    $subtotal = $unitFinal * $qty;
                    $taxRate = static::taxRateFromSettings();
                    $tax = $subtotal * $taxRate;

                    $existing->update([
                        'qty' => $qty,
                        'subtotal' => $subtotal,
                        'tax' => $tax,
                        'total' => $subtotal + $tax,
                    ]);
                    $item->delete();
                } else {
                    $item->update(['cart_id' => $userCart->id]);
                }
            }

            $guestCart->delete();
            $userCart->recalculateTotals();

            return $userCart->fresh(['cartItems']);
        });
    }

    public static function taxRateFromSettings(): float
    {
        $setting = Setting::query()->where('key', 'tax_rate')->first();
        $value = $setting ? $setting->value : 0.15;

        return is_numeric($value) ? (float) $value : 0.15;
    }
}
