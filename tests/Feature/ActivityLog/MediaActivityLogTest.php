<?php

/** @noinspection PhpUndefinedMethodInspection, PhpUnhandledExceptionInspection */

use App\Models\Media;
use App\Models\User;
use Illuminate\Http\UploadedFile;

use function Pest\Laravel\actingAs;

use Spatie\Activitylog\Models\Activity;

describe('media activity logging', function () {
    test('it does not log generated_conversions in activity log for media', function () {
        $user = User::factory()->create();

        actingAs($user);

        $file = UploadedFile::fake()->image('avatar.jpg');

        $media = $user->addMedia($file)->toMediaCollection('avatar');

        $activities = Activity::where('subject_type', Media::class)
            ->where('subject_id', $media->id)
            ->get();

        foreach ($activities as $activity) {
            expect($activity->properties->has('generated_conversions'))
                ->toBeFalse('Activity log should not contain generated_conversions. Current: '.json_encode($activity->properties));
        }

        $updateActivities = $activities->filter(fn ($a) => $a->event === 'updated');
        foreach ($updateActivities as $activity) {
            expect($activity->properties->has('generated_conversions'))->toBeFalse();
        }
    });
});
