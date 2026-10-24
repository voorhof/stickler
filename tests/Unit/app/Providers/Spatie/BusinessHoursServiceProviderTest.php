<?php

use App\Providers\Spatie\BusinessHoursServiceProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use LaBoiteACode\FilamentBusinessHours\Models\BusinessHours;

test('it is a service provider', function () {
    expect(new BusinessHoursServiceProvider(app()))
        ->toBeInstanceOf(ServiceProvider::class);
});

test('it registers the observer that forgets the cache when a record is saved', function () {
    Cache::put('business-hours:default', ['exists' => true, 'definition' => []]);

    BusinessHours::query()->create([
        'key' => 'default',
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

    expect(Cache::has('business-hours:default'))->toBeFalse();
});
