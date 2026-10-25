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
