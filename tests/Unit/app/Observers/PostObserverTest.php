<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Models\Post;
use App\Observers\PostObserver;
use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('sets created_by_user_id and updated_by_user_id when a post is created', function (): void {
    $user = User::factory()->create();

    actingAs($user);

    $post = Post::create([
        'title' => 'Observer Test Post',
        'intro' => 'Testing that the observer sets the creator and updater on create.',
    ]);

    expect($post->created_by_user_id)->toBe($user->id)
        ->and($post->updated_by_user_id)->toBe($user->id);
});

it('updates updated_by_user_id but not created_by_user_id when a post is updated', function (): void {
    $creator = User::factory()->create();
    $updater = User::factory()->create();

    actingAs($creator);

    $post = Post::create([
        'title' => 'Original Title',
        'intro' => 'Original intro text for the observer update test.',
    ]);

    expect($post->created_by_user_id)->toBe($creator->id);

    actingAs($updater);

    $post->update(['title' => 'Updated Title']);

    expect($post->fresh()->created_by_user_id)->toBe($creator->id)
        ->and($post->fresh()->updated_by_user_id)->toBe($updater->id);
});

it('does not overwrite user ids when no user is authenticated during update', function (): void {
    $creator = User::factory()->create();

    actingAs($creator);

    $post = Post::create([
        'title' => 'Auth Create No Auth Update',
        'intro' => 'Testing that unauthenticated updates do not overwrite the user ids.',
    ]);

    $originalCreatorId = $post->created_by_user_id;
    $originalUpdaterId = $post->updated_by_user_id;

    auth()->logout();

    $post->update(['title' => 'Updated Without Auth']);

    expect($post->fresh()->created_by_user_id)->toBe($originalCreatorId)
        ->and($post->fresh()->updated_by_user_id)->toBe($originalUpdaterId);
});

it('syncs image alt text to media name when a post is saved with rich content', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    $uuid = '0190a2b1-c3d4-7e8f-9a0b-1c2d3e4f5a6b';

    $post = Post::create([
        'title' => 'Post with Rich Content Image',
        'intro' => 'Intro text',
        'content' => '<p><img src="/storage/test.jpg" alt="Observer Test Alt" data-id="'.$uuid.'"></p>',
    ]);

    $media = App\Models\Media::create([
        'uuid' => $uuid,
        'model_type' => $post->getMorphClass(),
        'model_id' => $post->getKey(),
        'collection_name' => 'content',
        'name' => 'original-name.jpg',
        'file_name' => 'test.jpg',
        'mime_type' => 'image/jpeg',
        'disk' => 'public',
        'size' => 1024,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);

    expect($media->name)->toBe('original-name.jpg');

    // Trigger saved event by updating post
    $post->update([
        'title' => 'Updated Post Title',
    ]);

    expect($media->fresh()->name)->toBe('Observer Test Alt');
});

it('calls syncImageAltToMediaName in post observer saved method', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    $uuid = '0190a2b1-c3d4-7e8f-9a0b-1c2d3e4f5a6b';

    $post = Post::create([
        'title' => 'Post Observer Direct Test',
        'intro' => 'Intro text',
        'content' => '<p><img src="/storage/test.jpg" alt="Direct Observer Alt" data-id="'.$uuid.'"></p>',
    ]);

    $media = App\Models\Media::create([
        'uuid' => $uuid,
        'model_type' => $post->getMorphClass(),
        'model_id' => $post->getKey(),
        'collection_name' => 'content',
        'name' => 'old-name.jpg',
        'file_name' => 'test.jpg',
        'mime_type' => 'image/jpeg',
        'disk' => 'public',
        'size' => 1024,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);

    $observer = new App\Observers\PostObserver;
    $observer->saved($post);

    expect($media->fresh()->name)->toBe('Direct Observer Alt');
});
