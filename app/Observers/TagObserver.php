<?php

/** @noinspection PhpParameterNameChangedDuringInheritanceInspection */

namespace App\Observers;

use App\Observers\Traits\FlushesResponseCache;
use App\Observers\Traits\TracksCreatorAndUpdaterObserver;

class TagObserver
{
    use FlushesResponseCache;
    use TracksCreatorAndUpdaterObserver;
}
