<?php

use App\Traits\LogsTrashedActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Activity;

test('it does not change event name if event name is not deleted', function () {
    $model = new class
    {
        use LogsTrashedActivity;
    };

    $activity = new Activity;
    $activity->event = 'updated';

    $model->beforeActivityLogged($activity, 'updated');

    expect($activity->event)->toBe('updated');
});

test('it does not change event name if model does not use soft deletes', function () {
    $model = new class
    {
        use LogsTrashedActivity;
    };

    $activity = new Activity;
    $activity->event = 'deleted';

    $model->beforeActivityLogged($activity, 'deleted');

    expect($activity->event)->toBe('deleted');
});

test('it keeps event name deleted when force deleting', function () {
    $model = new class extends Model
    {
        use LogsTrashedActivity, SoftDeletes;

        public function isForceDeleting(): bool
        {
            return true;
        }
    };

    $activity = new Activity;
    $activity->event = 'deleted';

    $model->beforeActivityLogged($activity, 'deleted');

    expect($activity->event)->toBe('deleted');
});

test('it changes event name to trashed when soft deleting', function () {
    $model = new class extends Model
    {
        use LogsTrashedActivity, SoftDeletes;

        public function isForceDeleting(): bool
        {
            return false;
        }
    };

    $activity = new Activity;
    $activity->event = 'deleted';

    $model->beforeActivityLogged($activity, 'deleted');

    expect($activity->event)->toBe('trashed');
});
