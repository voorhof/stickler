<?php

namespace App\Policies;

class ProductVariantOptionPolicy extends BasePolicy
{
    /**
     * {@inheritDoc}
     */
    protected function resourceName(): string
    {
        return 'products';
    }
}
