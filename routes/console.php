<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Spatie\Health\Commands\DispatchQueueCheckJobsCommand;
use Spatie\Health\Commands\RunHealthChecksCommand;
use Spatie\Health\Commands\ScheduleCheckHeartbeatCommand;
use Spatie\Health\Models\HealthCheckResultHistoryItem;

/**
 * Console Command
 *
 * These are the console commands for the application.
 * They are tested in the tests/Feature/ConsoleTest.php file.
 */

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * Clear expired password reset tokens
 */
Schedule::command('auth:clear-resets')
    ->hourlyAt(0);

/**
 * Telescope Data Pruning
 * By default, all entries older than 24 hours will be pruned.
 */
Schedule::command('telescope:prune')
    ->daily()->at('05:59');

/**
 * Spatie backups
 */
Schedule::command('backup:clean')
    ->daily()->at('06:00');
Schedule::command('backup:run --only-db --filename=db-'.app()->environment().'-'.date(config('backup.backup.database_dump_file_timestamp_format')).'.zip')
    ->daily()->at('06:01');
Schedule::command('backup:run --only-files --filename=files-'.app()->environment().'-'.date(config('backup.backup.database_dump_file_timestamp_format')).'.zip')
    ->weeklyOn(Illuminate\Console\Scheduling\Schedule::SUNDAY, '06:02');

/**
 * Spatie Activity log cleanup
 * 'clean_after_days' is defined in config/activitylog.php
 */
Schedule::command('activitylog:clean --force')
    ->daily()->at('06:03');

/**
 * Page view cleanup
 * Deletes page views older than the number of days given by --days (default 60).
 */
Schedule::command('page-views:clean --days=60 --force')
    ->daily()->at('06:05');

/**
 * Expired cart cleanup
 * Deletes carts that have expired.
 */
Schedule::command('carts:cleanup --force')
    ->daily()->at('06:06');

/**
 * Clear the response cache when scheduled content becomes public.
 */
Schedule::command('content:clear-scheduled-cache')
    ->hourlyAt(0)
    ->withoutOverlapping()
    ->onOneServer();

/**
 * Spatie health checks every hour.
 */
Schedule::command(RunHealthChecksCommand::class)
    ->hourlyAt(1);
// Dispatch a very light job on the queue you wish to monitor.
Schedule::command(DispatchQueueCheckJobsCommand::class)
    ->hourlyAt(0);
// Set the heartbeat for the schedule health check.
Schedule::command(ScheduleCheckHeartbeatCommand::class)
    ->hourlyAt(0);
// Prune the health check results daily and only keep history of the last 7 days (defined in config health.php)
Schedule::command('model:prune', ['--model' => [HealthCheckResultHistoryItem::class]])
    ->daily()->at('06:04');
