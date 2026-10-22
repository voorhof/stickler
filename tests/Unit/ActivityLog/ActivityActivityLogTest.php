<?php

use App\Models\Activity;
use Spatie\Activitylog\Models\Concerns\LogsActivity;

function makeActivity(): Activity
{
    return Activity::create([
        'log_name' => 'default',
        'description' => 'Test activity',
    ]);
}

describe('trait registration', function () {
    test('the Activity model uses the LogsActivity trait', function () {
        expect(in_array(LogsActivity::class, class_uses_recursive(Activity::class), true))->toBeTrue();
    });
});

describe('create and update ignored', function () {
    test('it does not log an activity when an activity record is created', function () {
        Activity::truncate();

        $activity = makeActivity();

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
});

describe('delete activity logging', function () {
    test('it logs deleted event when an activity record is deleted', function () {
        $activity = makeActivity();
        $activity->delete();

        expect(Activity::query()
            ->where('subject_type', $activity->getMorphClass())
            ->where('subject_id', $activity->id)
            ->where('event', 'deleted')
            ->exists())->toBeTrue();
    });

    test('it logs deleted event for force delete with correct subject', function () {
        $activity = makeActivity();

        $deletedActivityId = $activity->id;
        $activity->forceDelete();

        $loggedActivity = Activity::where('event', 'deleted')->latest()->first();
        expect($loggedActivity)->not->toBeNull()
            ->and($loggedActivity->subject_type)->toBe(Activity::class)
            ->and($loggedActivity->subject_id)->toBe($deletedActivityId)
            ->and($loggedActivity->event)->toBe('deleted');
    });
});
