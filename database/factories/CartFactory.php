<?php

namespace Database\Factories;

use App\Models\Cart;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cart>
 */
class CartFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cart_token' => $this->faker->uuid(),
            'user_id' => null,
            'is_guest' => true,
            'expires_at' => now()->addDays(30),
        ];
    }

    /**
     * Indicate the cart belongs to a user.
     */
    public function forUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => User::factory(),
            'is_guest' => false,
        ]);
    }

    /**
     * Indicate the cart is a guest cart.
     */
    public function guest(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
            'is_guest' => true,
        ]);
    }

    /**
     * Indicate the cart is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subDays(),
        ]);
    }
}
