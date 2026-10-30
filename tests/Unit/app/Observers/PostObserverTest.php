<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Jobs\GenerateSitemapJob;
use App\Models\Media;
use App\Models\Post;
use App\Models\User;
use App\Observers\PostObserver;
use Illuminate\Database\Eloquent\Model;

use function Illuminate\Support\defer;

use Illuminate\Support\Facades\Queue;

use function Pest\Laravel\actingAs;

use Spatie\ResponseCache\Facades\ResponseCache;

it('tracks creator and updater on post create and update', function (): void {
    $creator = User::factory()->create();
    $updater = User::factory()->create();

    actingAs($creator);

    $post = Post::create([
        'title' => 'Observer Test Post',
        'intro' => 'Testing observer tracking.',
    ]);

    expect($post->created_by_user_id)->toBe($creator->id)
        ->and($post->updated_by_user_id)->toBe($creator->id);

    actingAs($updater);

    $post->update(['title' => 'Updated Title']);

    expect($post->fresh()->created_by_user_id)->toBe($creator->id)
        ->and($post->fresh()->updated_by_user_id)->toBe($updater->id);
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

    $media = Media::create([
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

it('calls syncImageAltToMediaName directly via observer saved method', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    $uuid = '0190a2b1-c3d4-7e8f-9a0b-1c2d3e4f5a6b';

    $post = Post::create([
        'title' => 'Post Observer Direct Test',
        'intro' => 'Intro text',
        'content' => '<p><img src="/storage/test.jpg" alt="Direct Observer Alt" data-id="'.$uuid.'"></p>',
    ]);

    $media = Media::create([
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

    $observer = new PostObserver;
    $observer->saved($post);

    expect($media->fresh()->name)->toBe('Direct Observer Alt');
});

it('queues sitemap generation when a post is created', function (): void {
    $user = User::factory()->create();
    Queue::fake([GenerateSitemapJob::class]);
    actingAs($user);

    Post::create([
        'title' => 'Sitemap Observer Test Post',
        'intro' => 'Testing sitemap generation.',
    ]);

    Queue::assertPushed(GenerateSitemapJob::class);
});

it('queues sitemap generation when a post is deleted', function (): void {
    $user = User::factory()->create();
    $post = Model::withoutEvents(fn (): Post => Post::factory()->create([
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]));
    Queue::fake([GenerateSitemapJob::class]);

    $post->delete();

    Queue::assertPushed(GenerateSitemapJob::class);
});

it('flushes response cache when a post is saved', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    config(['responsecache.enabled' => true]);

    ResponseCache::shouldReceive('clear')->once();

    Post::create([
        'title' => 'Cache Flush Test Post',
        'intro' => 'Testing cache flush on save.',
    ]);

    defer()->invoke();

    ResponseCache::shouldReceive('clear')->once();

    Post::first()->update(['title' => 'Cache Flush Updated Post']);

    defer()->invoke();
});

it('flushes response cache when a post is deleted', function (): void {
    $user = User::factory()->create();
    $post = Model::withoutEvents(fn (): Post => Post::factory()->create([
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]));

    config(['responsecache.enabled' => true]);
    ResponseCache::shouldReceive('clear')->once();

    $post->delete();

    defer()->invoke();
});

it('disables activity logging on second save for newly created posts', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::create([
        'title' => 'Activity Log Test Post',
        'intro' => 'Testing activity log suppression.',
    ]);

    $observer = new PostObserver;

    // Simulate a newly created post
    $post->wasRecentlyCreated = true;

    // The observer should call disableLogging on the model
    $observer->saved($post);

    // Logging is disabled then re-enabled, so it ends up true
    expect($post->enableLoggingModelsEvents)->toBeTrue();
});

it('does not disable activity logging for existing posts', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    $post = Post::create([
        'title' => 'Existing Post',
        'intro' => 'Testing existing post.',
        'content' => '<p>Some content</p>',
    ]);

    $observer = new PostObserver;

    // Simulate an existing post (not newly created)
    $post->wasRecentlyCreated = false;
    $post->enableLoggingModelsEvents = true;

    $observer->saved($post);

    // Logging should remain enabled
    expect($post->enableLoggingModelsEvents)->toBeTrue();
});

it('updates the created activity with content after the second save', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    // Create a post without content (simulating the first save)
    $post = Post::create([
        'title' => 'Content Activity Test Post',
        'intro' => 'Testing content in activity log.',
    ]);

    // Simulate the RichEditor setting content on the second save
    $post->content = '<p>Rich editor content</p>';
    $post->wasRecentlyCreated = true;

    $observer = new PostObserver;
    $observer->saved($post);

    // The "created" activity should now include the content field
    $activity = $post->activitiesAsSubject()
        ->where('event', 'created')
        ->latest('created_at')
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->properties['attributes']['content'])->toBe('<p>Rich editor content</p>');
});
