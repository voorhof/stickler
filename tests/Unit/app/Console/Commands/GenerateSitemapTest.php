<?php

use App\Console\Commands\GenerateSitemap;
use App\Models\Page;
use App\Models\Post;
use App\Models\Product;
use App\Models\Project;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

describe('class structure', function () {
    test('it extends command', function () {
        $command = new GenerateSitemap;

        expect($command)->toBeInstanceOf(Command::class);
    });

    test('it has correct signature and description', function () {
        $command = new GenerateSitemap;

        expect($command->getName())->toBe('sitemap:generate')
            ->and($command->getDescription())->toBe('Generate the sitemap.');
    });
});

describe('command execution', function () {
    test('it executes the command successfully and generates sitemap file', function () {
        Storage::fake('public');

        $this->artisan('sitemap:generate')
            ->expectsOutput('Sitemap generated!')
            ->assertSuccessful();

        Storage::disk('public')->assertExists('sitemap.xml');
    });

    test('it includes static routes and published models in the sitemap while excluding drafts', function () {
        Storage::fake('public');

        $user = User::factory()->create(['id' => 1]);

        $publishedPage = Page::factory()->recycle($user)->create(['published_at' => now()->subDay()]);
        $draftPage = Page::factory()->recycle($user)->create(['published_at' => null]);

        $publishedPost = Post::factory()->recycle($user)->create(['published_at' => now()->subDay()]);
        $draftPost = Post::factory()->recycle($user)->create(['published_at' => null]);

        $publishedProduct = Product::factory()->recycle($user)->create(['published_at' => now()->subDay()]);
        $draftProduct = Product::factory()->recycle($user)->create(['published_at' => null]);

        $publishedProject = Project::factory()->recycle($user)->create(['published_at' => now()->subDay()]);
        $draftProject = Project::factory()->recycle($user)->create(['published_at' => null]);

        $this->artisan('sitemap:generate')
            ->assertSuccessful();

        $content = Storage::disk('public')->get('sitemap.xml');

        expect($content)->toContain(route('welcome.index'))
            ->and($content)->toContain(route('pages.show', $publishedPage))
            ->and($content)->not->toContain(route('pages.show', $draftPage))
            ->and($content)->toContain(route('posts.show', $publishedPost))
            ->and($content)->not->toContain(route('posts.show', $draftPost))
            ->and($content)->toContain(route('products.show', $publishedProduct))
            ->and($content)->not->toContain(route('products.show', $draftProduct))
            ->and($content)->toContain(route('projects.show', $publishedProject))
            ->and($content)->not->toContain(route('projects.show', $draftProject));
    });
});
