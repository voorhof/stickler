<?php

use App\Services\Spatie\BackupService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumAgeInDays;
use Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumStorageInMegabytes;

beforeEach(function () {
    Cache::flush();
});

test('getDisks returns the disks configured for backup destination', function () {
    config()->set('backup.backup.destination.disks', ['backups', 'local']);

    expect(BackupService::getDisks())->toBe(['backups', 'local']);
});

test('getFilterDisks returns disks mapped to their ucfirst label', function () {
    config()->set('backup.backup.destination.disks', ['backups', 'local']);

    expect(BackupService::getFilterDisks())->toBe([
        'backups' => 'Backups',
        'local' => 'Local',
    ]);
});

test('getBackupDestinationData returns an empty array when no backups exist', function () {
    Storage::fake('backups');

    expect(BackupService::getBackupDestinationData('backups'))->toBe([]);
});

test('getBackupDestinationData returns the backup files stored on the given disk', function () {
    Storage::fake('backups');

    $backupName = config('backup.backup.name');

    Storage::disk('backups')->put("$backupName/backup.zip", 'backup-content');

    $data = BackupService::getBackupDestinationData('backups');

    expect($data)->toHaveCount(1)
        ->and($data[0]['disk'])->toBe('backups')
        ->and($data[0]['path'])->toBe("$backupName/backup.zip")
        ->and($data[0])->toHaveKeys(['disk', 'path', 'date', 'size'])
        ->and($data[0]['size'])->toBeString();
});

test('getBackupDestinationData caches the result for the given disk', function () {
    Storage::fake('backups');

    $backupName = config('backup.backup.name');

    Storage::disk('backups')->put("$backupName/backup.zip", 'backup-content');

    $firstResult = BackupService::getBackupDestinationData('backups');

    Storage::disk('backups')->put("$backupName/backup-2.zip", 'backup-content');

    $cachedResult = BackupService::getBackupDestinationData('backups');

    expect($cachedResult)->toBe($firstResult)
        ->and($cachedResult)->toHaveCount(1);
});

test('getBackupDestinationStatusData reports a healthy, reachable backup destination', function () {
    Storage::fake('backups');

    $backupName = config('backup.backup.name');

    Storage::disk('backups')->put("$backupName/backup.zip", 'backup-content');

    config()->set('backup.monitor_backups', [
        [
            'name' => $backupName,
            'disks' => ['backups'],
            'health_checks' => [
                MaximumAgeInDays::class => 1,
                MaximumStorageInMegabytes::class => 5000,
            ],
        ],
    ]);

    $data = BackupService::getBackupDestinationStatusData();

    expect($data)->toHaveCount(1);

    $status = $data[0];

    expect($status['name'])->toBe($backupName)
        ->and($status['disk'])->toBe('backups')
        ->and($status['reachable'])->toBeTrue()
        ->and($status['healthy'])->toBeTrue()
        ->and($status['amount'])->toBe(1)
        ->and($status['newest'])->toBeString()
        ->and($status['newest'])->not->toBe('No backups present')
        ->and($status['usedStorage'])->toBeString();
});

test('getBackupDestinationStatusData reports no backups present when the destination is empty', function () {
    Storage::fake('backups');

    $backupName = config('backup.backup.name');

    config()->set('backup.monitor_backups', [
        [
            'name' => $backupName,
            'disks' => ['backups'],
            'health_checks' => [
                MaximumAgeInDays::class => 1,
                MaximumStorageInMegabytes::class => 5000,
            ],
        ],
    ]);

    $data = BackupService::getBackupDestinationStatusData();

    expect($data)->toHaveCount(1)
        ->and($data[0]['amount'])->toBe(0)
        ->and($data[0]['newest'])->toBe(__('backups.no_backups_info'));
});

test('getBackupDestinationStatusData caches the result', function () {
    Storage::fake('backups');

    $backupName = config('backup.backup.name');

    config()->set('backup.monitor_backups', [
        [
            'name' => $backupName,
            'disks' => ['backups'],
            'health_checks' => [
                MaximumAgeInDays::class => 1,
                MaximumStorageInMegabytes::class => 5000,
            ],
        ],
    ]);

    $firstResult = BackupService::getBackupDestinationStatusData();

    Storage::disk('backups')->put("$backupName/backup.zip", 'backup-content');

    $cachedResult = BackupService::getBackupDestinationStatusData();

    expect($cachedResult)->toBe($firstResult)
        ->and($cachedResult[0]['amount'])->toBe(0);
});

test('clearCache clears cache for disk and status', function () {
    Storage::fake('backups');
    $backupName = config('backup.backup.name');
    Storage::disk('backups')->put("$backupName/backup.zip", 'backup-content');

    $firstResult = BackupService::getBackupDestinationData('backups');

    Storage::disk('backups')->delete("$backupName/backup.zip");

    BackupService::clearCache('backups');

    $clearedResult = BackupService::getBackupDestinationData('backups');

    expect($clearedResult)->not->toBe($firstResult)
        ->and($clearedResult)->toBeEmpty();
});
