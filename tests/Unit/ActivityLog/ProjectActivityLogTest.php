<?php

/** @noinspection PhpUndefinedMethodInspection, PhpPossiblePolymorphicInvocationInspection */

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Models\Concerns\LogsActivity;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->author = User::factory()->create();
});

test('the Project model uses the LogsActivity trait', function () {
    expect(in_array(LogsActivity::class, class_uses_recursive(Project::class), true))->toBeTrue();
});

test('it logs an activity when a project is created', function () {
    $project = Project::factory()->create([
        'created_by_user_id' => $this->author->id,
        'updated_by_user_id' => $this->author->id,
    ]);

    $activity = Activity::query()
        ->where('subject_type', $project->getMorphClass())
        ->where('subject_id', $project->id)
        ->where('event', 'created')
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->event)->toBe('created')
        ->and($activity->subject->is($project))->toBeTrue();
});

test('it logs an activity when a project is updated', function () {
    $project = Project::factory()->create([
        'created_by_user_id' => $this->author->id,
        'updated_by_user_id' => $this->author->id,
    ]);

    $project->update(['title' => 'A new title']);

    expect(Activity::query()
        ->where('subject_type', $project->getMorphClass())
        ->where('subject_id', $project->id)
        ->where('event', 'updated')
        ->exists())->toBeTrue();
});

test('it uses the authenticated user as the causer of project activities', function () {
    auth()->login($this->author);

    $project = Project::factory()->create([
        'created_by_user_id' => $this->author->id,
        'updated_by_user_id' => $this->author->id,
    ]);

    $activity = Activity::query()
        ->where('subject_type', $project->getMorphClass())
        ->where('subject_id', $project->id)
        ->where('event', 'created')
        ->first();

    expect($activity->causer)->toBeInstanceOf(User::class)
        ->and($activity->causer->is($this->author))->toBeTrue();
});

test('it logs trashed event for soft-delete and deleted event for force delete', function () {
    $project = Project::factory()->create();

    // Soft delete
    $project->delete();

    $activity = Activity::where('event', 'trashed')->latest()->first();
    expect($activity)->not->toBeNull()
        ->and($activity->subject->is($project))->toBeTrue()
        ->and($activity->event)->toBe('trashed');

    // Force delete
    $deletedProjectId = $project->id;
    $project->forceDelete();

    $activity = Activity::where('event', 'deleted')->latest()->first();
    expect($activity)->not->toBeNull()
        ->and($activity->subject_type)->toBe(Project::class)
        ->and($activity->subject_id)->toBe($deletedProjectId)
        ->and($activity->event)->toBe('deleted');
});
