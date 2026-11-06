<?php

namespace App\Enums;

/**
 * Result of processing a payment webhook.
 */
enum WebhookResult: string
{
    case Success = 'success';
    case BadPayload = 'bad_payload';
    case NotFound = 'not_found';
    case Error = 'error';

    /**
     * Get the HTTP status code for this result.
     */
    public function httpStatusCode(): int
    {
        return match ($this) {
            self::Success => 200,
            self::BadPayload => 400,
            self::NotFound => 404,
            self::Error => 500,
        };
    }
}
