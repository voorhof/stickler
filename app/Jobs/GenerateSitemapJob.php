<?php

namespace App\Jobs;

use App\Actions\Sitemap\RegenerateSitemap;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateSitemapJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public function __construct()
    {
        $this->onConnection(config('queue.sitemap_connection'));
    }

    /**
     * Generate the sitemap and log the activity.
     */
    public function handle(RegenerateSitemap $regenerateSitemap): void
    {
        $regenerateSitemap->handle();
    }
}
