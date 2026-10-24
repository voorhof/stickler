<?php

use App\Services\Spatie\UpcomingBusinessHoursExceptionsService;
use Carbon\CarbonImmutable;

test('it returns no exceptions when none are configured', function () {
    $today = CarbonImmutable::parse('2026-01-10 10:00:00', 'Europe/Brussels');

    expect(app(UpcomingBusinessHoursExceptionsService::class)->resolve([], $today))->toBe([]);
});

test('it includes only exceptions within the requested number of weeks', function () {
    $today = CarbonImmutable::parse('2026-01-10 10:00:00', 'Europe/Brussels');
    $exceptions = [
        '2026-01-12' => ['hours' => [], 'data' => 'Inside window'],
        '2026-02-20' => ['hours' => [], 'data' => 'Outside window'],
    ];

    $twoWeeks = app(UpcomingBusinessHoursExceptionsService::class)->resolve($exceptions, $today);
    $fiveWeeks = app(UpcomingBusinessHoursExceptionsService::class)->resolve($exceptions, $today, 6);

    expect($twoWeeks)->toHaveCount(1)
        ->and($twoWeeks[0]['label'])->toBe('Inside window')
        ->and($fiveWeeks)->toHaveCount(2);
});
