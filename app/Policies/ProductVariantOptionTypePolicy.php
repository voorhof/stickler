<?php

namespace App\Policies;

class ProductVariantOptionTypePolicy extends BasePolicy
{
    /**
     * {@inheritDoc}
     */
    protected function resourceName(): string
    {
        return 'products';
    }
}
