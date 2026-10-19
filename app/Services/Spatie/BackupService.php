<?php

namespace App\Services\Spatie;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Spatie\Backup\BackupDestination\Backup;
use Spatie\Backup\BackupDestination\BackupDestination;
use Spatie\Backup\Config\MonitoredBackupsConfig;
use Spatie\Backup\Helpers\Format;
use Spatie\Backup\Tasks\Monitor\BackupDestinationStatus;
use Spatie\Backup\Tasks\Monitor\BackupDestinationStatusFactory;

class BackupService
{
    public static function clearCache(string $disk): void
    {
        Cache::forget('backups-'.$disk);
        Cache::forget('backup-statuses');
    }

    public static function getDisks(): array
    {
        return config('backup.backup.destination.disks');
    }

    public static function getFilterDisks(): array
    {
        $result = [];
        foreach (static::getDisks() as $value) {
            $result[$value] = ucfirst($value);
        }

        return $result;
    }

    public static function getBackupDestinationData(string $disk): array
    {
        return Cache::rememberForever('backups-'.$disk, function () use ($disk) {
            return BackupDestination::create($disk, config('backup.backup.name'))
                ->backups()
                ->map(function (Backup $backup) use ($disk) {
                    return [
                        'disk' => $disk,
                        'path' => $backup->path(),
                        'date' => $backup->date()->format('Y-m-d H:i:s'),
                        'size' => Format::humanReadableSize($backup->sizeInBytes()),
                        'sizeInBytes' => $backup->sizeInBytes(),
                    ];
                })
                ->toArray();
        });
    }

    public static function getBackupDestinationStatusData(): array
    {
        return Cache::rememberForever('backup-statuses', function () {
            $config = class_exists('Spatie\Backup\Config\MonitoredBackupsConfig')
                ? MonitoredBackupsConfig::fromArray(config('backup.monitor_backups'))
                : config('backup.monitor_backups');

            return BackupDestinationStatusFactory::createForMonitorConfig($config)
                ->map(function (BackupDestinationStatus $backupDestinationStatus, int|string $key) {
                    $disk = $backupDestinationStatus->backupDestination()->diskName();
                    $backups = collect(static::getBackupDestinationData($disk));
                    $newestBackup = $backups->sortByDesc('date')->first();

                    return [
                        'id' => $key,
                        'name' => $backupDestinationStatus->backupDestination()->backupName(),
                        'disk' => $disk,
                        'reachable' => $backupDestinationStatus->backupDestination()->isReachable(),
                        'healthy' => $backupDestinationStatus->isHealthy(),
                        'amount' => $backups->count(),
                        'newest' => $newestBackup
                            ? Carbon::parse($newestBackup['date'])->diffForHumans()
                            : __('backups.no_backups_info'),
                        'usedStorage' => Format::humanReadableSize($backups->sum('sizeInBytes')),
                    ];
                })
                ->values()
                ->toArray();
        });
    }
}
