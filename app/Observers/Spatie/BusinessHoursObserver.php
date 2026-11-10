<?php

namespace App\Observers\Spatie;

use App\Observers\Traits\FlushesResponseCache;
use App\Services\Spatie\BusinessHoursCacheService;
use LaBoiteACode\FilamentBusinessHours\Models\BusinessHours;

readonly class BusinessHoursObserver
{
    use FlushesResponseCache;

    public function __construct(private BusinessHoursCacheService $businessHoursCache) {}

    public function saved(BusinessHours $model): void
    {
        $this->flushResponseCache();
        $this->businessHoursCache->forget($model->key);

        if ($model->wasChanged('key')) {
            $this->businessHoursCache->forget((string) $model->getOriginal('key'));
        }
    }

    public function deleted(BusinessHours $model): void
    {
        $this->flushResponseCache();
        $this->businessHoursCache->forget($model->key);
    }
}
