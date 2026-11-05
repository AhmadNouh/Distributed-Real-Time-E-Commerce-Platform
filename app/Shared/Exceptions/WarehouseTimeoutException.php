<?php

namespace App\Shared\Exceptions;

/**
 * The warehouse service did not answer before the configured deadline.
 * Treated as "warehouse unavailable" (HTTP 503) but with its own message so
 * the user knows the request was cut short instead of hanging.
 */
class WarehouseTimeoutException extends WarehouseUnavailableException
{
    public function __construct(?string $message = null)
    {
        parent::__construct($message ?: 'The warehouse service did not respond in time. Please try again.');
    }
}
