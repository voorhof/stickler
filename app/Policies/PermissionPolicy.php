<?php

namespace App\Policies;

class PermissionPolicy extends BasePolicy
{
    /**
     * {@inheritDoc}
     */
    protected function resourceName(): string
    {
        return 'roles';
    }
}
