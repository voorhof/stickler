<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class UserPolicy extends BasePolicy
{
    /**
     * Determine whether the user can update the user.
     * Admin users can only be updated by Admin users.
     */
    public function update(?User $user = null, ?Model $model = null): bool
    {
        if ($model instanceof User && $model->hasRole('Admin')) {
            return $user->hasAnyRole(['Admin']);
        }

        return parent::update($user);
    }

    /**
     * Determine whether the user can delete the user.
     * Admin users can only be deleted by Admin users.
     */
    public function delete(?User $user = null, ?Model $model = null): bool
    {
        if ($model instanceof User && $model->hasRole('Admin')) {
            return $user->hasAnyRole(['Admin']);
        }

        return parent::delete($user);
    }

    /**
     * Determine whether the user can restore the user.
     */
    public function restore(?User $user = null, ?Model $model = null): bool
    {
        return $this->delete($user, $model);
    }

    /**
     * Determine whether the user can permanently delete the user.
     */
    public function forceDelete(?User $user = null, ?Model $model = null): bool
    {
        return $this->delete($user, $model);
    }
}
