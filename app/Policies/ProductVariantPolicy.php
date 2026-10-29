<?php

namespace App\Policies;

class ProductVariantPolicy extends BasePolicy
{
    /**
     * {@inheritDoc}
     */
    protected function resourceName(): string
    {
        return 'products';
    }
}
