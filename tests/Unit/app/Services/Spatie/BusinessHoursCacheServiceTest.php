<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Services\Spatie\BusinessHoursCacheService;
use Illuminate\Support\Facades\Cache;
use LaBoiteACode\FilamentBusinessHours\Models\BusinessHours;

function createBusinessHoursRecord(string $key, array $exceptions = []): BusinessHours
{
    return BusinessHours::query()->create([
        'key' => $key,
        'timezone' => null,
        'overflow' => false,
        'hours' => [
            'monday' => ['08:00-12:00'],
            'tuesday' => ['08:00-12:00'],
            'wednesday' => ['08:00-12:00'],
            'thursday' => ['08:00-12:00'],
            'friday' => ['08:00-12:00'],
            'saturday' => [],
            'sunday' => [],
        ],
        'exceptions' => $exceptions,
    ]);
}

test('it resolves the existing record definition', function () {
    createBusinessHoursRecord('default');

    $result = app(BusinessHoursCacheService::class)->resolve('default');

    expect($result['exists'])->toBeTrue()
        ->and($result['definition']['monday'])->toBe(['08:00-12:00'])
        ->and($result['definition']['sunday'])->toBe([]);
});

test('it caches a missing record as non existing', function () {
    $result = app(BusinessHoursCacheService::class)->resolve('missing');

    expect($result['exists'])->toBeFalse()
        ->and(Cache::has('business-hours:missing'))->toBeTrue();
});

test('it stores the resolved definition under a prefixed cache key', function () {
    createBusinessHoursRecord('default');

    app(BusinessHoursCacheService::class)->resolve('default');

    expect(Cache::has('business-hours:default'))->toBeTrue();
});

test('it returns the cached definition instead of querying the database again', function () {
    $record = createBusinessHoursRecord('default', ['2026-01-15' => ['hours' => [], 'data' => 'Inventory']]);
    $service = app(BusinessHoursCacheService::class);

    $service->resolve('default');

    BusinessHours::query()
        ->whereKey($record->getKey())
        ->toBase()
        ->update(['exceptions' => json_encode(['2026-01-16' => ['hours' => [], 'data' => 'Changed']])]);

    expect($service->resolve('default')['definition']['exceptions'])
        ->toHaveKey('2026-01-15')
        ->not->toHaveKey('2026-01-16');
});

test('it reads fresh data again after the cache is forgotten', function () {
    $record = createBusinessHoursRecord('default', ['2026-01-15' => ['hours' => [], 'data' => 'Inventory']]);
    $service = app(BusinessHoursCacheService::class);

    $service->resolve('default');

    BusinessHours::query()
        ->whereKey($record->getKey())
        ->toBase()
        ->update(['exceptions' => json_encode(['2026-01-16' => ['hours' => [], 'data' => 'Changed']])]);

    $service->forget('default');

    expect($service->resolve('default')['definition']['exceptions'])
        ->toHaveKey('2026-01-16');
});

test('it forgets only the cache entry of the given record', function () {
    createBusinessHoursRecord('default');
    createBusinessHoursRecord('other');
    $service = app(BusinessHoursCacheService::class);

    $service->resolve('default');
    $service->resolve('other');

    $service->forget('default');

    expect(Cache::has('business-hours:default'))->toBeFalse()
        ->and(Cache::has('business-hours:other'))->toBeTrue();
});

test('it can forget a key that is not cached without failing', function () {
    app(BusinessHoursCacheService::class)->forget('unknown');

    expect(Cache::has('business-hours:unknown'))->toBeFalse();
});
