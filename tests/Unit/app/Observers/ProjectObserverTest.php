<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Jobs\GenerateSitemapJob;
use App\Models\Media;
use App\Models\Project;
use App\Models\User;
use App\Observers\ProjectObserver;

use function Illuminate\Support\defer;

use Illuminate\Support\Facades\Queue;

use function Pest\Laravel\actingAs;

use Spatie\ResponseCache\Facades\ResponseCache;

it('tracks creator and updater on project create and update', function (): void {
    $creator = User::factory()->create();
    $updater = User::factory()->create();

    actingAs($creator);

    $project = Project::create([
        'title' => 'Observer Test Project',
        'intro' => 'Testing observer tracking.',
    ]);

    expect($project->created_by_user_id)->toBe($creator->id)
        ->and($project->updated_by_user_id)->toBe($creator->id);

    actingAs($updater);

    $project->update(['title' => 'Updated Title']);

    expect($project->fresh()->created_by_user_id)->toBe($creator->id)
        ->and($project->fresh()->updated_by_user_id)->toBe($updater->id);
});

it('syncs image alt text to media name when a project is saved with rich content', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    $uuid = '0190a2b1-c3d4-7e8f-9a0b-1c2d3e4f5a6b';

    $project = Project::create([
        'title' => 'Project with Rich Content Image',
        'intro' => 'Intro text',
        'content' => '<p><img src="/storage/test.jpg" alt="Observer Test Alt" data-id="'.$uuid.'"></p>',
    ]);

    $media = Media::create([
        'uuid' => $uuid,
        'model_type' => $project->getMorphClass(),
        'model_id' => $project->getKey(),
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

    // Trigger saved event by updating project
    $project->update([
        'title' => 'Updated Project Title',
    ]);

    expect($media->fresh()->name)->toBe('Observer Test Alt');
});

it('calls syncImageAltToMediaName directly via observer saved method', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    $uuid = '0190a2b1-c3d4-7e8f-9a0b-1c2d3e4f5a6b';

    $project = Project::create([
        'title' => 'Project Observer Direct Test',
        'intro' => 'Intro text',
        'content' => '<p><img src="/storage/test.jpg" alt="Direct Observer Alt" data-id="'.$uuid.'"></p>',
    ]);

    $media = Media::create([
        'uuid' => $uuid,
        'model_type' => $project->getMorphClass(),
        'model_id' => $project->getKey(),
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

    $observer = new ProjectObserver;
    $observer->saved($project);

    expect($media->fresh()->name)->toBe('Direct Observer Alt');
});

it('queues sitemap generation when a project is created', function (): void {
    $user = User::factory()->create();
    Queue::fake([GenerateSitemapJob::class]);
    actingAs($user);

    Project::create([
        'title' => 'Sitemap Observer Test Project',
        'intro' => 'Testing sitemap generation.',
    ]);

    Queue::assertPushed(GenerateSitemapJob::class);
});

it('queues sitemap generation when a project is deleted', function (): void {
    $user = User::factory()->create();
    $project = Project::withoutEvents(fn (): Project => Project::factory()->create([
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]));
    Queue::fake([GenerateSitemapJob::class]);

    $project->delete();

    Queue::assertPushed(GenerateSitemapJob::class);
});

it('flushes response cache when a project is saved', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    config(['responsecache.enabled' => true]);

    ResponseCache::shouldReceive('clear')->once();

    Project::create([
        'title' => 'Cache Flush Test Project',
        'intro' => 'Testing cache flush on save.',
    ]);

    defer()->invoke();

    ResponseCache::shouldReceive('clear')->once();

    Project::first()->update(['title' => 'Cache Flush Updated Project']);

    defer()->invoke();
});

it('flushes response cache when a project is deleted', function (): void {
    $user = User::factory()->create();
    $project = Project::withoutEvents(fn (): Project => Project::factory()->create([
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]));

    config(['responsecache.enabled' => true]);
    ResponseCache::shouldReceive('clear')->once();

    $project->delete();

    defer()->invoke();
});
