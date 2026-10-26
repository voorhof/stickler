<?php

/** @noinspection PhpUndefinedFieldInspection, PhpUndefinedMethodInspection */

use App\Models\Page;
use App\Models\Post;
use App\Models\Project;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Spatie\ResponseCache\Facades\ResponseCache;

const SCHEDULED_CONTENT_LAST_CHECKED_CACHE_KEY = 'scheduled-content:last-checked-at';

function createScheduledContentForCacheCommandTest(string $modelClass, User $user, ?CarbonInterface $publishedAt): Model
{
    return $modelClass::factory()->create([
        'created_by_user_id' => $user->id,
        'updated_by_user_id' => $user->id,
        'published_at' => $publishedAt,
    ]);
}

it('clears the response cache when scheduled content becomes public', function (string $modelClass): void {
    $this->travelTo(Carbon::parse('2026-10-26 11:00:00'));
    config(['responsecache.enabled' => false]);

    $user = User::factory()->create();
    createScheduledContentForCacheCommandTest($modelClass, $user, now()->subMinutes(30));
    Cache::forever(SCHEDULED_CONTENT_LAST_CHECKED_CACHE_KEY, now()->subHour()->toIso8601String());
    config(['responsecache.enabled' => true]);

    ResponseCache::shouldReceive('clear')->once();

    $this->artisan('content:clear-scheduled-cache')
        ->expectsOutput('Response cache cleared for newly published content.')
        ->assertSuccessful();

    expect(Cache::get(SCHEDULED_CONTENT_LAST_CHECKED_CACHE_KEY))->toBe(now()->toIso8601String());
})->with([
    'posts' => [Post::class],
    'pages' => [Page::class],
    'projects' => [Project::class],
]);

it('does not clear the response cache when no publishable content exists', function (): void {
    $this->travelTo(Carbon::parse('2026-10-26 11:00:00'));
    config(['responsecache.enabled' => true]);
    Cache::forever(SCHEDULED_CONTENT_LAST_CHECKED_CACHE_KEY, now()->subHour()->toIso8601String());

    ResponseCache::shouldReceive('clear')->never();

    $this->artisan('content:clear-scheduled-cache')->assertSuccessful();

    expect(Cache::get(SCHEDULED_CONTENT_LAST_CHECKED_CACHE_KEY))->toBe(now()->toIso8601String());
});

it('does not clear the response cache for content outside the publish window', function (string $publishedAt): void {
    $this->travelTo(Carbon::parse('2026-10-26 11:00:00'));
    config(['responsecache.enabled' => false]);

    $user = User::factory()->create();
    createScheduledContentForCacheCommandTest(Post::class, $user, Carbon::parse($publishedAt));
    Cache::forever(SCHEDULED_CONTENT_LAST_CHECKED_CACHE_KEY, now()->subHour()->toIso8601String());
    config(['responsecache.enabled' => true]);

    ResponseCache::shouldReceive('clear')->never();

    $this->artisan('content:clear-scheduled-cache')->assertSuccessful();
})->with([
    'still scheduled' => ['2026-10-26 11:30:00'],
    'published before the last check' => ['2026-10-26 09:00:00'],
    'published at the last check' => ['2026-10-26 10:00:00'],
]);

it('clears the response cache when content is published exactly at the current check', function (): void {
    $this->travelTo(Carbon::parse('2026-10-26 11:00:00'));
    config(['responsecache.enabled' => false]);

    $user = User::factory()->create();
    createScheduledContentForCacheCommandTest(Post::class, $user, now());
    Cache::forever(SCHEDULED_CONTENT_LAST_CHECKED_CACHE_KEY, now()->subHour()->toIso8601String());
    config(['responsecache.enabled' => true]);

    ResponseCache::shouldReceive('clear')->once();

    $this->artisan('content:clear-scheduled-cache')
        ->expectsOutput('Response cache cleared for newly published content.')
        ->assertSuccessful();
});

it('clears the response cache once when no previous check is recorded', function (): void {
    $this->travelTo(Carbon::parse('2026-10-26 11:00:00'));
    config(['responsecache.enabled' => true]);
    Cache::forget(SCHEDULED_CONTENT_LAST_CHECKED_CACHE_KEY);

    ResponseCache::shouldReceive('clear')->once();

    $this->artisan('content:clear-scheduled-cache')
        ->expectsOutput('Response cache cleared for newly published content.')
        ->assertSuccessful();

    expect(Cache::get(SCHEDULED_CONTENT_LAST_CHECKED_CACHE_KEY))->toBe(now()->toIso8601String());
});

it('does not clear the response cache when response caching is disabled', function (): void {
    $this->travelTo(Carbon::parse('2026-10-26 11:00:00'));
    config(['responsecache.enabled' => false]);
    Cache::forget(SCHEDULED_CONTENT_LAST_CHECKED_CACHE_KEY);

    ResponseCache::shouldReceive('clear')->never();

    $this->artisan('content:clear-scheduled-cache')->assertSuccessful();

    expect(Cache::get(SCHEDULED_CONTENT_LAST_CHECKED_CACHE_KEY))->toBe(now()->toIso8601String());
});

it('does not clear the response cache for soft-deleted scheduled content', function (): void {
    $this->travelTo(Carbon::parse('2026-10-26 11:00:00'));
    config(['responsecache.enabled' => false]);

    $user = User::factory()->create();
    $post = createScheduledContentForCacheCommandTest(Post::class, $user, now()->subMinutes(30));
    $post->delete();
    Cache::forever(SCHEDULED_CONTENT_LAST_CHECKED_CACHE_KEY, now()->subHour()->toIso8601String());
    config(['responsecache.enabled' => true]);

    ResponseCache::shouldReceive('clear')->never();

    $this->artisan('content:clear-scheduled-cache')->assertSuccessful();
});

it('schedules the cache-clearing command hourly at minute zero', function (): void {
    $event = collect(app(Schedule::class)->events())
        ->first(fn ($event): bool => str_contains($event->command, 'content:clear-scheduled-cache'));

    expect($event?->expression)->toBe('0 * * * *')
        ->and($event?->withoutOverlapping)->toBeTrue()
        ->and($event?->onOneServer)->toBeTrue();
});
