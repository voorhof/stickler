<?php

namespace App\Policies;

use App\Models\User;

class TagPolicy
{
    /**
     * Determine whether the user can view any tags.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view tags');
    }

    /**
     * Determine whether the user can view the tag.
     */
    public function view(User $user): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can create tags.
     */
    public function create(User $user): bool
    {
        return $user->can('create tags');
    }

    /**
     * Determine whether the user can update the tag.
     */
    public function update(User $user): bool
    {
        return $user->can('update tags');
    }

    /**
     * Determine whether the user can reorder tags.
     */
    public function reorder(User $user): bool
    {
        return $this->update($user);
    }

    /**
     * Determine whether the user can delete any tags.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete tags');
    }

    /**
     * Determine whether the user can delete the tag.
     */
    public function delete(User $user): bool
    {
        return $this->deleteAny($user);
    }

    /**
     * Determine whether the user can restore any tags.
     */
    public function restoreAny(User $user): bool
    {
        return $this->deleteAny($user);
    }

    /**
     * Determine whether the user can restore the tag.
     */
    public function restore(User $user): bool
    {
        return $this->delete($user);
    }

    /**
     * Determine whether the user can permanently delete any tags.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $this->deleteAny($user);
    }

    /**
     * Determine whether the user can permanently delete the tag.
     */
    public function forceDelete(User $user): bool
    {
        return $this->delete($user);
    }
}
