<?php

use App\Models\Permission;
use App\Models\User;
use App\Providers\TelescopeServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Laravel\Telescope\TelescopeApplicationServiceProvider;

uses(RefreshDatabase::class);

test('it extends TelescopeApplicationServiceProvider', function () {
    $provider = new TelescopeServiceProvider(app());

    expect($provider)->toBeInstanceOf(TelescopeApplicationServiceProvider::class);
});

test('it executes register and boot methods without errors', function () {
    $provider = new TelescopeServiceProvider(app());

    $provider->register();
    $provider->boot();

    expect(true)->toBeTrue();
});

test('it is registered in the application', function () {
    expect(app()->getLoadedProviders())->toHaveKey(TelescopeServiceProvider::class);
});

test('viewTelescope gate requires access telescope permission', function () {
    $provider = new TelescopeServiceProvider(app());
    $provider->boot();

    Permission::firstOrCreate(['name' => 'access telescope']);

    $userWithoutPermission = User::factory()->create();
    $userWithPermission = User::factory()->create();
    $userWithPermission->givePermissionTo('access telescope');

    expect(Gate::forUser($userWithoutPermission)->allows('viewTelescope'))->toBeFalse()
        ->and(Gate::forUser($userWithPermission)->allows('viewTelescope'))->toBeTrue();
});
