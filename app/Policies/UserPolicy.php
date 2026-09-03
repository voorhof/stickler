<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any users.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view users');
    }

    /**
     * Determine whether the user can view the user.
     */
    public function view(User $user): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can create users.
     */
    public function create(User $user): bool
    {
        return $user->can('create users');
    }

    /**
     * Determine whether the user can update the user.
     */
    public function update(User $user, User $model): bool
    {
        if ($model->hasRole('Admin')) {
            return $user->hasAnyRole(['Admin']);
        }

        return $user->can('update users');
    }

    /**
     * Determine whether the user can reorder users.
     */
    public function reorder(User $user): bool
    {
        return $user->can('update users');
    }

    /**
     * Determine whether the user can delete any users.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete users');
    }

    /**
     * Determine whether the user can delete the user.
     */
    public function delete(User $user, User $model): bool
    {
        if ($model->hasRole('Admin')) {
            return $user->hasAnyRole(['Admin']);
        }

        return $this->deleteAny($user);
    }

    /**
     * Determine whether the user can restore any users.
     */
    public function restoreAny(User $user): bool
    {
        return $this->deleteAny($user);
    }

    /**
     * Determine whether the user can restore the user.
     */
    public function restore(User $user, User $model): bool
    {
        return $this->delete($user, $model);
    }

    /**
     * Determine whether the user can permanently delete any users.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $this->deleteAny($user);
    }

    /**
     * Determine whether the user can permanently delete the user.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return $this->delete($user, $model);
    }
}
