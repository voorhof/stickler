<?php

use App\Models\Permission;
use App\Models\User;

use function Pest\Laravel\actingAs;

it('tracks creator and updater on permission create and update', function (): void {
    $creator = User::factory()->create();
    $updater = User::factory()->create();

    actingAs($creator);

    $permission = Permission::create([
        'name' => 'Observer Test Permission',
    ]);

    expect($permission->created_by_user_id)->toBe($creator->id)
        ->and($permission->updated_by_user_id)->toBe($creator->id);

    actingAs($updater);

    $permission->update(['name' => 'Updated Permission']);

    expect($permission->fresh()->created_by_user_id)->toBe($creator->id)
        ->and($permission->fresh()->updated_by_user_id)->toBe($updater->id);
});
