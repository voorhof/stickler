<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->unique()->word();

        return [
            'slug' => Str::limit(Str::slug($title, '-', 'nl'), 44, ''),
            'title' => $title,
            'for_pages' => $this->faker->boolean(),
            'for_posts' => $this->faker->boolean(),
            'for_products' => $this->faker->boolean(100),
            'for_projects' => $this->faker->boolean(),
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
        return $this->afterCreating(function (Category $model) {
            $model->order_column = $model->id;
            $model->saveQuietly();
        });
    }
}
