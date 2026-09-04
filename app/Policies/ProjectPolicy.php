<?php

namespace App\Policies;

use App\Models\User;

class ProjectPolicy
{
    /**
     * Determine whether the user can view any projects.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view projects');
    }

    /**
     * Determine whether the user can view the project.
     */
    public function view(User $user): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can create projects.
     */
    public function create(User $user): bool
    {
        return $user->can('create projects');
    }

    /**
     * Determine whether the user can update the project.
     */
    public function update(User $user): bool
    {
        return $user->can('update projects');
    }

    /**
     * Determine whether the user can reorder projects.
     */
    public function reorder(User $user): bool
    {
        return $this->update($user);
    }

    /**
     * Determine whether the user can delete any projects.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete projects');
    }

    /**
     * Determine whether the user can delete the project.
     */
    public function delete(User $user): bool
    {
        return $this->deleteAny($user);
    }

    /**
     * Determine whether the user can restore any projects.
     */
    public function restoreAny(User $user): bool
    {
        return $this->deleteAny($user);
    }

    /**
     * Determine whether the user can restore the project.
     */
    public function restore(User $user): bool
    {
        return $this->delete($user);
    }

    /**
     * Determine whether the user can permanently delete any projects.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $this->deleteAny($user);
    }

    /**
     * Determine whether the user can permanently delete the project.
     */
    public function forceDelete(User $user): bool
    {
        return $this->delete($user);
    }
}
