<?php

namespace App\Policies;

use App\Models\User;

class MessagePolicy extends BasePolicy
{
    /**
     * Determine whether the user can create messages.
     * All users can create messages, even guests.
     */
    public function create(?User $user = null): bool
    {
        return true;
    }
}
