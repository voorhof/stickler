<?php

/** @noinspection PhpParameterNameChangedDuringInheritanceInspection */

namespace App\Observers;

use App\Models\Product;
use App\Observers\Traits\DisablesActivityLogOnSecondSave;
use App\Observers\Traits\DispatchesSitemapGeneration;
use App\Observers\Traits\FlushesResponseCache;
use App\Observers\Traits\SyncsRichContentImageAltText;
use App\Observers\Traits\TracksCreatorAndUpdaterObserver;
use Exception;

class ProductObserver
{
    use DisablesActivityLogOnSecondSave;
    use DispatchesSitemapGeneration;
    use FlushesResponseCache;
    use SyncsRichContentImageAltText;
    use TracksCreatorAndUpdaterObserver;

    /**
     * Copy each rich-content image's `alt` text into the `name`
     * column of the matching media record (matched by UUID / data-id).
     *
     * @throws Exception
     */
    public function saved(Product $product): void
    {
        $this->disableActivityLogIfRecentlyCreated($product);
        $this->flushResponseCache();
        $this->dispatchSitemapGeneration();
        $this->syncImageAltToMediaName($product); // defaults to ['content']

        // e.g. a model with two rich-content fields:
        // $this->syncImageAltToMediaName($product, ['content', 'method']);
    }

    public function deleted(Product $product): void
    {
        $this->flushResponseCache();
        $this->dispatchSitemapGeneration();
    }
}
