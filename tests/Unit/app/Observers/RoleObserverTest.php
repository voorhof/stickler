<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('sets created_by_user_id and updated_by_user_id when a role is created', function (): void {
    $user = User::factory()->create();

    actingAs($user);

    $role = Role::create([
        'name' => 'Observer Test Role',
    ]);

    expect($role->created_by_user_id)->toBe($user->id)
        ->and($role->updated_by_user_id)->toBe($user->id);
});

it('updates updated_by_user_id but not created_by_user_id when a role is updated', function (): void {
    $creator = User::factory()->create();
    $updater = User::factory()->create();

    actingAs($creator);

    $role = Role::create([
        'name' => 'Original Title',
    ]);

    expect($role->created_by_user_id)->toBe($creator->id);

    actingAs($updater);

    $role->update(['name' => 'Updated Title']);

    expect($role->fresh()->created_by_user_id)->toBe($creator->id)
        ->and($role->fresh()->updated_by_user_id)->toBe($updater->id);
});

it('does not overwrite user ids when no user is authenticated during update', function (): void {
    $creator = User::factory()->create();

    actingAs($creator);

    $role = Role::create([
        'name' => 'Auth Create No Auth Update',
    ]);

    $originalCreatorId = $role->created_by_user_id;
    $originalUpdaterId = $role->updated_by_user_id;

    auth()->logout();

    $role->update(['name' => 'Updated Without Auth']);

    expect($role->fresh()->created_by_user_id)->toBe($originalCreatorId)
        ->and($role->fresh()->updated_by_user_id)->toBe($originalUpdaterId);
});
