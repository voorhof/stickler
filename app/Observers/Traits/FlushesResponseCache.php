<?php

namespace App\Observers\Traits;

use Illuminate\Database\Eloquent\Model;

use function Illuminate\Support\defer;

use Spatie\ResponseCache\Facades\ResponseCache;

trait FlushesResponseCache
{
    public function saved(Model $model): void
    {
        $this->flushResponseCache();
    }

    public function deleted(Model $model): void
    {
        $this->flushResponseCache();
    }

    protected function flushResponseCache(): void
    {
        if (! config('responsecache.enabled')) {
            return;
        }

        defer(fn () => ResponseCache::clear(), 'flush-response-cache', always: true);
    }
}
