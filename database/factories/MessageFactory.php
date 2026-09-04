<?php

namespace Database\Factories;

use App\Models\Message;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Message>
 */
class MessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subject = $this->faker->unique()->sentence();

        return [
            'slug' => Str::limit(Str::slug($subject, '-', 'nl'), 48, ''),
            'name' => $this->faker->name(),
            'email' => $this->faker->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'subject' => $subject,
            'message' => $this->faker->text(),
            'read' => 0,
            'replied' => 0,
            'reply' => null,
            'replied_at' => null,
            'created_by_user_id' => null,
            'updated_by_user_id' => null,
        ];
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

    /**
     * Indicate that the model is archived.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'archived_at' => now(),
        ]);
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Message $model) {
            $model->order_column = $model->id;
            $model->saveQuietly();
        });
    }
}
