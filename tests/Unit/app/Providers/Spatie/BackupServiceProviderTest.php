<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Providers\Spatie\BackupServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Spatie\Backup\Events\BackupWasSuccessful;

uses(RefreshDatabase::class);

test('it extends ServiceProvider', function () {
    $provider = new BackupServiceProvider(app());

    expect($provider)->toBeInstanceOf(ServiceProvider::class);
});

test('it executes boot method without errors', function () {
    $provider = new BackupServiceProvider(app());

    $provider->boot();

    expect(true)->toBeTrue();
});

test('it is registered in the application', function () {
    expect(app()->getLoadedProviders())->toHaveKey(BackupServiceProvider::class);
});

test('it clears cache when BackupWasSuccessful event fires', function () {
    Permission::firstOrCreate(['name' => 'read backups']);
    $role = Role::firstOrCreate(['name' => 'Admin']);
    $role->givePermissionTo('read backups');

    $user = User::factory()->create();
    $user->assignRole($role);

    Cache::put('backups-backups', 'cached-data');
    Cache::put('backup-statuses', 'cached-status');

    $event = new BackupWasSuccessful('backups', config('backup.backup.name'));

    Event::dispatch($event);

    expect(Cache::has('backups-backups'))->toBeFalse()
        ->and(Cache::has('backup-statuses'))->toBeFalse();
});
