<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = $this->faker->randomNumber(4, true);
        $taxAmount = (int) round($subtotal * 0.21);
        $shippingAmount = $this->faker->randomElement([0, 299, 499, 999]);

        return [
            'user_id' => User::factory(),
            'status' => OrderStatus::Pending,
            'payment_status' => PaymentStatus::Unpaid,
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'shipping_amount' => $shippingAmount,
            'total' => $subtotal + $taxAmount + $shippingAmount,
            'shipping_address' => [
                'street' => $this->faker->streetAddress(),
                'city' => $this->faker->city(),
                'postal_code' => $this->faker->postcode(),
                'country' => $this->faker->countryCode(),
            ],
            'billing_address' => [
                'street' => $this->faker->streetAddress(),
                'city' => $this->faker->city(),
                'postal_code' => $this->faker->postcode(),
                'country' => $this->faker->countryCode(),
            ],
            'payment_method' => null,
            'notes' => null,
        ];
    }

    /**
     * Indicate the order is confirmed.
     */
    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Confirmed,
        ]);
    }

    /**
     * Indicate the order is processing.
     */
    public function processing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Processing,
        ]);
    }

    /**
     * Indicate the order is shipped.
     */
    public function shipped(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Shipped,
        ]);
    }

    /**
     * Indicate the order is delivered.
     */
    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Delivered,
        ]);
    }

    /**
     * Indicate the order is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Cancelled,
        ]);
    }

    /**
     * Indicate the order is paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => PaymentStatus::Paid,
            'payment_method' => $this->faker->randomElement(['credit_card', 'paypal', 'bank_transfer']),
        ]);
    }

    /**
     * Indicate the order is refunded.
     */
    public function refunded(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => PaymentStatus::Refunded,
        ]);
    }

    /**
     * Indicate the order is a guest order (no user).
     */
    public function guest(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
        ]);
    }
}
