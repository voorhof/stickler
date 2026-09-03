<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Providers\Spatie\AccessServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

uses(RefreshDatabase::class);

test('it extends ServiceProvider', function () {
    $provider = new AccessServiceProvider(app());

    expect($provider)->toBeInstanceOf(ServiceProvider::class);
});

test('it executes boot method without errors', function () {
    $provider = new AccessServiceProvider(app());

    $provider->boot();

    expect(true)->toBeTrue();
});

test('it is registered in the application', function () {
    expect(app()->getLoadedProviders())->toHaveKey(AccessServiceProvider::class);
});

test('super admin role is implicitly granted all permissions via gate before', function () {
    $provider = new AccessServiceProvider(app());
    $provider->boot();

    Role::firstOrCreate(['name' => 'Super Admin']);
    Role::firstOrCreate(['name' => 'Regular Role']);

    $superAdminUser = User::factory()->create();
    $superAdminUser->assignRole('Super Admin');

    $regularUser = User::factory()->create();
    $regularUser->assignRole('Regular Role');

    expect(Gate::forUser($superAdminUser)->allows('non-existent-permission'))->toBeTrue()
        ->and(Gate::forUser($regularUser)->allows('non-existent-permission'))->toBeFalse();

});

test('viewLogViewer gate requires access logs permission', function () {
    $provider = new AccessServiceProvider(app());
    $provider->boot();

    Permission::firstOrCreate(['name' => 'access logs']);

    $userWithoutPermission = User::factory()->create();
    $userWithPermission = User::factory()->create();
    $userWithPermission->givePermissionTo('access logs');

    expect(Gate::forUser($userWithoutPermission)->allows('viewLogViewer'))->toBeFalse()
        ->and(Gate::forUser($userWithPermission)->allows('viewLogViewer'))->toBeTrue();
});

test('downloadLogFile gate requires download logs permission', function () {
    $provider = new AccessServiceProvider(app());
    $provider->boot();

    Permission::firstOrCreate(['name' => 'download logs']);

    $userWithoutPermission = User::factory()->create();
    $userWithPermission = User::factory()->create();
    $userWithPermission->givePermissionTo('download logs');

    expect(Gate::forUser($userWithoutPermission)->allows('downloadLogFile'))->toBeFalse()
        ->and(Gate::forUser($userWithPermission)->allows('downloadLogFile'))->toBeTrue();
});

test('deleteLogFile gate requires delete logs permission', function () {
    $provider = new AccessServiceProvider(app());
    $provider->boot();

    Permission::firstOrCreate(['name' => 'delete logs']);

    $userWithoutPermission = User::factory()->create();
    $userWithPermission = User::factory()->create();
    $userWithPermission->givePermissionTo('delete logs');

    expect(Gate::forUser($userWithoutPermission)->allows('deleteLogFile'))->toBeFalse()
        ->and(Gate::forUser($userWithPermission)->allows('deleteLogFile'))->toBeTrue();
});
