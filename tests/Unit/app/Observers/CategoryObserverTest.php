<?php

use App\Models\Category;
use App\Models\User;

use function Pest\Laravel\actingAs;

it('tracks creator and updater on category create and update', function (): void {
    $creator = User::factory()->create();
    $updater = User::factory()->create();

    actingAs($creator);

    $category = Category::create([
        'title' => 'New Category',
        'slug' => 'new-category',
    ]);

    expect($category->created_by_user_id)->toBe($creator->id)
        ->and($category->updated_by_user_id)->toBe($creator->id);

    actingAs($updater);

    $category->update(['title' => 'Updated Category']);

    expect($category->fresh()->created_by_user_id)->toBe($creator->id)
        ->and($category->fresh()->updated_by_user_id)->toBe($updater->id);
});
