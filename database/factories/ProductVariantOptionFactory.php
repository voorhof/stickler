<?php

namespace Database\Factories;

use App\Models\ProductVariantOption;
use App\Models\ProductVariantOptionType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductVariantOption>
 */
class ProductVariantOptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_variant_option_type_id' => ProductVariantOptionType::factory(),
            'name' => $this->faker->word(),
            'value' => $this->faker->hexColor(),
        ];
    }
}
