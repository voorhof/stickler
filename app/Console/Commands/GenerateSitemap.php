<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Spatie\Sitemap\SitemapGenerator;

#[Signature('sitemap:generate')]
#[Description('Generate the sitemap.')]
class GenerateSitemap extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        SitemapGenerator::create(config('app.url'))
            ->getSitemap()
            ->writeToDisk('public', 'sitemap.xml', true);

        $this->info('Sitemap generated!');
    }
}
