<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Models\Post;
use App\Traits\LogsTrashedActivity;
use Spatie\Activitylog\Models\Activity;

test('it does not change event name if event name is not deleted', function () {
    $model = Mockery::mock(Post::class)->makePartial();
    $model->shouldReceive('isForceDeleting')->never();

    $activity = new Activity;
    $activity->event = 'updated';

    $model->beforeActivityLogged($activity, 'updated');

    expect($activity->event)->toBe('updated');
});

test('it does not change event name if model does not use soft deletes', function () {
    $model = Mockery::mock(LogsTrashedActivity::class);

    $activity = new Activity;
    $activity->event = 'deleted';

    $model->beforeActivityLogged($activity, 'deleted');

    expect($activity->event)->toBe('deleted');
});

test('it keeps event name deleted when force deleting', function () {
    $model = Mockery::mock(Post::class)->makePartial();
    $model->shouldReceive('isForceDeleting')->once()->andReturnTrue();

    $activity = new Activity;
    $activity->event = 'deleted';

    $model->beforeActivityLogged($activity, 'deleted');

    expect($activity->event)->toBe('deleted');
});

test('it changes event name to trashed when soft deleting', function () {
    $model = Mockery::mock(Post::class)->makePartial();
    $model->shouldReceive('isForceDeleting')->once()->andReturnFalse();

    $activity = new Activity;
    $activity->event = 'deleted';

    $model->beforeActivityLogged($activity, 'deleted');

    expect($activity->event)->toBe('trashed');
});
