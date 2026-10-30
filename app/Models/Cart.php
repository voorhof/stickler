<?php

namespace App\Models;

use App\Observers\CartObserver;
use App\Policies\CartPolicy;
use App\Traits\HasCreatorAndUpdater;
use App\Traits\HasStandardActivitylogOptions;
use App\Traits\LogsTrashedActivity;
use Database\Factories\CartFactory;
use DateTime;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Concerns\LogsActivity;

/**
 * @property int $id
 * @property string $cart_token
 * @property int|null $user_id
 * @property bool $is_guest
 * @property DateTime|null $expires_at
 * @property DateTime $created_at
 * @property DateTime $updated_at
 * @property BelongsTo<User>|null $user
 * @property Collection<CartItem> $cartItems
 * @property mixed $isExpired
 *
 * @method static create(mixed $validated)
 * @method static forceCreate(array $array)
 * @method static where(string $string, mixed $operator, mixed $value = null)
 * @method static whereCartToken(string $cartToken)
 * @method static whereUserId(int $userId)
 * @method static expired()
 * @method static find(mixed $id)
 * @method static firstOrCreate(array $array, array $array1)
 * @method static count()
 * @method static latest()
 * @method static first()
 */
#[Fillable([
    'cart_token',
    'user_id',
    'is_guest',
    'expires_at',
])]
#[Hidden([])]
#[ObservedBy(CartObserver::class)]
#[UsePolicy(CartPolicy::class)]
class Cart extends Model
{
    use HasCreatorAndUpdater;

    /** @use HasFactory<CartFactory> */
    use HasFactory;

    use HasStandardActivitylogOptions {
        HasStandardActivitylogOptions::getActivitylogOptions insteadof LogsActivity;
    }
    use LogsActivity;
    use LogsTrashedActivity;

    /*
    |--------------------------------------------------------------------------
    | Casts, Accessors and Mutators
    |--------------------------------------------------------------------------
    */

    /**
     * Cached items sum to avoid duplicate queries within a single request.
     */
    protected ?float $itemsSumCache = null;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_guest' => 'boolean',
            'expires_at' => 'datetime',
        ];
    }

    /**
     * Get the sum of all cart item total prices in cents, cached per request.
     */
    protected function getItemsSumInCents(): float
    {
        if ($this->itemsSumCache === null) {
            $this->itemsSumCache = (float) $this->cartItems()->sum('total_price');
        }

        return $this->itemsSumCache;
    }

    /**
     * Get the total value of all cart items in cents.
     */
    protected function totalInCents(): Attribute
    {
        return Attribute::make(
            get: fn () => (int) $this->getItemsSumInCents(),
        );
    }

    /**
     * Convert total from cents (computed) to euros (application).
     */
    protected function total(): Attribute
    {
        return Attribute::make(
            get: fn () => (int) ($this->getItemsSumInCents() / 100),
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Boot & Lifecycle
    |--------------------------------------------------------------------------
    */

    /**
     * Bootstrap any application services.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Cart $cart) {
            if (! $cart->cart_token) {
                $cart->cart_token = (string) Str::uuid();
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Get the total quantity of all items in the cart.
     */
    public function totalQuantity(): int
    {
        return $this->cartItems->sum('quantity');
    }

    /**
     * Check if the cart has no items.
     */
    public function isEmpty(): bool
    {
        return $this->cartItems->isEmpty();
    }

    /**
     * Check if the cart has passed its expiration time.
     */
    protected function isExpired(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->expires_at !== null && now()->isAfter($this->expires_at),
        );
    }

    /**
     * Check if the cart has passed its expiration time (public method for tests).
     */
    public function isExpiredMethod(): bool
    {
        return (bool) $this->isExpired;
    }

    /**
     * Merge another cart into this cart.
     */
    public function merge(self $other): void
    {
        foreach ($other->cartItems as $item) {
            $existing = $this->cartItems
                ->where('product_id', $item->product_id)
                ->where('product_variant_id', $item->product_variant_id)
                ->first();

            if ($existing) {
                $existing->quantity += $item->quantity;
                $existing->total_price = $existing->unit_price * $existing->quantity;
                $existing->save();
            } else {
                $item->cart_id = $this->id;
                $item->save();
            }
        }

        $other->cartItems()->delete();
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Scope for expired carts.
     */
    public function scopeExpired($query): void
    {
        $query->where('expires_at', '<', now());
    }

    /*
    |--------------------------------------------------------------------------
    | Eloquent Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Get the user who owns this cart.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the cart items.
     *
     * @return HasMany<CartItem>
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }
}
