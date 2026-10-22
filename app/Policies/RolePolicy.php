<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class RolePolicy extends BasePolicy
{
    /**
     * Determine whether the user can update the role.
     * Admin roles can only be updated by Admin users.
     */
    public function update(?User $user = null, ?Model $model = null): bool
    {
        if ($model instanceof Role && $model->name === 'Admin') {
            return $user->hasRole(['Admin']);
        }

        return parent::update($user);
    }

    /**
     * Determine whether the user can delete the role.
     * Admin roles cannot be deleted.
     */
    public function delete(?User $user = null, ?Model $model = null): bool
    {
        if ($model instanceof Role && $model->name === 'Admin') {
            return false;
        }

        return parent::delete($user);
    }

    /**
     * Determine whether the user can restore the role.
     */
    public function restore(?User $user = null, ?Model $model = null): bool
    {
        return $this->delete($user, $model);
    }

    /**
     * Determine whether the user can permanently delete the role.
     */
    public function forceDelete(?User $user = null, ?Model $model = null): bool
    {
        return $this->delete($user, $model);
    }
}
