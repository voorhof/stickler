<?php

namespace App\Exceptions;

use Exception;

/**
 * Exception thrown when a webhook signature verification fails.
 */
class InvalidWebhookSignatureException extends Exception
{
    /**
     * Create a new InvalidWebhookSignatureException instance.
     */
    public function __construct()
    {
        parent::__construct('Invalid webhook signature.');
    }
}
