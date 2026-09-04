<?php

/** @noinspection LaravelEloquentGuardedAttributeAssignmentInspection, PhpUndefinedMethodInspection */

use App\Models\Media;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('download route is available with permissions', function () {
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

test('download route is protected without permissions', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $response = $this->get('/admin/media/1/download');
    $response->assertStatus(403);
});
