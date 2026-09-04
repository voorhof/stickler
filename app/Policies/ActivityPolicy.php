<?php

namespace App\Policies;

use App\Models\User;

class ActivityPolicy
{
    /**
     * Determine whether the user can view any activities.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view activities');
    }

    /**
     * Determine whether the user can view the activity.
     */
    public function view(User $user): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can create activities.
     */
    public function create(): bool
    {
        // No user can create activities, only the app system can
        return false;
    }

    /**
     * Determine whether the user can update the activity.
     */
    public function update(): bool
    {
        // No user can create activities, only the app system can
        return false;
    }

    /**
     * Determine whether the user can delete any activities.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete activities');
    }

    /**
     * Determine whether the user can delete the activity.
     */
    public function delete(User $user): bool
    {
        return $this->deleteAny($user);
    }

    /**
     * Determine whether the user can permanently delete any activities.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $this->deleteAny($user);
    }

    /**
     * Determine whether the user can permanently delete the activity.
     */
    public function forceDelete(User $user): bool
    {
        return $this->delete($user);
    }
}
