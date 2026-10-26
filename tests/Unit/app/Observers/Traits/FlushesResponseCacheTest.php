<?php

/** @noinspection PhpUndefinedFieldInspection */

use App\Models\Media;
use App\Models\Page;
use App\Models\Post;
use App\Models\Project;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

use function Illuminate\Support\defer;

use LaBoiteACode\FilamentBusinessHours\Models\BusinessHours;
use Spatie\ResponseCache\Facades\ResponseCache;

function createFlushResponseCacheTestModel(string $modelClass): Model
{
    $user = User::factory()->create();

    return match ($modelClass) {
        Page::class => Page::factory()->create([
            'created_by_user_id' => $user->id,
            'updated_by_user_id' => $user->id,
        ]),
        Post::class => Post::factory()->create([
            'created_by_user_id' => $user->id,
            'updated_by_user_id' => $user->id,
        ]),
        Project::class => Project::factory()->create([
            'created_by_user_id' => $user->id,
            'updated_by_user_id' => $user->id,
        ]),
        Tag::class => Tag::factory()->create([
            'created_by_user_id' => $user->id,
            'updated_by_user_id' => $user->id,
        ]),
        Media::class => Media::create([
            'model_type' => User::class,
            'model_id' => $user->id,
            'collection_name' => 'default',
            'name' => 'Response cache test media',
            'file_name' => 'response-cache-test.jpg',
            'mime_type' => 'image/jpeg',
            'disk' => 'public',
            'size' => 1024,
            'manipulations' => [],
            'custom_properties' => [],
            'generated_conversions' => [],
            'responsive_images' => [],
        ]),
        BusinessHours::class => BusinessHours::query()->create([
            'key' => 'response-cache-test',
            'timezone' => null,
            'overflow' => false,
            'hours' => [
                'monday' => ['08:00-12:00'],
                'tuesday' => [],
                'wednesday' => [],
                'thursday' => [],
                'friday' => [],
                'saturday' => [],
                'sunday' => [],
            ],
            'exceptions' => [],
        ]),
    };
}

it('defers a response cache clear when public content is saved', function (string $modelClass): void {
    config(['responsecache.enabled' => true]);

    ResponseCache::shouldReceive('clear')->once();

    createFlushResponseCacheTestModel($modelClass);

    expect(defer()->count())->toBe(1)
        ->and(defer()->first()->always)->toBeTrue();

    defer()->invoke();
})->with([
    'posts' => Post::class,
    'pages' => Page::class,
    'projects' => Project::class,
    'tags' => Tag::class,
    'media' => Media::class,
    'business hours' => BusinessHours::class,
]);

it('defers a response cache clear when public content is deleted', function (string $modelClass): void {
    config(['responsecache.enabled' => false]);
    $model = createFlushResponseCacheTestModel($modelClass);

    config(['responsecache.enabled' => true]);

    ResponseCache::shouldReceive('clear')->once();

    $model->delete();

    expect(defer()->count())->toBe(1)
        ->and(defer()->first()->always)->toBeTrue();

    defer()->invoke();
})->with([
    'posts' => Post::class,
    'pages' => Page::class,
    'projects' => Project::class,
    'tags' => Tag::class,
    'media' => Media::class,
    'business hours' => BusinessHours::class,
]);

it('does not schedule a response cache clear when response caching is disabled', function (): void {
    config(['responsecache.enabled' => false]);

    ResponseCache::shouldReceive('clear')->never();

    createFlushResponseCacheTestModel(Post::class);

    expect(defer()->count())->toBe(0);
});

it('clears the response cache only once for multiple model saves', function (): void {
    config(['responsecache.enabled' => true]);

    ResponseCache::shouldReceive('clear')->once();

    createFlushResponseCacheTestModel(Post::class);
    createFlushResponseCacheTestModel(Page::class);
    createFlushResponseCacheTestModel(Tag::class);

    expect(defer()->count())->toBe(1);

    defer()->invoke();
});
