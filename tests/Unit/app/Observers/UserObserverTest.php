<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('sets created_by_user_id and updated_by_user_id when a user is created', function (): void {
    $user = User::factory()->create();

    actingAs($user);

    $user = User::create([
        'name' => 'Name',
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    expect($user->created_by_user_id)->toBe(Auth::id())
        ->and($user->updated_by_user_id)->toBe(Auth::id());
});

it('updates updated_by_user_id but not created_by_user_id when a user is updated', function (): void {
    $creator = User::factory()->create();
    $updater = User::factory()->create();

    actingAs($creator);

    $user = User::create([
        'name' => 'Name',
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    expect($user->created_by_user_id)->toBe($creator->id);

    actingAs($updater);

    $user->update(['name' => 'Updated']);

    expect($user->fresh()->created_by_user_id)->toBe($creator->id)
        ->and($user->fresh()->updated_by_user_id)->toBe($updater->id);
});

it('does not overwrite user ids when no user is authenticated during update', function (): void {
    $creator = User::factory()->create();

    actingAs($creator);

    $user = User::create([
        'name' => 'Name',
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    $originalCreatorId = $user->created_by_user_id;
    $originalUpdaterId = $user->updated_by_user_id;

    auth()->logout();

    $user->update(['name' => 'Updated Without Auth']);

    expect($user->fresh()->created_by_user_id)->toBe($originalCreatorId)
        ->and($user->fresh()->updated_by_user_id)->toBe($originalUpdaterId);
});
