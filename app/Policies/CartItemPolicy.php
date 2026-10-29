<?php

namespace App\Policies;

class CartItemPolicy extends BasePolicy
{
    /**
     * {@inheritDoc}
     */
    protected function resourceName(): string
    {
        return 'carts';
    }
}
