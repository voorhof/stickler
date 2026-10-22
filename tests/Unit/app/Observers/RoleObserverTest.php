<?php

use App\Models\Role;
use App\Models\User;

use function Pest\Laravel\actingAs;

it('tracks creator and updater on role create and update', function (): void {
    $creator = User::factory()->create();
    $updater = User::factory()->create();

    actingAs($creator);

    $role = Role::create([
        'name' => 'Observer Test Role',
    ]);

    expect($role->created_by_user_id)->toBe($creator->id)
        ->and($role->updated_by_user_id)->toBe($creator->id);

    actingAs($updater);

    $role->update(['name' => 'Updated Role']);

    expect($role->fresh()->created_by_user_id)->toBe($creator->id)
        ->and($role->fresh()->updated_by_user_id)->toBe($updater->id);
});
