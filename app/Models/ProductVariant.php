<?php

namespace App\Models;

use App\Observers\ProductVariantObserver;
use App\Policies\ProductVariantPolicy;
use App\Traits\HasCreatorAndUpdater;
use Database\Factories\ProductVariantFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property int $product_id
 * @property string|null $sku
 * @property float|null $price
 * @property float|null $discount_price
 * @property bool $is_discounted
 * @property int $stock_quantity
 * @property Product $product
 * @property Collection<ProductVariantOption> $options
 *
 * @method static create(mixed $validated)
 * @method static forceCreate(array $array)
 * @method static where(string $column, mixed $operator, mixed $value = null)
 */
#[Fillable([
    'product_id',
    'sku',
    'price',
    'discount_price',
    'is_discounted',
    'stock_quantity',
])]
#[Hidden([])]
#[ObservedBy(ProductVariantObserver::class)]
#[UsePolicy(ProductVariantPolicy::class)]
class ProductVariant extends Model
{
    use HasCreatorAndUpdater;

    /** @use HasFactory<ProductVariantFactory> */
    use HasFactory;

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
            'is_discounted' => 'boolean',
            'stock_quantity' => 'integer',
        ];
    }

    /**
     * Convert price from cents (database) to euros (application).
     */
    protected function price(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value !== null ? $value / 100 : null,
            set: fn ($value) => $value !== null ? (int) round($value * 100) : null,
        );
    }

    /**
     * Convert discount price from cents (database) to euros (application).
     */
    protected function discountPrice(): Attribute
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
     * Get the product that owns this variant.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the variant options for this variant.
     */
    public function options(): BelongsToMany
    {
        return $this->belongsToMany(ProductVariantOption::class);
    }
}
