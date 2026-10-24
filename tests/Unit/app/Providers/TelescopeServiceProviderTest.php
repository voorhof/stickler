<?php

use App\Models\Permission;
use App\Models\User;
use App\Providers\TelescopeServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Telescope\TelescopeApplicationServiceProvider;

test('it extends telescope application service provider', function () {
    expect(new TelescopeServiceProvider(app()))->toBeInstanceOf(TelescopeApplicationServiceProvider::class);
});

test('it is registered in the application', function () {
    expect(app()->getLoadedProviders())->toHaveKey(TelescopeServiceProvider::class);
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
