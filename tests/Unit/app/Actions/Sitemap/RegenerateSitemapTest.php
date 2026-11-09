<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Actions\Sitemap\RegenerateSitemap;
use App\Models\Activity;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

test('it generates the sitemap file and logs the activity', function () {
    Storage::fake('public');

    app(RegenerateSitemap::class)->handle();

    Storage::disk('public')->assertExists('sitemap.xml');
    expect(Activity::where('description', 'Sitemap generated')->where('event', 'updated')->exists())->toBeTrue();
});

test('it does not log the activity when sitemap generation fails', function () {
    Artisan::shouldReceive('call')
        ->once()
        ->with('sitemap:generate')
        ->andThrow(new RuntimeException('Command failed'));

    expect(fn () => app(RegenerateSitemap::class)->handle())->toThrow(RuntimeException::class, 'Command failed')
        ->and(Activity::where('description', 'Sitemap generated')->exists())->toBeFalse();
});
