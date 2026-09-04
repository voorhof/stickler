<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it generates sitemap successfully when authorized', function () {
    Permission::firstOrCreate(['name' => 'edit settings']);
    Permission::firstOrCreate(['name' => 'access admin']);

    $adminUser = User::factory()->create();

    $adminRole = Role::firstOrCreate(['name' => 'Admin']);
    $adminRole->givePermissionTo(['edit settings', 'access admin']);

    $adminUser->assignRole($adminRole);

    $this->actingAs($adminUser);

    $response = $this->get(route('filament.admin.sitemap.generate'));
    $response->assertStatus(302); // redirect back
});

test('it returns 403 when lacking permission to generate sitemap', function () {
    Permission::firstOrCreate(['name' => 'access admin']);

    $user = User::factory()->create();
    $adminRole = Role::firstOrCreate(['name' => 'Admin']);
    $adminRole->givePermissionTo('access admin');
    $user->assignRole($adminRole);

    $this->actingAs($user);

    $response = $this->get(route('filament.admin.sitemap.generate'));
    $response->assertStatus(403);
});
