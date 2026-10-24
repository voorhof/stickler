<?php

use App\Models\Permission;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Models\Concerns\LogsActivity;

describe('trait registration', function () {
    test('the Permission model uses the LogsActivity trait', function () {
        expect(in_array(LogsActivity::class, class_uses_recursive(Permission::class), true))->toBeTrue();
    });
});

describe('create activity logging', function () {
    test('it logs an activity when a permission is created', function () {
        $permission = Permission::create(['name' => 'access admin']);

        expect(Activity::query()
            ->where('subject_type', $permission->getMorphClass())
            ->where('subject_id', $permission->id)
            ->where('event', 'created')
            ->exists())->toBeTrue();
    });
});

describe('update activity logging', function () {
    test('it logs an activity when a permission is updated', function () {
        $permission = Permission::create(['name' => 'access admin']);
        $permission->update(['name' => 'access cms']);

        expect(Activity::query()
            ->where('subject_type', $permission->getMorphClass())
            ->where('subject_id', $permission->id)
            ->where('event', 'updated')
            ->exists())->toBeTrue();
    });
});

describe('delete activity logging', function () {
    test('it logs deleted event for force delete', function () {
        $permission = Permission::factory()->create();

        $deletedPermissionId = $permission->id;
        $permission->forceDelete();

        $activity = Activity::where('event', 'deleted')->latest()->first();
        expect($activity)->not->toBeNull()
            ->and($activity->subject_type)->toBe(Permission::class)
            ->and($activity->subject_id)->toBe($deletedPermissionId)
            ->and($activity->event)->toBe('deleted');
    });
});
