<?php

namespace App\Models;

use App\Policies\ProductVariantOptionPolicy;
use Database\Factories\ProductVariantOptionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property int $product_variant_option_type_id
 * @property string $name
 * @property string|null $value
 * @property ProductVariantOptionType $type
 * @property Collection<ProductVariant> $productVariants
 *
 * @method static create(mixed $validated)
 * @method static forceCreate(array $array)
 * @method static where(string $column, mixed $operator, mixed $value = null)
 */
#[Fillable([
    'product_variant_option_type_id',
    'name',
    'value',
])]
#[Hidden([])]
#[UsePolicy(ProductVariantOptionPolicy::class)]
class ProductVariantOption extends Model
{
    /** @use HasFactory<ProductVariantOptionFactory> */
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | Eloquent Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Get the option type that owns this option.
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(ProductVariantOptionType::class, 'product_variant_option_type_id');
    }

    /**
     * Get the product variants that have this option.
     */
    public function productVariants(): BelongsToMany
    {
        return $this->belongsToMany(ProductVariant::class);
    }
}
