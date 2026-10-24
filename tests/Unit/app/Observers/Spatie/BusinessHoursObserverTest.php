<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Observers\Spatie\BusinessHoursObserver;
use App\Services\Spatie\BusinessHoursCacheService;
use LaBoiteACode\FilamentBusinessHours\Models\BusinessHours;

function createBusinessHoursRecordForObserver(string $key): BusinessHours
{
    return BusinessHours::query()->create([
        'key' => $key,
        'timezone' => null,
        'overflow' => false,
        'hours' => [
            'monday' => ['08:00-12:00'],
            'tuesday' => [],
            'wednesday' => [],
            'thursday' => [],
            'friday' => [],
            'saturday' => [],
            'sunday' => [],
        ],
        'exceptions' => [],
    ]);
}

test('it forgets the cached definition when a record is saved', function () {
    $record = createBusinessHoursRecordForObserver('default');

    $cache = Mockery::mock(BusinessHoursCacheService::class);
    $cache->shouldReceive('forget')->with('default')->once();

    new BusinessHoursObserver($cache)->saved($record);
});

test('it forgets the original key as well when the key was renamed', function () {
    $record = createBusinessHoursRecordForObserver('old');
    $record->key = 'new';
    $record->syncChanges();

    $cache = Mockery::mock(BusinessHoursCacheService::class);
    $cache->shouldReceive('forget')->with('new')->once();
    $cache->shouldReceive('forget')->with('old')->once();

    new BusinessHoursObserver($cache)->saved($record);
});

test('it forgets the cached definition when a record is deleted', function () {
    $record = createBusinessHoursRecordForObserver('default');

    $cache = Mockery::mock(BusinessHoursCacheService::class);
    $cache->shouldReceive('forget')->with('default')->once();

    new BusinessHoursObserver($cache)->deleted($record);
});
