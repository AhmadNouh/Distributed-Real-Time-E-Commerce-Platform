<?php

namespace App\Shared\Exceptions;

use Exception;

/**
 * The warehouse approved nothing because at least one line has not enough
 * stock (or is unknown to the warehouse). Mapped to HTTP 409.
 */
class InsufficientStockException extends Exception
{
    /**
     * @param  array<int, array{product_id: int, requested: int, available: int, name?: string}>  $shortages
     */
    public function __construct(
        public readonly array $shortages = [],
        ?string $message = null,
    ) {
        parent::__construct(
            $message ?: 'One or more products do not have enough stock.',
            409,
        );
    }
}
