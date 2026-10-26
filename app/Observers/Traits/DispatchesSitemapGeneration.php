<?php

namespace App\Observers\Traits;

use App\Jobs\GenerateSitemapJob;

trait DispatchesSitemapGeneration
{
    protected function dispatchSitemapGeneration(): void
    {
        GenerateSitemapJob::dispatch();
    }
}
