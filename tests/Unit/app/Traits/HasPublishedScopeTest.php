<?php

use App\Models\Page;
use App\Models\Post;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Carbon;

beforeEach(function () {
    User::factory()->create();
    Carbon::setTestNow('2026-10-25 12:00:00');
});

afterEach(function () {
    Carbon::setTestNow();
});

test('isPublicPublished only includes records whose published_at is in the past or now', function (string $modelClass) {
    $pastRecord = $modelClass::factory()->create(['published_at' => now()->subDay()]);
    $currentRecord = $modelClass::factory()->create(['published_at' => now()]);
    $modelClass::factory()->create(['published_at' => null]);
    $modelClass::factory()->create(['published_at' => now()->addDay()]);

    expect($modelClass::isPublicPublished()->pluck('id')->sort()->values()->all())
        ->toBe(collect([$pastRecord->id, $currentRecord->id])->sort()->values()->all());
})->with([
    'post' => [Post::class],
    'project' => [Project::class],
    'page' => [Page::class],
]);

test('isPublicPublished returns no records when none are published', function (string $modelClass) {
    $modelClass::factory()->create(['published_at' => null]);
    $modelClass::factory()->create(['published_at' => now()->addHour()]);

    expect($modelClass::isPublicPublished()->count())->toBe(0);
})->with([
    'post' => [Post::class],
    'project' => [Project::class],
    'page' => [Page::class],
]);

test('becamePublishedBetween includes only records after the start and through the end of the window', function (string $modelClass) {
    $from = now()->subHour();
    $until = now();

    $insideWindow = $modelClass::factory()->create(['published_at' => now()->subMinutes(30)]);
    $atWindowEnd = $modelClass::factory()->create(['published_at' => $until]);
    $modelClass::factory()->create(['published_at' => $from]);
    $modelClass::factory()->create(['published_at' => now()->subHours(2)]);
    $modelClass::factory()->create(['published_at' => now()->addMinutes(30)]);
    $modelClass::factory()->create(['published_at' => null]);

    expect($modelClass::becamePublishedBetween($from, $until)->pluck('id')->sort()->values()->all())
        ->toBe(collect([$insideWindow->id, $atWindowEnd->id])->sort()->values()->all());
})->with([
    'post' => [Post::class],
    'project' => [Project::class],
    'page' => [Page::class],
]);

test('becamePublishedBetween returns no records when none fall within the window', function (string $modelClass) {
    $from = now()->subHour();
    $until = now();

    $modelClass::factory()->create(['published_at' => now()->subHours(2)]);
    $modelClass::factory()->create(['published_at' => now()->addHour()]);
    $modelClass::factory()->create(['published_at' => null]);

    expect($modelClass::becamePublishedBetween($from, $until)->count())->toBe(0);
})->with([
    'post' => [Post::class],
    'project' => [Project::class],
    'page' => [Page::class],
]);

test('becamePublishedBetween returns no records for an empty window', function (string $modelClass) {
    $publishedAt = now();
    $modelClass::factory()->create(['published_at' => $publishedAt]);

    expect($modelClass::becamePublishedBetween($publishedAt, $publishedAt)->pluck('id')->all())
        ->toBe([]);
})->with([
    'post' => [Post::class],
    'project' => [Project::class],
    'page' => [Page::class],
]);

test('isPublished returns true only when published_at is set and in the past or now', function (string $modelClass) {
    $pastRecord = $modelClass::factory()->create(['published_at' => now()->subDay()]);
    $currentRecord = $modelClass::factory()->create(['published_at' => now()]);
    $nullRecord = $modelClass::factory()->create(['published_at' => null]);
    $futureRecord = $modelClass::factory()->create(['published_at' => now()->addDay()]);

    expect($pastRecord->isPublished())->toBeTrue()
        ->and($currentRecord->isPublished())->toBeTrue()
        ->and($nullRecord->isPublished())->toBeFalse()
        ->and($futureRecord->isPublished())->toBeFalse();
})->with([
    'post' => [Post::class],
    'project' => [Project::class],
    'page' => [Page::class],
]);
