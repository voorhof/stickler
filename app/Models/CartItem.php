<?php

namespace App\Models;

use App\Observers\CartItemObserver;
use App\Policies\CartItemPolicy;
use App\Traits\HasCreatorAndUpdater;
use App\Traits\HasStandardActivitylogOptions;
use App\Traits\LogsTrashedActivity;
use Database\Factories\CartItemFactory;
use DateTime;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;

/**
 * @property int $id
 * @property int $cart_id
 * @property int|null $product_id
 * @property int|null $product_variant_id
 * @property string $product_name
 * @property string|null $product_variant_label
 * @property string|null $sku
 * @property int $quantity
 * @property int $unit_price
 * @property int $total_price
 * @property DateTime $created_at
 * @property DateTime $updated_at
 * @property Cart $cart
 * @property Product|null $product
 * @property ProductVariant|null $productVariant
 *
 * @method static create(mixed $validated)
 * @method static forceCreate(array $array)
 * @method static where(string $string, mixed $operator, mixed $value = null)
 * @method static find(mixed $id)
 */
#[Fillable([
    'cart_id',
    'product_id',
    'product_variant_id',
    'product_name',
    'product_variant_label',
    'sku',
    'quantity',
    'unit_price',
    'total_price',
])]
#[Hidden([])]
#[ObservedBy(CartItemObserver::class)]
#[UsePolicy(CartItemPolicy::class)]
class CartItem extends Model
{
    use HasCreatorAndUpdater;

    /** @use HasFactory<CartItemFactory> */
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
            'quantity' => 'integer',
        ];
    }

    /**
     * Convert unit price from cents (database) to euros (application).
     */
    protected function unitPrice(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value !== null ? $value / 100 : null,
            set: fn ($value) => $value !== null ? (int) round($value * 100) : null,
        );
    }

    /**
     * Convert total price from cents (database) to euros (application).
     */
    protected function totalPrice(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value !== null ? $value / 100 : null,
            set: fn ($value) => $value !== null ? (int) round($value * 100) : null,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Eloquent Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Get the cart that owns this item.
     *
     * @return BelongsTo<Cart, $this>
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * Get the product for this item (may be null if product was deleted).
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the product variant for this item (may be null if variant was deleted).
     */
    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }
}
