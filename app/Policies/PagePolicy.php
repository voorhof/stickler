<?php

namespace App\Policies;

use App\Models\User;

class PagePolicy
{
    /**
     * Determine whether the user can view any pages.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view pages');
    }

    /**
     * Determine whether the user can view the page.
     */
    public function view(User $user): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can create pages.
     */
    public function create(User $user): bool
    {
        return $user->can('create pages');
    }

    /**
     * Determine whether the user can update the page.
     */
    public function update(User $user): bool
    {
        return $user->can('update pages');
    }

    /**
     * Determine whether the user can reorder pages.
     */
    public function reorder(User $user): bool
    {
        return $this->update($user);
    }

    /**
     * Determine whether the user can delete any pages.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete pages');
    }

    /**
     * Determine whether the user can delete the page.
     */
    public function delete(User $user): bool
    {
        return $this->deleteAny($user);
    }

    /**
     * Determine whether the user can restore any pages.
     */
    public function restoreAny(User $user): bool
    {
        return $this->deleteAny($user);
    }

    /**
     * Determine whether the user can restore the page.
     */
    public function restore(User $user): bool
    {
        return $this->delete($user);
    }

    /**
     * Determine whether the user can permanently delete any pages.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $this->deleteAny($user);
    }

    /**
     * Determine whether the user can permanently delete the page.
     */
    public function forceDelete(User $user): bool
    {
        return $this->delete($user);
    }
}
