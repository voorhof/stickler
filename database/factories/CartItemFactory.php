<?php

namespace Database\Factories;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CartItem>
 */
class CartItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $product = Product::factory();
        $quantity = $this->faker->numberBetween(1, 10);
        $unitPrice = $this->faker->randomNumber(4, true);

        return [
            'cart_id' => Cart::factory(),
            'product_id' => $product,
            'product_variant_id' => null,
            'product_name' => $this->faker->words(3, true),
            'product_variant_label' => null,
            'sku' => $this->faker->unique()->ean8(),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $unitPrice * $quantity,
        ];
    }

    /**
     * Indicate the cart item has a product variant.
     */
    public function withVariant(): static
    {
        return $this->state(function () {
            $variant = ProductVariant::factory();

            return [
                'product_variant_id' => $variant,
                'product_variant_label' => $this->faker->words(3, true),
            ];
        });
    }

    /**
     * Indicate that the model is soft-deleted.
     */
    public function softDeleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'deleted_at' => now(),
        ]);
    }
}
