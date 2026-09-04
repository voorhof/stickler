<?php

namespace App\Observers;

use App\Models\Role;
use Illuminate\Support\Facades\Auth;

class RoleObserver
{
    /**
     * Handle the Role "creating" event.
     * Sets the creator and initial updater to the authenticated user.
     */
    public function creating(Role $role): void
    {
        if (Auth::check()) {
            $role->created_by_user_id = Auth::id();
            $role->updated_by_user_id = Auth::id();
        }
    }

    /**
     * Handle the Role "updating" event.
     * Updates the updater to the current authenticated user.
     */
    public function updating(Role $role): void
    {
        if (Auth::check()) {
            $role->updated_by_user_id = Auth::id();
        }
    }
}
