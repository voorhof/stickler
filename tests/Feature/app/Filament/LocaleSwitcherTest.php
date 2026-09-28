<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    Filament::bootCurrentPanel();

    User::factory()->create();
    $adminRole = Role::factory()->create(['name' => 'Admin']);
    $accessAdminPermission = Permission::create(['name' => 'access admin']);
    $adminRole->givePermissionTo($accessAdminPermission);
});

it('persists the selected locale on the authenticated user', function () {
    $adminUser = User::factory()->create([
        'locale' => 'en_US',
    ]);
    $adminUser->assignRole('Admin');

    $response = $this->actingAs($adminUser)->get(route('filament.admin.locale.update', [
        'locale' => 'nl_BE',
    ]));

    $response->assertRedirect();

    expect($adminUser->fresh()->locale)->toBe('nl_BE')
        ->and(app()->getLocale())->toBe('nl');
});

it('can update locale to nl_NL', function () {
    $adminUser = User::factory()->create([
        'locale' => 'en_US',
    ]);
    $adminUser->assignRole('Admin');

    $response = $this->actingAs($adminUser)->get(route('filament.admin.locale.update', [
        'locale' => 'nl_NL',
    ]));

    $response->assertRedirect();

    expect($adminUser->fresh()->locale)->toBe('nl_NL')
        ->and(app()->getLocale())->toBe('nl');
});

it('can update locale to en_UK', function () {
    $adminUser = User::factory()->create([
        'locale' => 'nl_BE',
    ]);
    $adminUser->assignRole('Admin');

    $response = $this->actingAs($adminUser)->get(route('filament.admin.locale.update', [
        'locale' => 'en_UK',
    ]));

    $response->assertRedirect();

    expect($adminUser->fresh()->locale)->toBe('en_UK')
        ->and(app()->getLocale())->toBe('en');
});

it('returns a 404 when trying to set an unsupported locale', function () {
    $adminUser = User::factory()->create();
    $adminUser->assignRole('Admin');

    $this->actingAs($adminUser)
        ->get(route('filament.admin.locale.update', ['locale' => 'fr']))
        ->assertNotFound();
});
