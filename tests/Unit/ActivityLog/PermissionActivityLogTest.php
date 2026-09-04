<?php

/** @noinspection PhpUndefinedMethodInspection, LaravelEloquentGuardedAttributeAssignmentInspection */

use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Models\Concerns\LogsActivity;

uses(RefreshDatabase::class);

test('the Permission model uses the LogsActivity trait', function () {
    expect(in_array(LogsActivity::class, class_uses_recursive(Permission::class), true))->toBeTrue();
});

test('it logs an activity when a permission is created', function () {
    $permission = Permission::create(['name' => 'access admin']);

    expect(Activity::query()
        ->where('subject_type', $permission->getMorphClass())
        ->where('subject_id', $permission->id)
        ->where('event', 'created')
        ->exists())->toBeTrue();
});

test('it logs an activity when a permission is updated', function () {
    $permission = Permission::create(['name' => 'access admin']);
    $permission->update(['name' => 'access cms']);

    expect(Activity::query()
        ->where('subject_type', $permission->getMorphClass())
        ->where('subject_id', $permission->id)
        ->where('event', 'updated')
        ->exists())->toBeTrue();
});

test('it logs an activity when a permission is deleted', function () {
    $permission = Permission::create(['name' => 'access admin']);
    $permission->delete();

    expect(Activity::query()
        ->where('subject_type', $permission->getMorphClass())
        ->where('event', 'deleted')
        ->exists())->toBeTrue();
});

test('it logs trashed event for soft-delete and deleted event for force delete', function () {
    $permission = Permission::factory()->create();

    // Soft delete
    //    $permission->delete();

    //    $activity = Activity::where('event', 'trashed')->latest()->first();
    //    expect($activity)->not->toBeNull()
    //        ->and($activity->subject->is($permission))->toBeTrue()
    //        ->and($activity->event)->toBe('trashed');

    // Force delete
    $deletedPermissionId = $permission->id;
    $permission->forceDelete();

    $activity = Activity::where('event', 'deleted')->latest()->first();
    expect($activity)->not->toBeNull()
        ->and($activity->subject_type)->toBe(Permission::class)
        ->and($activity->subject_id)->toBe($deletedPermissionId)
        ->and($activity->event)->toBe('deleted');
});
