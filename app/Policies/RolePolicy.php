<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

class RolePolicy
{
    /**
     * Determine whether the user can view any roles.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view roles');
    }

    /**
     * Determine whether the user can view the role.
     */
    public function view(User $user): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can create roles.
     */
    public function create(User $user): bool
    {
        return $user->can('create roles');
    }

    /**
     * Determine whether the user can update the role.
     */
    public function update(User $user, Role $role): bool
    {
        if ($role->name === 'Admin') {
            return $user->hasRole(['Admin']);
        }

        return $user->can('update roles');
    }

    /**
     * Determine whether the user can reorder the role.
     */
    public function reorder(User $user): bool
    {
        return $user->can('update roles');
    }

    /**
     * Determine whether the user can delete any roles.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete roles');
    }

    /**
     * Determine whether the user can delete the role.
     */
    public function delete(User $user, Role $role): bool
    {
        if ($role->name === 'Admin') {
            return false;
        }

        return $user->can('delete roles');
    }

    /**
     * Determine whether the user can restore any roles.
     */
    public function restoreAny(User $user): bool
    {
        return $this->deleteAny($user);
    }

    /**
     * Determine whether the user can restore the role.
     */
    public function restore(User $user, Role $role): bool
    {
        return $this->delete($user, $role);
    }

    /**
     * Determine whether the user can permanently delete any roles.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $this->deleteAny($user);
    }

    /**
     * Determine whether the user can permanently delete the role.
     */
    public function forceDelete(User $user, Role $role): bool
    {
        return $this->delete($user, $role);
    }
}
