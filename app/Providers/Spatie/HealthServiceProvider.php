<?php

/** @noinspection PhpUnusedAliasInspection */

namespace App\Providers\Spatie;

use Illuminate\Support\ServiceProvider;
use Spatie\Health\Checks\Checks\BackupsCheck;
use Spatie\Health\Checks\Checks\CacheCheck;
use Spatie\Health\Checks\Checks\DatabaseCheck;
use Spatie\Health\Checks\Checks\DatabaseConnectionCountCheck;
use Spatie\Health\Checks\Checks\DatabaseSizeCheck;
use Spatie\Health\Checks\Checks\DatabaseTableSizeCheck;
use Spatie\Health\Checks\Checks\DebugModeCheck;
use Spatie\Health\Checks\Checks\EnvironmentCheck;
use Spatie\Health\Checks\Checks\FlareErrorOccurrenceCountCheck;
use Spatie\Health\Checks\Checks\HorizonCheck;
use Spatie\Health\Checks\Checks\MeilisearchCheck;
use Spatie\Health\Checks\Checks\OptimizedAppCheck;
use Spatie\Health\Checks\Checks\PingCheck;
use Spatie\Health\Checks\Checks\QueueCheck;
use Spatie\Health\Checks\Checks\RedisCheck;
use Spatie\Health\Checks\Checks\RedisMemoryUsageCheck;
use Spatie\Health\Checks\Checks\ScheduleCheck;
use Spatie\Health\Checks\Checks\UsedDiskSpaceCheck;
use Spatie\Health\Facades\Health;
use Spatie\SecurityAdvisoriesHealthCheck\SecurityAdvisoriesCheck;

class HealthServiceProvider extends ServiceProvider
{
    /**
     * Registering Spatie Health checks
     */
    public function boot(): void
    {
        Health::checks([
            OptimizedAppCheck::new()
                ->unless(app()->environment('local')),
            DebugModeCheck::new()
                ->unless(app()->environment('local')),
            EnvironmentCheck::new()
                ->unless(app()->environment('local', 'staging')),
            UsedDiskSpaceCheck::new()
                ->warnWhenUsedSpaceIsAbovePercentage(80)
                ->failWhenUsedSpaceIsAbovePercentage(95),
            BackupsCheck::new()
                ->onDisk(config('backup.backup.destination.disks')[0])
                ->locatedAt(config('backup.backup.name')),
            CacheCheck::new(),
            DatabaseCheck::new(),
            DatabaseSizeCheck::new()
                ->failWhenSizeAboveGb(errorThresholdGb: 4.0)
                ->unless(app()->environment('local')),
            // DatabaseConnectionCountCheck::new(),
            DatabaseTableSizeCheck::new(),
            // FlareErrorOccurrenceCountCheck::new(),
            // HorizonCheck::new(),
            // MeilisearchCheck::new(),
            PingCheck::new()
                ->url(config('app.url').'/up')
                ->retryTimes(3)
                ->unless(app()->environment('local')),
            QueueCheck::new()
                ->unless(app()->environment('local')),
            // RedisCheck::new(),
            // RedisMemoryUsageCheck::new(),
            ScheduleCheck::new()
                ->heartbeatMaxAgeInMinutes(61)
                ->unless(app()->environment('local')),
            SecurityAdvisoriesCheck::new(),

            // Todo: CPU Load? (https://spatie.be/docs/laravel-health/v1/available-checks/cpu-load)
        ]);
    }
}
