<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ActivityPolicy extends BasePolicy
{
    /**
     * Determine whether the user can create activities.
     * No user can create activities, only the app system can.
     */
    public function create(?User $user = null): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the activity.
     * No user can update activities, only the app system can.
     */
    public function update(?User $user = null, ?Model $model = null): bool
    {
        return false;
    }
}
