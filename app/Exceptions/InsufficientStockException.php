<?php

namespace App\Exceptions;

use Exception;

/**
 * Exception thrown when there is insufficient stock for a product or variant.
 */
class InsufficientStockException extends Exception
{
    /**
     * Create a new InsufficientStockException instance.
     */
    public function __construct(string $message = '')
    {
        parent::__construct($message ?: __('Insufficient stock for this product'));
    }
}
