<?php

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('sets created_by_user_id and updated_by_user_id when a tag is created', function (): void {
    $user = User::factory()->create();

    actingAs($user);

    $tag = Tag::create([
        'name' => 'Observer Test Tag',
    ]);

    expect($tag->created_by_user_id)->toBe($user->id)
        ->and($tag->updated_by_user_id)->toBe($user->id);
});

it('updates updated_by_user_id but not created_by_user_id when a tag is updated', function (): void {
    $creator = User::factory()->create();
    $updater = User::factory()->create();

    actingAs($creator);

    $tag = Tag::create([
        'name' => 'Original Title',
    ]);

    expect($tag->created_by_user_id)->toBe($creator->id);

    actingAs($updater);

    $tag->update(['name' => 'Updated Title']);

    expect($tag->fresh()->created_by_user_id)->toBe($creator->id)
        ->and($tag->fresh()->updated_by_user_id)->toBe($updater->id);
});

it('does not overwrite user ids when no user is authenticated during update', function (): void {
    $creator = User::factory()->create();

    actingAs($creator);

    $tag = Tag::create([
        'name' => 'Auth Create No Auth Update',
    ]);

    $originalCreatorId = $tag->created_by_user_id;
    $originalUpdaterId = $tag->updated_by_user_id;

    auth()->logout();

    $tag->update(['name' => 'Updated Without Auth']);

    expect($tag->fresh()->created_by_user_id)->toBe($originalCreatorId)
        ->and($tag->fresh()->updated_by_user_id)->toBe($originalUpdaterId);
});
