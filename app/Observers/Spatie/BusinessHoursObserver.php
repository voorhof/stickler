<?php

namespace App\Observers\Spatie;

use App\Observers\Traits\FlushesResponseCache;
use App\Services\Spatie\BusinessHoursCacheService;
use LaBoiteACode\FilamentBusinessHours\Models\BusinessHours;

readonly class BusinessHoursObserver
{
    use FlushesResponseCache;

    public function __construct(private BusinessHoursCacheService $businessHoursCache) {}

    public function saved(BusinessHours $businessHours): void
    {
        $this->flushResponseCache();
        $this->businessHoursCache->forget($businessHours->key);

        if ($businessHours->wasChanged('key')) {
            $this->businessHoursCache->forget((string) $businessHours->getOriginal('key'));
        }
    }

    public function deleted(BusinessHours $businessHours): void
    {
        $this->flushResponseCache();
        $this->businessHoursCache->forget($businessHours->key);
    }
}
