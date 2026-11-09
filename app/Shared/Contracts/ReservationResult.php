<?php

namespace App\Shared\Contracts;

/**
 * Outcome of an all-or-nothing stock reservation.
 */
class ReservationResult
{
    /**
     * @param  array<int, array{product_id: int, requested: int, available: int}>  $shortages
     */
    public function __construct(
        public readonly bool $approved,
        public readonly ?string $reason = null,
        public readonly array $shortages = [],
        public readonly ?string $reservationId = null,
    ) {}

    public function isOutOfStock(): bool
    {
        return $this->reason === 'INSUFFICIENT_STOCK';
    }

    public function isProductMissing(): bool
    {
        return $this->reason === 'PRODUCT_NOT_FOUND';
    }

    /**
     * @return array<int, array{product_id: int, requested: int, available: int}>
     */
    public function shortages(): array
    {
        return $this->shortages;
    }
}
