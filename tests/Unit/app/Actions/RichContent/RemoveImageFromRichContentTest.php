<?php

use App\Actions\RichContent\RemoveImageFromRichContent;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it removes matching image tag from rich content when handled', function () {
    $user = User::factory()->create();
    $uuid = '0190a2b1-c3d4-7e8f-9a0b-1c2d3e4f5a6b';

    $post = Post::factory()->create([
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
        'content' => '<p><img src="/storage/test.jpg" alt="Test Image" data-id="'.$uuid.'"></p><p>Other content</p>',
    ]);

    expect($post->content)->toContain($uuid);

    (new RemoveImageFromRichContent)->handle($post, $uuid);

    expect($post->fresh()->content)->not->toContain($uuid)
        ->and($post->fresh()->content)->toContain('Other content');
});

test('it handles multiple attributes when removing rich content images', function () {
    $user = User::factory()->create();
    $uuid1 = '0190a2b1-c3d4-7e8f-9a0b-1c2d3e4f5a61';
    $uuid2 = '0190a2b1-c3d4-7e8f-9a0b-1c2d3e4f5a62';

    $post = Post::factory()->create([
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
        'intro' => '<p><img src="/storage/1.jpg" alt="Intro Image" data-id="'.$uuid1.'"></p>',
        'content' => '<p><img src="/storage/2.jpg" alt="Content Image" data-id="'.$uuid2.'"></p>',
    ]);

    (new RemoveImageFromRichContent)->handle($post, $uuid1, ['intro', 'content']);
    (new RemoveImageFromRichContent)->handle($post, $uuid2, ['intro', 'content']);

    expect($post->fresh()->intro)->not->toContain($uuid1)
        ->and($post->fresh()->content)->not->toContain($uuid2);
});

test('it does nothing if uuid is not found in content', function () {
    $user = User::factory()->create();
    $content = '<p>No images here</p>';

    $post = Post::factory()->create([
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
        'content' => $content,
    ]);

    (new RemoveImageFromRichContent)->handle($post, 'non-existent-uuid');

    expect($post->fresh()->content)->toBe($content);
});
