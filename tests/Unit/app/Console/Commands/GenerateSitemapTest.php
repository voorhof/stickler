<?php

use App\Console\Commands\GenerateSitemap;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\SitemapGenerator;

test('it extends command', function () {
    $command = new GenerateSitemap;

    expect($command)->toBeInstanceOf(Command::class);
});

test('it has correct signature and description', function () {
    $command = new GenerateSitemap;

    expect($command->getName())->toBe('sitemap:generate')
        ->and($command->getDescription())->toBe('Generate the sitemap.');
});

test('it executes the command successfully and generates sitemap file', function () {
    Storage::fake('public');

    $sitemapGenerator = Mockery::mock(SitemapGenerator::class);
    $sitemapGenerator->shouldReceive('setUrl')->andReturnSelf();
    $sitemapGenerator->shouldReceive('getSitemap')->andReturn(new Sitemap);
    $this->app->instance(SitemapGenerator::class, $sitemapGenerator);

    $this->artisan('sitemap:generate')
        ->expectsOutput('Sitemap generated!')
        ->assertSuccessful();

    Storage::disk('public')->assertExists('sitemap.xml');
});
