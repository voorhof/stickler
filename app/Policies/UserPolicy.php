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
    public function update(User $user, ?Model $model = null): bool
    {
        if ($model instanceof User && $model->hasRole('Admin')) {
            return $user->hasAnyRole(['Admin']);
        }

        return parent::update($user);
    }

    /**
     * Determine whether the user can delete the user.
     * Admin users can only be deleted by Admin users, and the last remaining admin cannot be deleted.
     */
    public function delete(User $user, ?Model $model = null): bool
    {
        if ($model instanceof User && $model->hasRole('Admin')) {
            if (! $model->trashed() && User::role('Admin')->withoutTrashed()->where('id', '!=', $model->id)->count() === 0) {
                return false;
            }

            return $user->hasAnyRole(['Admin']);
        }

        return parent::delete($user);
    }

    /**
     * Determine whether the user can restore the user.
     */
    public function restore(User $user, ?Model $model = null): bool
    {
        if ($model instanceof User && $model->hasRole('Admin')) {
            return $user->hasAnyRole(['Admin']);
        }

        return parent::restore($user);
    }

    /**
     * Determine whether the user can permanently delete the user.
     */
    public function forceDelete(User $user, ?Model $model = null): bool
    {
        if ($model instanceof User && $model->hasRole('Admin')) {
            if (User::role('Admin')->where('id', '!=', $model->id)->count() === 0) {
                return false;
            }

            return $user->hasAnyRole(['Admin']);
        }

        return parent::forceDelete($user);
    }
}
