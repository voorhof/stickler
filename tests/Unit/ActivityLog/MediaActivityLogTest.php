<?php

/** @noinspection PhpUndefinedMethodInspection, LaravelEloquentGuardedAttributeAssignmentInspection */

use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Models\Concerns\LogsActivity;

uses(RefreshDatabase::class);

function makeMedia(): Media
{
    $user = User::factory()->create();

    actingAs($user);

    return Media::create([
        'model_type' => User::class,
        'model_id' => $user->id,
        'collection_name' => 'avatar',
        'name' => 'Profile Avatar',
        'file_name' => 'avatar.jpg',
        'mime_type' => 'image/jpeg',
        'disk' => 'media',
        'size' => 1024,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);
}

test('the Media model uses the LogsActivity trait', function () {
    expect(in_array(LogsActivity::class, class_uses_recursive(Media::class), true))->toBeTrue();
});

test('it logs an activity when a media record is created', function () {
    $media = makeMedia();

    expect(Activity::query()
        ->where('subject_type', $media->getMorphClass())
        ->where('subject_id', $media->id)
        ->where('event', 'created')
        ->exists())->toBeTrue();
});

test('it logs an activity when a media record is updated', function () {
    $media = makeMedia();
    $media->update(['name' => 'Renamed Avatar']);

    expect(Activity::query()
        ->where('subject_type', $media->getMorphClass())
        ->where('subject_id', $media->id)
        ->where('event', 'updated')
        ->exists())->toBeTrue();
});

test('it logs an activity when a media record is deleted', function () {
    $media = makeMedia();
    $media->delete();

    expect(Activity::query()
        ->where('subject_type', $media->getMorphClass())
        ->where('event', 'deleted')
        ->exists())->toBeTrue();
});

test('it logs deleted event for force delete', function () {
    $media = makeMedia();

    // Force delete
    $deletedMediaId = $media->id;
    $media->forceDelete();

    $activity = Activity::where('event', 'deleted')->latest()->first();
    expect($activity)->not->toBeNull()
        ->and($activity->subject_type)->toBe(Media::class)
        ->and($activity->subject_id)->toBe($deletedMediaId)
        ->and($activity->event)->toBe('deleted');
});
