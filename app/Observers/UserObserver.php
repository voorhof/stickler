<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserObserver
{
    /**
     * Handle the User "creating" event.
     * Sets the creator and initial updater to the authenticated user.
     */
    public function creating(User $user): void
    {
        if (Auth::check()) {
            $user->created_by_user_id = Auth::id();
            $user->updated_by_user_id = Auth::id();
        }
    }

    /**
     * Handle the User "updating" event.
     * Updates the updater to the current authenticated user.
     */
    public function updating(User $user): void
    {
        if (Auth::check()) {
            $user->updated_by_user_id = Auth::id();
        }
    }
}
