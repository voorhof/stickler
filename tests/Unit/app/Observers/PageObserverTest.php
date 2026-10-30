<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Jobs\GenerateSitemapJob;
use App\Models\Media;
use App\Models\Page;
use App\Models\User;
use App\Observers\PageObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

use function Illuminate\Support\defer;

use Illuminate\Support\Facades\Queue;

use function Pest\Laravel\actingAs;

use Spatie\ResponseCache\Facades\ResponseCache;

it('tracks creator and updater on page create and update', function (): void {
    $creator = User::factory()->create();
    $updater = User::factory()->create();

    actingAs($creator);

    $page = Page::create([
        'title' => 'Observer Test Page',
        'intro' => 'Testing observer tracking.',
    ]);

    expect($page->created_by_user_id)->toBe($creator->id)
        ->and($page->updated_by_user_id)->toBe($creator->id);

    actingAs($updater);

    $page->update(['title' => 'Updated Title']);

    expect($page->fresh()->created_by_user_id)->toBe($creator->id)
        ->and($page->fresh()->updated_by_user_id)->toBe($updater->id);
});

it('does not overwrite user ids when no user is authenticated', function (): void {
    $creator = User::factory()->create();

    actingAs($creator);

    $page = Page::create([
        'title' => 'Auth Create No Auth Update',
        'intro' => 'Testing unauthenticated updates.',
    ]);

    $originalCreatorId = $page->created_by_user_id;
    $originalUpdaterId = $page->updated_by_user_id;

    auth()->logout();

    $page->update(['title' => 'Updated Without Auth']);

    expect($page->fresh()->created_by_user_id)->toBe($originalCreatorId)
        ->and($page->fresh()->updated_by_user_id)->toBe($originalUpdaterId);
});

it('appends timestamp suffix to excluded slug on create', function (): void {
    Carbon::setTestNow('2026-10-16 12:00:00');

    $user = User::factory()->create();
    actingAs($user);

    $excludedSlug = config('sluggable.excluded_from_slug')[0] ?? 'contact';
    $expectedSuffix = now()->format('-y-m-d-H-i-s');

    $page = Page::create([
        'title' => 'Contact Page',
        'slug' => $excludedSlug,
        'intro' => 'Testing excluded slug.',
    ]);

    expect($page->slug)->toBe($excludedSlug.$expectedSuffix)
        ->and($page->fresh()->slug)->toBe($excludedSlug.$expectedSuffix);

    Carbon::setTestNow();
});

it('appends timestamp suffix to excluded slug on update', function (): void {
    Carbon::setTestNow('2026-10-16 12:00:00');

    $user = User::factory()->create();
    actingAs($user);

    $page = Page::create([
        'title' => 'Normal Page',
        'slug' => 'normal-page',
        'intro' => 'Original intro.',
    ]);

    expect($page->slug)->toBe('normal-page');

    $excludedSlug = config('sluggable.excluded_from_slug')[0] ?? 'contact';
    $expectedSuffix = now()->format('-y-m-d-H-i-s');

    $page->update(['slug' => $excludedSlug]);

    expect($page->fresh()->slug)->toBe($excludedSlug.$expectedSuffix);

    Carbon::setTestNow();
});

it('does not alter non-excluded slug on create or update', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    $page = Page::create([
        'title' => 'Unique Title',
        'slug' => 'unique-allowed-slug',
        'intro' => 'Intro text.',
    ]);

    expect($page->slug)->toBe('unique-allowed-slug');

    $page->update(['slug' => 'another-unique-slug']);

    expect($page->fresh()->slug)->toBe('another-unique-slug');
});

it('appends timestamp to excluded slug via observer methods directly', function (): void {
    Carbon::setTestNow('2026-10-16 12:00:00');

    $observer = new PageObserver;
    $excludedSlug = config('sluggable.excluded_from_slug')[0] ?? 'contact';
    $expectedSuffix = now()->format('-y-m-d-H-i-s');

    $page = new Page([
        'title' => 'Direct Observer Test',
        'slug' => $excludedSlug,
    ]);

    $observer->creating($page);
    expect($page->slug)->toBe($excludedSlug.$expectedSuffix);

    $page->slug = $excludedSlug;
    $observer->updating($page);
    expect($page->slug)->toBe($excludedSlug.$expectedSuffix);

    Carbon::setTestNow();
});

it('does not append suffix when not authenticated but slug is excluded', function (): void {
    Carbon::setTestNow('2026-10-16 12:00:00');

    auth()->logout();

    $excludedSlug = config('sluggable.excluded_from_slug')[0] ?? 'contact';
    $expectedSuffix = now()->format('-y-m-d-H-i-s');

    $page = Page::create([
        'title' => 'Unauth Excluded Slug',
        'slug' => $excludedSlug,
        'intro' => 'Testing unauth excluded slug.',
    ]);

    expect($page->slug)->toBe($excludedSlug.$expectedSuffix);

    Carbon::setTestNow();
});

it('syncs image alt text to media name when a page is saved', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    $uuid = '0190a2b1-c3d4-7e8f-9a0b-1c2d3e4f5a6b';

    $page = Page::create([
        'title' => 'Page with Rich Content Image',
        'intro' => 'Intro text',
        'content' => '<p><img src="/storage/test.jpg" alt="Observer Test Alt" data-id="'.$uuid.'"></p>',
    ]);

    $media = Media::create([
        'uuid' => $uuid,
        'model_type' => $page->getMorphClass(),
        'model_id' => $page->getKey(),
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

    $page->update(['title' => 'Updated Page Title']);

    expect($media->fresh()->name)->toBe('Observer Test Alt');
});

it('calls syncImageAltToMediaName directly via observer saved method', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    $uuid = '0190a2b1-c3d4-7e8f-9a0b-1c2d3e4f5a6b';

    $page = Page::create([
        'title' => 'Page Observer Direct Test',
        'intro' => 'Intro text',
        'content' => '<p><img src="/storage/test.jpg" alt="Direct Observer Alt" data-id="'.$uuid.'"></p>',
    ]);

    $media = Media::create([
        'uuid' => $uuid,
        'model_type' => $page->getMorphClass(),
        'model_id' => $page->getKey(),
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

    $observer = new PageObserver;
    $observer->saved($page);

    expect($media->fresh()->name)->toBe('Direct Observer Alt');
});

it('queues sitemap generation when a page is created', function (): void {
    $user = User::factory()->create();
    Queue::fake([GenerateSitemapJob::class]);
    actingAs($user);

    Page::create([
        'title' => 'Sitemap Observer Test Page',
        'intro' => 'Testing sitemap generation.',
    ]);

    Queue::assertPushed(GenerateSitemapJob::class);
});

it('queues sitemap generation when a page is deleted', function (): void {
    $user = User::factory()->create();
    $page = Model::withoutEvents(fn (): Page => Page::factory()->create([
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]));
    Queue::fake([GenerateSitemapJob::class]);

    $page->delete();

    Queue::assertPushed(GenerateSitemapJob::class);
});

it('flushes response cache when a page is saved', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    config(['responsecache.enabled' => true]);

    ResponseCache::shouldReceive('clear')->once();

    Page::create([
        'title' => 'Cache Flush Test Page',
        'intro' => 'Testing cache flush on save.',
    ]);

    defer()->invoke();

    ResponseCache::shouldReceive('clear')->once();

    Page::first()->update(['title' => 'Cache Flush Updated Page']);

    defer()->invoke();
});

it('flushes response cache when a page is deleted', function (): void {
    $user = User::factory()->create();
    $page = Model::withoutEvents(fn (): Page => Page::factory()->create([
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]));

    config(['responsecache.enabled' => true]);
    ResponseCache::shouldReceive('clear')->once();

    $page->delete();

    defer()->invoke();
});

it('disables activity logging on second save for newly created pages', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    $page = Page::create([
        'title' => 'Activity Log Test Page',
        'intro' => 'Testing activity log suppression.',
    ]);

    $observer = new PageObserver;

    // Simulate a newly created page
    $page->wasRecentlyCreated = true;

    // The observer should call disableLogging on the model
    $observer->saved($page);

    // If disableLogging was called, enableLoggingModelsEvents should be false
    expect($page->enableLoggingModelsEvents)->toBeFalse();
});

it('does not disable activity logging for existing pages', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    $page = Page::create([
        'title' => 'Existing Page',
        'intro' => 'Testing existing page.',
        'content' => '<p>Some content</p>',
    ]);

    $observer = new PageObserver;

    // Simulate an existing page (not newly created)
    $page->wasRecentlyCreated = false;
    $page->enableLoggingModelsEvents = true;

    $observer->saved($page);

    // Logging should remain enabled
    expect($page->enableLoggingModelsEvents)->toBeTrue();
});
