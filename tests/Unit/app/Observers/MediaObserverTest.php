<?php

use App\Models\Media;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('sets created_by_user_id and updated_by_user_id when a media is created', function (): void {
    $user = User::factory()->create();

    actingAs($user);

    $media = Media::create([
        'name' => 'Observer Test Media',
        'model_type' => 'App\Models\Media',
        'model_id' => 1,
        'collection_name' => 'default',
        'file_name' => 'test.jpg',
        'disk' => 'public',
        'size' => 1024,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);

    expect($media->created_by_user_id)->toBe($user->id)
        ->and($media->updated_by_user_id)->toBe($user->id);
});

it('updates updated_by_user_id but not created_by_user_id when a media is updated', function (): void {
    $creator = User::factory()->create();
    $updater = User::factory()->create();

    actingAs($creator);

    $media = Media::create([
        'name' => 'Observer Test Media',
        'model_type' => 'App\Models\Media',
        'model_id' => 1,
        'collection_name' => 'default',
        'file_name' => 'test.jpg',
        'disk' => 'public',
        'size' => 1024,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);

    expect($media->created_by_user_id)->toBe($creator->id);

    actingAs($updater);

    $media->update(['name' => 'Updated Title']);

    expect($media->fresh()->created_by_user_id)->toBe($creator->id)
        ->and($media->fresh()->updated_by_user_id)->toBe($updater->id);
});

it('does not overwrite user ids when no user is authenticated during update', function (): void {
    $creator = User::factory()->create();

    actingAs($creator);

    $media = Media::create([
        'name' => 'Observer Test Media',
        'model_type' => 'App\Models\Media',
        'model_id' => 1,
        'collection_name' => 'default',
        'file_name' => 'test.jpg',
        'disk' => 'public',
        'size' => 1024,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);

    $originalCreatorId = $media->created_by_user_id;
    $originalUpdaterId = $media->updated_by_user_id;

    auth()->logout();

    $media->update(['name' => 'Updated Without Auth']);

    expect($media->fresh()->created_by_user_id)->toBe($originalCreatorId)
        ->and($media->fresh()->updated_by_user_id)->toBe($originalUpdaterId);
});

it('removes image from post rich content when media in content collection is deleted', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    $uuid = '0190a2b1-c3d4-7e8f-9a0b-1c2d3e4f5a6b';

    $post = Post::create([
        'title' => 'Post with Image to Delete',
        'intro' => 'Intro text',
        'content' => '<p><img src="/storage/test.jpg" alt="Delete Me" data-id="'.$uuid.'"></p><p>Keep Me</p>',
    ]);

    $media = Media::create([
        'uuid' => $uuid,
        'model_type' => $post->getMorphClass(),
        'model_id' => $post->getKey(),
        'collection_name' => 'content',
        'name' => 'test.jpg',
        'file_name' => 'test.jpg',
        'mime_type' => 'image/jpeg',
        'disk' => 'public',
        'size' => 1024,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);

    expect($post->fresh()->content)->toContain($uuid);

    // Delete the media record, which triggers MediaObserver::deleted
    $media->delete();

    expect($post->fresh()->content)->not->toContain($uuid)
        ->and($post->fresh()->content)->toContain('Keep Me');
});

it('does not remove image from rich content when deleted media is not in content collection', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    $uuid = '0190a2b1-c3d4-7e8f-9a0b-1c2d3e4f5a6b';

    $content = '<p><img src="/storage/test.jpg" alt="Do Not Delete" data-id="'.$uuid.'"></p>';

    $post = Post::create([
        'title' => 'Post with Other Collection Media',
        'intro' => 'Intro text',
        'content' => $content,
    ]);

    $media = Media::create([
        'uuid' => $uuid,
        'model_type' => $post->getMorphClass(),
        'model_id' => $post->getKey(),
        'collection_name' => 'default', // Not 'content'
        'name' => 'test.jpg',
        'file_name' => 'test.jpg',
        'mime_type' => 'image/jpeg',
        'disk' => 'public',
        'size' => 1024,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);

    $media->delete();

    expect($post->fresh()->content)->toBe($content);
});
