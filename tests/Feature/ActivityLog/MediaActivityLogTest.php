<?php

use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

use function Pest\Laravel\actingAs;

use Spatie\Activitylog\Models\Activity;

uses(RefreshDatabase::class);

it('does not log generated_conversions in activity log for media', function () {
    $user = User::factory()->create();

    actingAs($user);

    $file = UploadedFile::fake()->image('avatar.jpg');

    // Add media to trigger conversion
    // This will trigger creation activity and then update activity due to generated_conversions
    $media = $user->addMedia($file)->toMediaCollection('avatar');

    // Get activities for Media model
    $activities = Activity::where('subject_type', Media::class)
        ->where('subject_id', $media->id)
        ->get();

    // Verify generated_conversions is not in properties of any activity
    foreach ($activities as $activity) {
        expect($activity->properties
            ->has('generated_conversions'))
            ->toBeFalse('Activity log should not contain generated_conversions. Current: '.json_encode($activity->properties));
    }

    // Also, verify we don't have update logs with generated_conversions
    $updateActivities = $activities->filter(fn ($a) => $a->event === 'updated');
    foreach ($updateActivities as $activity) {
        // The properties should not have generated_conversions
        expect($activity->properties->has('generated_conversions'))->toBeFalse();
    }
});
