<?php

namespace Database\Factories;

use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Page>
 */
class PageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->unique()->sentence();

        return [
            'slug' => Str::limit(Str::slug($title, '-', 'nl'), 48, ''),
            'title' => $title,
            'intro' => $this->faker->realText(254),
            'content' => null,
            'published_at' => now(),
            'created_by_user_id' => 1,
            'updated_by_user_id' => 1,
        ];
    }

    /**
     * Indicate that the model is not published.
     */
    public function notPublished(): static
    {
        return $this->state(fn (array $attributes) => [
            'published_at' => null,
        ]);
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
        return $this->afterCreating(function (Page $model) {
            $model->order_column = $model->id;
            $model->saveQuietly();
        });
    }
}
