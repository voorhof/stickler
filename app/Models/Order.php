<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Observers\OrderObserver;
use App\Policies\OrderPolicy;
use App\Traits\HasCreatorAndUpdater;
use App\Traits\HasStandardActivitylogOptions;
use App\Traits\LogsTrashedActivity;
use Database\Factories\OrderFactory;
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
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Concerns\LogsActivity;

/**
 * @property int $id
 * @property string $order_number
 * @property int|null $user_id
 * @property OrderStatus $status
 * @property PaymentStatus $payment_status
 * @property int $subtotal
 * @property int $tax_amount
 * @property int $shipping_amount
 * @property int $total
 * @property array|null $shipping_address
 * @property array|null $billing_address
 * @property string|null $payment_method
 * @property string|null $notes
 * @property DateTime $created_at
 * @property DateTime $updated_at
 * @property BelongsTo<User>|null $user
 * @property Collection<OrderItem> $items
 *
 * @method static create(mixed $validated)
 * @method static forceCreate(array $array)
 * @method static where(string $column, mixed $operator, mixed $value = null)
 * @method static latest()
 */
#[Fillable([
    'order_number',
    'user_id',
    'status',
    'payment_status',
    'subtotal',
    'tax_amount',
    'shipping_amount',
    'total',
    'shipping_address',
    'billing_address',
    'payment_method',
    'notes',
])]
#[Hidden([])]
#[ObservedBy(OrderObserver::class)]
#[UsePolicy(OrderPolicy::class)]
class Order extends Model
{
    use HasCreatorAndUpdater;

    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    use HasStandardActivitylogOptions {
        HasStandardActivitylogOptions::getActivitylogOptions insteadof LogsActivity;
    }
    use LogsActivity;
    use LogsTrashedActivity;
    use SoftDeletes;

    /*
    |--------------------------------------------------------------------------
    | Casts, Accessors and Mutators
    |--------------------------------------------------------------------------
    */

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'payment_status' => PaymentStatus::class,
            'shipping_address' => 'array',
            'billing_address' => 'array',
        ];
    }

    /**
     * Convert subtotal from cents (database) to euros (application).
     */
    protected function subtotal(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value !== null ? $value / 100 : null,
            set: fn ($value) => $value !== null ? (int) round($value * 100) : null,
        );
    }

    /**
     * Convert tax amount from cents (database) to euros (application).
     */
    protected function taxAmount(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value !== null ? $value / 100 : null,
            set: fn ($value) => $value !== null ? (int) round($value * 100) : null,
        );
    }

    /**
     * Convert shipping amount from cents (database) to euros (application).
     */
    protected function shippingAmount(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value !== null ? $value / 100 : null,
            set: fn ($value) => $value !== null ? (int) round($value * 100) : null,
        );
    }

    /**
     * Convert total from cents (database) to euros (application).
     */
    protected function total(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value !== null ? $value / 100 : null,
            set: fn ($value) => $value !== null ? (int) round($value * 100) : null,
        );
    }

    /**
     * Get the customer name, falling back to 'Guest' for orders without a user.
     */
    public function customerName(): string
    {
        return $this->user?->name ?? __('Guest');
    }

    /**
     * Get the title for Filament actions (uses order_number).
     */
    protected function title(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->order_number,
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

        static::creating(function (Order $order) {
            if (! $order->order_number) {
                $order->order_number = self::generateOrderNumber();
            }
        });
    }

    /**
     * Generate a unique order number like ORD-2026-00001.
     */
    protected static function generateOrderNumber(): string
    {
        $year = now()->format('Y');
        $prefix = 'ORD-'.$year.'-';

        do {
            $count = self::withTrashed()->where('order_number', 'like', $prefix.'%')->count();
            $nextNumber = $count + 1;
            $sequence = Str::padLeft((string) $nextNumber, 5, '0');
            $orderNumber = $prefix.$sequence;
        } while (self::withTrashed()->where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }

    /*
    |--------------------------------------------------------------------------
    | Eloquent Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Get the user who placed the order.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the order items.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
