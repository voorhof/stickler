<?php

namespace App\Policies;

use App\Models\User;

class MediaPolicy
{
    /**
     * Determine whether the user can view any media.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view media');
    }

    /**
     * Determine whether the user can view the media.
     */
    public function view(User $user): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can create media.
     */
    public function create(User $user): bool
    {
        return $user->can('create media');
    }

    /**
     * Determine whether the user can update the media.
     */
    public function update(User $user): bool
    {
        return $user->can('update media');
    }

    /**
     * Determine whether the user can reorder media.
     */
    public function reorder(User $user): bool
    {
        return $this->update($user);
    }

    /**
     * Determine whether the user can delete any media.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete media');
    }

    /**
     * Determine whether the user can delete the media.
     */
    public function delete(User $user): bool
    {
        return $this->deleteAny($user);
    }

    /**
     * Determine whether the user can permanently delete any media.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $this->deleteAny($user);
    }

    /**
     * Determine whether the user can permanently delete the media.
     */
    public function forceDelete(User $user): bool
    {
        return $this->delete($user);
    }
}
