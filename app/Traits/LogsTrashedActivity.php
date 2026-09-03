<?php

/** @noinspection PhpDynamicFieldDeclarationInspection */

namespace App\Traits;

use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Contracts\Activity;

trait LogsTrashedActivity
{
    /**
     * Change the default event name from Spatie Activitylog to 'trashed' when soft deleting.
     * Keep the event name 'deleted' when removing a model permanently.
     */
    public function beforeActivityLogged(Activity $activity, string $eventName): void
    {
        if ($eventName !== 'deleted') {
            return;
        }

        if (! in_array(SoftDeletes::class, class_uses_recursive($this), true)) {
            return;
        }

        if (! $this->isForceDeleting()) {
            $activity->event = 'trashed';
        }
    }
}
