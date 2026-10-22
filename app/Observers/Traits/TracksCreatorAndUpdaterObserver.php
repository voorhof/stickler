<?php

/** @noinspection PhpPossiblePolymorphicInvocationInspection */

namespace App\Observers\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Automatically tracks the creator and updater user IDs on model events.
 *
 * Use this trait in any observer to eliminate repetitive Auth-based
 * foreign key assignment in <code>creating()</code> and <code>updating()</code> methods.
 */
trait TracksCreatorAndUpdaterObserver
{
    /**
     * Handle the "creating" event.
     *
     * Sets the creator and initial updater to the authenticated user.
     */
    public function creating(Model $model): void
    {
        if (Auth::check()) {
            $model->created_by_user_id = Auth::id();
            $model->updated_by_user_id = Auth::id();
        }
    }

    /**
     * Handle the "updating" event.
     *
     * Updates the updater to the current authenticated user.
     */
    public function updating(Model $model): void
    {
        if (Auth::check()) {
            $model->updated_by_user_id = Auth::id();
        }
    }
}
