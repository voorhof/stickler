<?php

/** @noinspection PhpParameterNameChangedDuringInheritanceInspection */

namespace App\Observers;

use App\Models\Tag;
use App\Observers\Traits\DispatchesSitemapGeneration;
use App\Observers\Traits\FlushesResponseCache;
use App\Observers\Traits\TracksCreatorAndUpdaterObserver;

class TagObserver
{
    use DispatchesSitemapGeneration;
    use FlushesResponseCache;
    use TracksCreatorAndUpdaterObserver;

    public function saved(Tag $tag): void
    {
        $this->flushResponseCache();
        $this->dispatchSitemapGeneration();
    }

    public function deleted(Tag $tag): void
    {
        $this->flushResponseCache();
        $this->dispatchSitemapGeneration();
    }
}
