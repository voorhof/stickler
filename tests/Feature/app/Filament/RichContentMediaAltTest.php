<?php

use App\Models\Media;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('saving a post with rich content syncs matching media name to image alt text', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $uuid = '0190a2b1-c3d4-7e8f-9a0b-1c2d3e4f5a6b';

    $post = Post::factory()->create([
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
        'content' => '<p><img src="/storage/test.jpg" alt="Scenic Mountain View" data-id="'.$uuid.'"></p>',
    ]);

    $media = Media::create([
        'uuid' => $uuid,
        'model_type' => $post->getMorphClass(),
        'model_id' => $post->getKey(),
        'collection_name' => 'content',
        'name' => 'original-filename.jpg',
        'file_name' => 'test.jpg',
        'mime_type' => 'image/jpeg',
        'disk' => 'public',
        'size' => 1024,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);

    expect($media->name)->toBe('original-filename.jpg');

    // Update the post to trigger the saved observer event
    $post->update([
        'title' => 'Updated Post Title with Rich Content',
    ]);

    expect($media->fresh()->name)->toBe('Scenic Mountain View');
});
