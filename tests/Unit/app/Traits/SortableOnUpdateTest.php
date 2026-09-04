<?php

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    User::factory()->create();
});

test('it updates order and reorders other records when order_column changes', function () {
    $post1 = Post::factory()->create(['order_column' => 1]);
    $post2 = Post::factory()->create(['order_column' => 2]);
    $post3 = Post::factory()->create(['order_column' => 3]);

    $post1->update(['order_column' => 3]);

    expect($post1->fresh()->order_column)->toBe(3)
        ->and($post2->fresh()->order_column)->toBe(1)
        ->and($post3->fresh()->order_column)->toBe(2);
});

test('it reorders records when a new model is created with specific order_column', function () {
    $post1 = Post::factory()->create(['order_column' => 1]);
    $post2 = Post::factory()->create(['order_column' => 2]);

    $newPost = Post::forceCreate([
        'title' => 'Content Post',
        'intro' => 'Intro text.',
        'content' => '<p>This is <strong>rich</strong> content.</p>',
        'order_column' => 1,
        'created_by_user_id' => 1,
        'updated_by_user_id' => 1,
    ]);

    expect($newPost->fresh()->order_column)->toBe(1)
        ->and($post1->fresh()->order_column)->toBe(2)
        ->and($post2->fresh()->order_column)->toBe(3);
});

test('it assigns default order when a new model is created without order_column', function () {
    $post1 = Post::factory()->create(['order_column' => 1]);
    $post2 = Post::factory()->create(['order_column' => 2]);

    $newPost = Post::forceCreate([
        'title' => 'Content Post',
        'intro' => 'Intro text.',
        'content' => '<p>This is <strong>rich</strong> content.</p>',
        'created_by_user_id' => 1,
        'updated_by_user_id' => 1,
    ]);

    expect($newPost->fresh()->order_column)->toBe(1)
        ->and($post1->fresh()->order_column)->toBe(2)
        ->and($post2->fresh()->order_column)->toBe(3);
});
