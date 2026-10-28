<?php

namespace App\Policies;

class OrderItemPolicy extends BasePolicy
{
    /**
     * {@inheritDoc}
     */
    protected function resourceName(): string
    {
        return 'orders';
    }
}
