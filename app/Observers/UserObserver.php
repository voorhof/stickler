<?php

namespace App\Observers;

use App\Exceptions\AdministrativeLockoutException;
use App\Models\User;
use App\Observers\Traits\TracksCreatorAndUpdaterObserver;

class UserObserver
{
    use TracksCreatorAndUpdaterObserver;

    /**
     * Handle the User "deleting" event.
     */
    public function deleting(User $user): void
    {
        if ($user->hasRole('Admin') && User::role('Admin')->withoutTrashed()->where('id', '!=', $user->id)->count() === 0) {
            throw new AdministrativeLockoutException(__('The last remaining administrator cannot be deleted.'));
        }
    }

    /**
     * Handle the User "forceDeleting" event.
     */
    public function forceDeleting(User $user): void
    {
        if ($user->hasRole('Admin') && User::role('Admin')->where('id', '!=', $user->id)->count() === 0) {
            throw new AdministrativeLockoutException(__('The last remaining administrator cannot be permanently deleted.'));
        }
    }
}
