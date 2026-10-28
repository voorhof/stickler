<?php

use App\Jobs\GenerateSitemapJob;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

use function Illuminate\Support\defer;

use Illuminate\Support\Facades\Queue;

use function Pest\Laravel\actingAs;

use Spatie\ResponseCache\Facades\ResponseCache;

it('tracks creator and updater on tag create and update', function (): void {
    $creator = User::factory()->create();
    $updater = User::factory()->create();

    actingAs($creator);

    $tag = Tag::create([
        'name' => 'Observer Test Tag',
    ]);

    expect($tag->created_by_user_id)->toBe($creator->id)
        ->and($tag->updated_by_user_id)->toBe($creator->id);

    actingAs($updater);

    $tag->update(['name' => 'Updated Tag']);

    expect($tag->fresh()->created_by_user_id)->toBe($creator->id)
        ->and($tag->fresh()->updated_by_user_id)->toBe($updater->id);
});

it('queues sitemap generation when a tag is created', function (): void {
    $user = User::factory()->create();
    Queue::fake([GenerateSitemapJob::class]);
    actingAs($user);

    Tag::create([
        'name' => 'Sitemap Observer Test Tag',
    ]);

    Queue::assertPushed(GenerateSitemapJob::class);
});

it('queues sitemap generation when a tag is deleted', function (): void {
    $user = User::factory()->create();
    $tag = Model::withoutEvents(fn (): Tag => Tag::factory()->create([
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]));
    Queue::fake([GenerateSitemapJob::class]);

    $tag->delete();

    Queue::assertPushed(GenerateSitemapJob::class);
});

it('flushes response cache when a tag is saved', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    config(['responsecache.enabled' => true]);

    ResponseCache::shouldReceive('clear')->once();

    Tag::create([
        'name' => 'Cache Flush Test Tag',
    ]);

    defer()->invoke();

    ResponseCache::shouldReceive('clear')->once();

    Tag::first()->update(['name' => 'Cache Flush Updated Tag']);

    defer()->invoke();
});

it('flushes response cache when a tag is deleted', function (): void {
    $user = User::factory()->create();
    $tag = Model::withoutEvents(fn (): Tag => Tag::factory()->create([
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
    ]));

    config(['responsecache.enabled' => true]);
    ResponseCache::shouldReceive('clear')->once();

    $tag->delete();

    defer()->invoke();
});
