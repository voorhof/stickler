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

    expect($adminUser->fresh()->locale)->toBe('nl_BE');
});

it('returns a 404 when trying to set an unsupported locale', function () {
    $adminUser = User::factory()->create();
    $adminUser->assignRole('Admin');

    $this->actingAs($adminUser)
        ->get(route('filament.admin.locale.update', ['locale' => 'fr']))
        ->assertNotFound();
});
