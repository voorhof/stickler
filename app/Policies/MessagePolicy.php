<?php

namespace App\Policies;

use App\Models\User;

class MessagePolicy
{
    /**
     * Determine whether the user can view any messages.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view messages');
    }

    /**
     * Determine whether the user can view the message.
     */
    public function view(User $user): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can create messages.
     */
    public function create(): bool
    {
        // All users can create messages, even guests.
        return true;
    }

    /**
     * Determine whether the user can update the message.
     */
    public function update(User $user): bool
    {
        return $user->can('update messages');
    }

    /**
     * Determine whether the user can reorder messages.
     */
    public function reorder(User $user): bool
    {
        return $this->update($user);
    }

    /**
     * Determine whether the user can delete any messages.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete messages');
    }

    /**
     * Determine whether the user can delete the message.
     */
    public function delete(User $user): bool
    {
        return $this->deleteAny($user);
    }

    /**
     * Determine whether the user can restore any messages.
     */
    public function restoreAny(User $user): bool
    {
        return $this->deleteAny($user);
    }

    /**
     * Determine whether the user can restore the message.
     */
    public function restore(User $user): bool
    {
        return $this->delete($user);
    }

    /**
     * Determine whether the user can permanently delete any messages.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $this->deleteAny($user);
    }

    /**
     * Determine whether the user can permanently delete the message.
     */
    public function forceDelete(User $user): bool
    {
        return $this->delete($user);
    }
}
