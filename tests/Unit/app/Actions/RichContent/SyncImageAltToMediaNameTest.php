<?php

use App\Actions\RichContent\SyncImageAltToMediaName;
use App\Models\Media;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it updates media name to match image alt text in rich content', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create([
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
        'content' => '<p><img src="/storage/test.jpg" alt="Beautiful Sunset" data-id="0190a2b1-c3d4-7e8f-9a0b-1c2d3e4f5a6b"></p>',
    ]);

    $media = Media::create([
        'uuid' => '0190a2b1-c3d4-7e8f-9a0b-1c2d3e4f5a6b',
        'model_type' => $post->getMorphClass(),
        'model_id' => $post->getKey(),
        'collection_name' => 'content',
        'name' => 'Original Filename',
        'file_name' => 'test.jpg',
        'mime_type' => 'image/jpeg',
        'disk' => 'public',
        'size' => 1024,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);

    expect($media->name)->toBe('Original Filename');

    (new SyncImageAltToMediaName)->handle($post);

    expect($media->fresh()->name)->toBe('Beautiful Sunset');
});

test('it does not update media name if it already matches alt text', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create([
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
        'content' => '<p><img src="/storage/test.jpg" alt="Already Matches" data-id="0190a2b1-c3d4-7e8f-9a0b-1c2d3e4f5a6b"></p>',
    ]);

    $media = Media::create([
        'uuid' => '0190a2b1-c3d4-7e8f-9a0b-1c2d3e4f5a6b',
        'model_type' => $post->getMorphClass(),
        'model_id' => $post->getKey(),
        'collection_name' => 'content',
        'name' => 'Already Matches',
        'file_name' => 'test.jpg',
        'mime_type' => 'image/jpeg',
        'disk' => 'public',
        'size' => 1024,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);

    (new SyncImageAltToMediaName)->handle($post);

    expect($media->fresh()->name)->toBe('Already Matches');
});

test('it handles multiple attributes when scanning rich content', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create([
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
        'intro' => '<p><img src="/storage/test.jpg" alt="Intro Alt" data-id="0190a2b1-c3d4-7e8f-9a0b-1c2d3e4f5a61"></p>',
        'content' => '<p><img src="/storage/test.jpg" alt="Content Alt" data-id="0190a2b1-c3d4-7e8f-9a0b-1c2d3e4f5a62"></p>',
    ]);

    $media1 = Media::create([
        'uuid' => '0190a2b1-c3d4-7e8f-9a0b-1c2d3e4f5a61',
        'model_type' => $post->getMorphClass(),
        'model_id' => $post->getKey(),
        'collection_name' => 'content',
        'name' => 'Old Intro Name',
        'file_name' => '1.jpg',
        'mime_type' => 'image/jpeg',
        'disk' => 'public',
        'size' => 1024,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);

    $media2 = Media::create([
        'uuid' => '0190a2b1-c3d4-7e8f-9a0b-1c2d3e4f5a62',
        'model_type' => $post->getMorphClass(),
        'model_id' => $post->getKey(),
        'collection_name' => 'content',
        'name' => 'Old Content Name',
        'file_name' => '2.jpg',
        'mime_type' => 'image/jpeg',
        'disk' => 'public',
        'size' => 1024,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);

    (new SyncImageAltToMediaName)->handle($post, ['intro', 'content']);

    expect($media1->fresh()->name)->toBe('Intro Alt')
        ->and($media2->fresh()->name)->toBe('Content Alt');
});
