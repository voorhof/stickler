<?php

/** @noinspection PhpUndefinedMethodInspection, LaravelEloquentGuardedAttributeAssignmentInspection */

use App\Models\Activity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Concerns\LogsActivity;

uses(RefreshDatabase::class);

function makeActivity(): Activity
{
    return Activity::create([
        'log_name' => 'default',
        'description' => 'Test activity',
    ]);
}

test('the Activity model uses the LogsActivity trait', function () {
    expect(in_array(LogsActivity::class, class_uses_recursive(Activity::class), true))->toBeTrue();
});

test('it does not log an activity when an activity record is created', function () {
    Activity::truncate();

    $activity = makeActivity();

    // We configured it to ONLY log 'deleted'
    expect(Activity::query()
        ->where('subject_type', $activity->getMorphClass())
        ->where('subject_id', $activity->id)
        ->exists())->toBeFalse();
});

test('it does not log an activity when an activity record is updated', function () {
    $activity = makeActivity();

    Activity::truncate();

    $activity->update(['description' => 'Updated description']);

    expect(Activity::query()
        ->where('subject_type', $activity->getMorphClass())
        ->where('subject_id', $activity->id)
        ->exists())->toBeFalse();
});

test('it logs an activity when an activity record is deleted', function () {
    $activity = makeActivity();

    $activity->delete();

    expect(Activity::query()
        ->where('subject_type', $activity->getMorphClass())
        ->where('subject_id', $activity->id)
        ->where('event', 'deleted')
        ->exists())->toBeTrue();
});

test('it logs trashed event for soft-delete and deleted event for force delete', function () {
    $activity = makeActivity();

    // Soft delete
    //    $activity->delete();

    //    $activity = Activity::where('event', 'trashed')->latest()->first();
    //    expect($activity)->not->toBeNull()
    //        ->and($activity->subject->is($activity))->toBeTrue()
    //        ->and($activity->event)->toBe('trashed');

    // Force delete
    $deletedActivityId = $activity->id;
    $activity->forceDelete();

    $activity = Activity::where('event', 'deleted')->latest()->first();
    expect($activity)->not->toBeNull()
        ->and($activity->subject_type)->toBe(Activity::class)
        ->and($activity->subject_id)->toBe($deletedActivityId)
        ->and($activity->event)->toBe('deleted');
});
