<?php

namespace App\Actions\Sitemap;

use Illuminate\Support\Facades\Artisan;

class RegenerateSitemap
{
    /**
     * Regenerate the sitemap file and log the activity.
     */
    public function handle(): void
    {
        Artisan::call('sitemap:generate');

        activity()
            ->event('updated')
            ->log('Sitemap generated');
    }
}
