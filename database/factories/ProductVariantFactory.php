<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $price = $this->faker->randomFloat(2, 1, 9999);

        return [
            'product_id' => Product::factory(),
            'sku' => $this->faker->unique()->ean8(),
            'price' => $price,
            'discount_price' => $price > 1 ? $this->faker->randomFloat(2, 0.99, $price - 0.01) : null,
            'is_discounted' => $this->faker->boolean(25),
            'stock_quantity' => $this->faker->randomNumber(2, true),
            'created_by_user_id' => 1,
            'updated_by_user_id' => 1,
        ];
    }
}
