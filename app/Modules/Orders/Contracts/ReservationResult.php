<?php

namespace App\Modules\Orders\Contracts;

class ReservationResult
{
    public function __construct(
        public readonly bool $approved,
        public readonly ?string $reason = null,
        public readonly int $available = 0,
    ) {}
}
