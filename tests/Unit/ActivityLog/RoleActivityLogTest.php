<?php

/** @noinspection PhpUndefinedMethodInspection, LaravelEloquentGuardedAttributeAssignmentInspection */

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Models\Concerns\LogsActivity;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->author = User::factory()->create();
});

test('the Role model uses the LogsActivity trait', function () {
    expect(in_array(LogsActivity::class, class_uses_recursive(Role::class), true))->toBeTrue();
});

test('it logs an activity when a role is created', function () {
    $role = Role::factory()->create(['name' => 'Editor', 'created_by_user_id' => $this->author->id, 'updated_by_user_id' => $this->author->id]);

    expect(Activity::query()
        ->where('subject_type', $role->getMorphClass())
        ->where('subject_id', $role->id)
        ->where('event', 'created')
        ->exists())->toBeTrue();
});

test('it logs an activity when a role is updated', function () {
    $role = Role::factory()->create(['name' => 'Editor', 'created_by_user_id' => $this->author->id, 'updated_by_user_id' => $this->author->id]);
    $role->update(['name' => 'Author']);

    expect(Activity::query()
        ->where('subject_type', $role->getMorphClass())
        ->where('subject_id', $role->id)
        ->where('event', 'updated')
        ->exists())->toBeTrue();
});

test('it logs an activity when a role is deleted', function () {
    $role = Role::factory()->create(['name' => 'Editor', 'created_by_user_id' => $this->author->id, 'updated_by_user_id' => $this->author->id]);
    $role->delete();

    expect(Activity::query()
        ->where('subject_type', $role->getMorphClass())
        ->where('event', 'trashed')
        ->exists())->toBeTrue();
});

test('it logs trashed event for soft-delete and deleted event for force delete', function () {
    $role = Role::factory()->create();

    // Force delete
    $deletedRoleId = $role->id;
    $role->forceDelete();

    $activity = Activity::where('event', 'deleted')->latest()->first();
    expect($activity)->not->toBeNull()
        ->and($activity->subject_type)->toBe(Role::class)
        ->and($activity->subject_id)->toBe($deletedRoleId)
        ->and($activity->event)->toBe('deleted');
});
