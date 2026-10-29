<?php

use App\Models\Media;
use App\Models\Post;
use App\Models\User;

use function Illuminate\Support\defer;
use function Pest\Laravel\actingAs;

use Spatie\ResponseCache\Facades\ResponseCache;

it('tracks creator and updater on media create and update', function (): void {
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

    expect($media->created_by_user_id)->toBe($creator->id)
        ->and($media->updated_by_user_id)->toBe($creator->id);

    actingAs($updater);

    $media->update(['name' => 'Updated Media']);

    expect($media->fresh()->created_by_user_id)->toBe($creator->id)
        ->and($media->fresh()->updated_by_user_id)->toBe($updater->id);
});

it('removes image from rich content when content media is deleted', function (): void {
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

    $media->delete();

    expect($post->fresh()->content)->not->toContain($uuid)
        ->and($post->fresh()->content)->toContain('Keep Me');
});

it('does not remove image when deleted media is not in content collection', function (): void {
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
        'collection_name' => 'default',
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

it('does nothing when content media belongs to a model without rich content', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    $media = Media::create([
        'model_type' => User::class,
        'model_id' => $user->id,
        'collection_name' => 'content',
        'name' => 'avatar.jpg',
        'file_name' => 'avatar.jpg',
        'mime_type' => 'image/jpeg',
        'disk' => 'public',
        'size' => 1024,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);

    $media->delete();

    expect(Media::find($media->id))->toBeNull()
        ->and(User::find($user->id))->not->toBeNull();
});

it('flushes response cache when media is saved', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    config(['responsecache.enabled' => true]);

    ResponseCache::shouldReceive('clear')->once();

    Media::create([
        'name' => 'Cache Flush Test Media',
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

    defer()->invoke();

    ResponseCache::shouldReceive('clear')->once();

    Media::first()->update(['name' => 'Cache Flush Updated Media']);

    defer()->invoke();
});

it('flushes response cache when media is deleted', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    $media = Media::create([
        'name' => 'Cache Flush Delete Test Media',
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

    config(['responsecache.enabled' => true]);
    ResponseCache::shouldReceive('clear')->once();

    $media->delete();

    defer()->invoke();
});
