<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Actions\Sitemap\RegenerateSitemap;
use App\Jobs\GenerateSitemapJob;
use Illuminate\Support\Facades\Queue;

it('runs sitemap regeneration when the job is handled', function (): void {
    $regenerateSitemap = Mockery::mock(RegenerateSitemap::class);
    $regenerateSitemap->shouldReceive('handle')->once();

    (new GenerateSitemapJob)->handle($regenerateSitemap);
});

it('keeps only one sitemap generation job queued at a time', function (): void {
    Queue::fake([GenerateSitemapJob::class]);

    GenerateSitemapJob::dispatch();
    GenerateSitemapJob::dispatch();

    Queue::assertPushedTimes(GenerateSitemapJob::class);
});
