<?php

namespace App\Policies;

use App\Models\User;

class PostPolicy
{
    /**
     * Determine whether the user can view any posts.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view posts');
    }

    /**
     * Determine whether the user can view the post.
     */
    public function view(User $user): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can create posts.
     */
    public function create(User $user): bool
    {
        return $user->can('create posts');
    }

    /**
     * Determine whether the user can update the post.
     */
    public function update(User $user): bool
    {
        return $user->can('update posts');
    }

    /**
     * Determine whether the user can reorder posts.
     */
    public function reorder(User $user): bool
    {
        return $this->update($user);
    }

    /**
     * Determine whether the user can delete any posts.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete posts');
    }

    /**
     * Determine whether the user can delete the post.
     */
    public function delete(User $user): bool
    {
        return $this->deleteAny($user);
    }

    /**
     * Determine whether the user can restore any posts.
     */
    public function restoreAny(User $user): bool
    {
        return $this->deleteAny($user);
    }

    /**
     * Determine whether the user can restore the post.
     */
    public function restore(User $user): bool
    {
        return $this->delete($user);
    }

    /**
     * Determine whether the user can permanently delete any posts.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $this->deleteAny($user);
    }

    /**
     * Determine whether the user can permanently delete the post.
     */
    public function forceDelete(User $user): bool
    {
        return $this->delete($user);
    }
}
