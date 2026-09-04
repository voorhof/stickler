<?php

use App\Models\Media;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it downloads media successfully when authorized', function () {
    Permission::firstOrCreate(['name' => 'view media']);
    Permission::firstOrCreate(['name' => 'access admin']);

    $adminUser = User::factory()->create();

    $adminRole = Role::firstOrCreate(['name' => 'Admin']);
    $adminRole->givePermissionTo(['view media', 'access admin']);

    $adminUser->assignRole($adminRole);

    $this->actingAs($adminUser);

    $media = Media::create([
        'name' => 'Mountains',
        'model_type' => 'App\Models\User',
        'model_id' => 1,
        'collection_name' => 'avatar',
        'file_name' => 'user-preview-test.pdf',
        'disk' => 'media',
        'size' => 66438,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);

    $response = $this->get(route('filament.admin.resources.media.download', ['record' => $media]));
    $response->assertStatus(200);
});

test('it returns 403 when lacking permission to download media', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $media = Media::create([
        'name' => 'Mountains',
        'model_type' => 'App\Models\User',
        'model_id' => 1,
        'collection_name' => 'avatar',
        'file_name' => 'user-preview-test.pdf',
        'disk' => 'media',
        'size' => 66438,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);

    $response = $this->get(route('filament.admin.resources.media.download', ['record' => $media]));
    $response->assertStatus(403);
});

test('it returns 404 when media record does not exist', function () {
    Permission::firstOrCreate(['name' => 'view media']);
    Permission::firstOrCreate(['name' => 'access admin']);

    $adminUser = User::factory()->create();
    $adminRole = Role::firstOrCreate(['name' => 'Admin']);
    $adminRole->givePermissionTo(['view media', 'access admin']);
    $adminUser->assignRole($adminRole);
    $this->actingAs($adminUser);

    $response = $this->get('/admin/media/99999/download');
    $response->assertStatus(404);
});
