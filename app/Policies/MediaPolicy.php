<?php

namespace App\Policies;

class MediaPolicy extends BasePolicy
{
    /**
     * {@inheritDoc}
     */
    protected function resourceName(): string
    {
        return 'media';
    }
}
