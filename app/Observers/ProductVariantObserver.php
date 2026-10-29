<?php

/** @noinspection PhpParameterNameChangedDuringInheritanceInspection */

namespace App\Observers;

use App\Models\ProductVariant;
use App\Observers\Traits\FlushesResponseCache;
use App\Observers\Traits\TracksCreatorAndUpdaterObserver;
use Exception;

class ProductVariantObserver
{
    use FlushesResponseCache;
    use TracksCreatorAndUpdaterObserver;

    /**
     * Copy each rich-content image's `alt` text into the `name`
     * column of the matching media record (matched by UUID / data-id).
     *
     * @throws Exception
     */
    public function saved(ProductVariant $product): void
    {
        $this->flushResponseCache();
    }

    public function deleted(ProductVariant $product): void
    {
        $this->flushResponseCache();
    }
}
