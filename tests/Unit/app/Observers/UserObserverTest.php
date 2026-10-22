<?php

use App\Models\User;

use function Pest\Laravel\actingAs;

it('tracks creator and updater on user create and update', function (): void {
    $creator = User::factory()->create();
    $updater = User::factory()->create();

    actingAs($creator);

    $user = User::create([
        'name' => 'New User',
        'email' => 'new@example.com',
        'password' => 'password',
    ]);

    expect($user->created_by_user_id)->toBe($creator->id)
        ->and($user->updated_by_user_id)->toBe($creator->id);

    actingAs($updater);

    $user->update(['name' => 'Updated User']);

    expect($user->fresh()->created_by_user_id)->toBe($creator->id)
        ->and($user->fresh()->updated_by_user_id)->toBe($updater->id);
});
