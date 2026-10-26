<?php

use App\Models\Permission;
use App\Models\User;
use App\Providers\TelescopeServiceProvider;
use Illuminate\Foundation\PackageManifest;
use Illuminate\Support\Facades\Gate;
use Laravel\Telescope\TelescopeApplicationServiceProvider;
use Laravel\Telescope\TelescopeServiceProvider as LaravelTelescopeServiceProvider;

test('it extends telescope application service provider', function () {
    expect(new TelescopeServiceProvider(app()))->toBeInstanceOf(TelescopeApplicationServiceProvider::class);
});

test('it is registered in the application', function () {
    expect(app()->getLoadedProviders())->toHaveKey(TelescopeServiceProvider::class)
        ->and(app()->getLoadedProviders())->toHaveKey(LaravelTelescopeServiceProvider::class);
});

test('it is not registered unconditionally at bootstrap', function () {
    expect(require base_path('bootstrap/providers.php'))->not->toContain(TelescopeServiceProvider::class);
});

test('its package provider is not auto discovered', function () {
    expect(app(PackageManifest::class)->providers())->not->toContain(LaravelTelescopeServiceProvider::class);
});

test('viewTelescope gate requires access telescope permission', function () {
    new TelescopeServiceProvider(app())->boot();

    Permission::firstOrCreate(['name' => 'access telescope']);

    $userWithoutPermission = User::factory()->create();
    $userWithPermission = User::factory()->create();
    $userWithPermission->givePermissionTo('access telescope');

    expect(Gate::forUser($userWithoutPermission)->allows('viewTelescope'))->toBeFalse()
        ->and(Gate::forUser($userWithPermission)->allows('viewTelescope'))->toBeTrue();
});
