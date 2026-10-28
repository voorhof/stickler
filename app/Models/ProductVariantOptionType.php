<?php

namespace App\Models;

use App\Policies\ProductVariantOptionTypePolicy;
use Database\Factories\ProductVariantOptionTypeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property \Illuminate\Database\Eloquent\Collection<ProductVariantOption> $options
 *
 * @method static create(mixed $validated)
 * @method static forceCreate(array $array)
 * @method static where(string $column, mixed $operator, mixed $value = null)
 */
#[Fillable([
    'name',
])]
#[Hidden([])]
#[UsePolicy(ProductVariantOptionTypePolicy::class)]
class ProductVariantOptionType extends Model
{
    /** @use HasFactory<ProductVariantOptionTypeFactory> */
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | Eloquent Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Get the variant options for this type.
     */
    public function options(): HasMany
    {
        return $this->hasMany(ProductVariantOption::class);
    }
}
