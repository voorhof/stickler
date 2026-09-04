<?php

namespace Database\Factories;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Tag>
 */
class TagFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->word();
        $slug = Str::limit(Str::slug($name, '-', 'nl'), 48, '');

        return [
            'name' => $this->translations(['nl_BE', 'en_US'], $name),
            'slug' => $this->translations(['nl_BE', 'en_US'], $slug),
            'url_slug' => $slug,
            'created_by_user_id' => 1,
            'updated_by_user_id' => 1,
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
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Tag $model) {
            $model->order_column = $model->id;
            $model->saveQuietly();
        });
    }
}
