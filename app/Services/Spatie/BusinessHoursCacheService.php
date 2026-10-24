<?php

namespace App\Services\Spatie;

use Illuminate\Support\Facades\Cache;
use LaBoiteACode\FilamentBusinessHours\Models\BusinessHours;

class BusinessHoursCacheService
{
    /**
     * The cached definition for a record, with an existence flag so a missing record is cached too.
     *
     * @return array{exists: bool, definition: array<string, mixed>}
     */
    public function resolve(string $recordKey): array
    {
        return Cache::remember(
            $this->cacheKey($recordKey),
            now()->addHour(),
            function () use ($recordKey): array {
                $businessHours = BusinessHours::resolve($recordKey);

                return [
                    'exists' => $businessHours->exists,
                    'definition' => $businessHours->toOpeningHoursArray(),
                ];
            },
        );
    }

    public function forget(string $recordKey): void
    {
        Cache::forget($this->cacheKey($recordKey));
    }

    private function cacheKey(string $recordKey): string
    {
        return 'business-hours:'.$recordKey;
    }
}
