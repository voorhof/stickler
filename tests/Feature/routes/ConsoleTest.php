<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Artisan;

/**
 * Finds the scheduled event whose command contains the given fragment.
 */
function findScheduledEvent(string $commandFragment): ?object
{
    return collect(app(Schedule::class)->events())
        ->first(fn ($event) => str_contains($event->command, $commandFragment));
}

it('registers the inspire command', function () {
    expect(Artisan::all())->toHaveKey('inspire');
});

it('schedules the expected commands at the expected times', function (string $commandFragment, string $cronExpression) {
    $event = findScheduledEvent($commandFragment);

    expect($event)->not->toBeNull()
        ->and($event->expression)->toBe($cronExpression);
})->with([
    'carts cleanup daily at 06:06' => ['carts:cleanup --force', '6 6 * * *'],
    'page views cleanup daily at 06:05' => ['page-views:clean --days=60 --force', '5 6 * * *'],
    'activity log cleanup daily at 06:03' => ['activitylog:clean --force', '3 6 * * *'],
    'telescope prune daily at 05:59' => ['telescope:prune', '59 5 * * *'],
    'backup clean daily at 06:00' => ['backup:clean', '0 6 * * *'],
    'model prune daily at 06:04' => ['model:prune', '4 6 * * *'],
    'password reset cleanup hourly' => ['auth:clear-resets', '0 * * * *'],
    'scheduled content cache clear hourly' => ['content:clear-scheduled-cache', '0 * * * *'],
]);

it('schedules the carts cleanup with the force option', function () {
    $event = findScheduledEvent('carts:cleanup');

    expect($event)->not->toBeNull()
        ->and($event->command)->toContain('carts:cleanup --force');
});
