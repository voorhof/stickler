<?php

namespace Database\Factories;

use App\Models\PageView;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PageView>
 */
class PageViewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'path' => '/'.fake()->slug(),
            'route_name' => 'pages.show',
            'referrer_host' => fake()->optional()->domainName(),
            'visitor_hash' => hash('sha256', fake()->uuid()),
            'is_bot' => false,
            'viewed_on' => now()->toDateString(),
        ];
    }

    /**
     * Mark the page view as coming from a crawler or bot.
     */
    public function bot(): static
    {
        return $this->state(fn (): array => [
            'is_bot' => true,
        ]);
    }
}
