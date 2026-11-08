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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Concerns\LogsActivity;

/**
 * @property int $id
 * @property string $cart_token
 * @property int|null $user_id
 * @property bool $is_guest
 * @property DateTime|null $expires_at
 * @property array|null $customer_data
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
 * @method static whereNotNull(string $string)
 */
#[Fillable([
    'cart_token',
    'user_id',
    'is_guest',
    'expires_at',
    'customer_data',
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
            'customer_data' => 'array',
        ];
    }

    /**
     * Get the sum of all cart item total prices in cents, cached per request.
     */
    protected function getItemsSumInCents(): float
    {
        if ($this->itemsSumCache !== null) {
            return $this->itemsSumCache;
        }

        if ($this->relationLoaded('cartItems')) {
            $this->itemsSumCache = (float) $this->cartItems->sum(fn ($item) => $item->getAttributes()['total_price']);
        } else {
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
            get: fn () => $this->getItemsSumInCents() / 100,
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
        if ($this->relationLoaded('cartItems')) {
            return (int) $this->cartItems->sum('quantity');
        }

        return (int) $this->cartItems()->sum('quantity');
    }

    /**
     * Check if the cart has no items.
     */
    public function isEmpty(): bool
    {
        if ($this->relationLoaded('cartItems')) {
            return $this->cartItems->isEmpty();
        }

        return $this->cartItems()->doesntExist();
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

    /*
    |--------------------------------------------------------------------------
    | Customer Data Accessors
    |--------------------------------------------------------------------------
    */

    /**
     * Get the customer's name from the customer data.
     */
    public function customerName(): ?string
    {
        return $this->customer_data['name'] ?? null;
    }

    /**
     * Get the customer's email from the customer data.
     */
    public function customerEmail(): ?string
    {
        return $this->customer_data['email'] ?? null;
    }

    /**
     * Get the customer's phone from the customer data.
     */
    public function customerPhone(): ?string
    {
        return $this->customer_data['phone'] ?? null;
    }

    /**
     * Get the customer's shipping address from the customer data.
     *
     * @return array<string, string>|null
     */
    public function customerShippingAddress(): ?array
    {
        return $this->customer_data['shipping_address'] ?? null;
    }

    /**
     * Get the customer's billing address from the customer data.
     *
     * @return array<string, string>|null
     */
    public function customerBillingAddress(): ?array
    {
        return $this->customer_data['billing_address'] ?? null;
    }

    /**
     * Get the customer's notes from the customer data.
     */
    public function customerNotes(): ?string
    {
        return $this->customer_data['notes'] ?? null;
    }

    /**
     * Merge another cart into this cart with stock validation and clamping.
     */
    public function merge(self $other): void
    {
        if ($this->id === $other->id) {
            return;
        }

        DB::transaction(function () use ($other) {
            $this->loadMissing('cartItems.product', 'cartItems.productVariant');
            $other->loadMissing('cartItems.product', 'cartItems.productVariant');

            foreach ($other->cartItems as $item) {
                $product = $item->product;
                $variant = $item->productVariant;

                if (! $product) {
                    $item->delete();

                    continue;
                }

                $availableStock = $variant?->stock_quantity ?? $product->stock_quantity;

                $existing = $this->cartItems
                    ->where('product_id', $item->product_id)
                    ->where('product_variant_id', $item->product_variant_id)
                    ->first();

                if ($existing) {
                    if ($availableStock <= 0) {
                        $existing->delete();
                    } else {
                        $newQuantity = min($existing->quantity + $item->quantity, $availableStock);
                        if ($newQuantity > 0) {
                            $existing->quantity = $newQuantity;
                            $existing->total_price = $existing->unit_price * $existing->quantity;
                            $existing->save();
                        } else {
                            $existing->delete();
                        }
                    }
                    $item->delete();
                } else {
                    if ($availableStock > 0) {
                        $newQuantity = min($item->quantity, $availableStock);
                        $item->cart_id = $this->id;
                        $item->quantity = $newQuantity;
                        $item->total_price = $item->unit_price * $newQuantity;
                        $item->save();
                    } else {
                        $item->delete();
                    }
                }
            }

            $other->cartItems()->delete();
            $this->unsetRelation('cartItems');
            $other->unsetRelation('cartItems');
        });
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
