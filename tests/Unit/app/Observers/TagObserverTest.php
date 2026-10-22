<?php

use App\Models\Tag;
use App\Models\User;

use function Pest\Laravel\actingAs;

it('tracks creator and updater on tag create and update', function (): void {
    $creator = User::factory()->create();
    $updater = User::factory()->create();

    actingAs($creator);

    $tag = Tag::create([
        'name' => 'Observer Test Tag',
    ]);

    expect($tag->created_by_user_id)->toBe($creator->id)
        ->and($tag->updated_by_user_id)->toBe($creator->id);

    actingAs($updater);

    $tag->update(['name' => 'Updated Tag']);

    expect($tag->fresh()->created_by_user_id)->toBe($creator->id)
        ->and($tag->fresh()->updated_by_user_id)->toBe($updater->id);
});
