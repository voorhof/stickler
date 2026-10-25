<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Actions\Sitemap\RegenerateSitemap;
use App\Models\Activity;
use Illuminate\Support\Facades\Storage;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\SitemapGenerator;

test('it generates the sitemap file and logs the activity', function () {
    Storage::fake('public');

    $sitemapGenerator = Mockery::mock(SitemapGenerator::class);
    $sitemapGenerator->shouldReceive('setUrl')->andReturnSelf();
    $sitemapGenerator->shouldReceive('getSitemap')->andReturn(new Sitemap);
    $this->app->instance(SitemapGenerator::class, $sitemapGenerator);

    app(RegenerateSitemap::class)->handle();

    Storage::disk('public')->assertExists('sitemap.xml');
    expect(Activity::where('description', 'Sitemap generated')->where('event', 'updated')->exists())->toBeTrue();
});

test('it does not log the activity when sitemap generation fails', function () {
    $sitemapGenerator = Mockery::mock(SitemapGenerator::class);
    $sitemapGenerator->shouldReceive('setUrl')->andThrow(new RuntimeException('Crawl failed'));
    $this->app->instance(SitemapGenerator::class, $sitemapGenerator);

    expect(fn () => app(RegenerateSitemap::class)->handle())->toThrow(RuntimeException::class, 'Crawl failed')
        ->and(Activity::where('description', 'Sitemap generated')->exists())->toBeFalse();
});
