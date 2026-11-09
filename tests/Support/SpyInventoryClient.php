<?php

namespace Tests\Support;

use App\Shared\Contracts\InventoryClient;
use App\Shared\Contracts\ReservationResult;
use Throwable;

/**
 * Test double for the warehouse port.
 *
 * Records every call so tests can assert that the application reserved the
 * whole order in one go and released it when persisting failed.
 */
class SpyInventoryClient implements InventoryClient
{
    /** @var array<string, array<int, array{product_id: int, quantity: int}>> */
    public array $reserveCalls = [];

    /** @var array<int, string> */
    public array $released = [];

    /** @var array<int, int> */
    public array $stockCalls = [];

    /** @var array<int, int> */
    public array $stock = [];

    public ?Throwable $reserveError = null;

    public ?Throwable $stockError = null;

    public ?Throwable $releaseError = null;

    public function __construct(
        private bool $approved = true,
        private string $reason = 'OK',
        private array $shortages = [],
    ) {}

    public function approve(bool $approved, string $reason = 'OK', array $shortages = []): self
    {
        $this->approved = $approved;
        $this->reason = $reason;
        $this->shortages = $shortages;

        return $this;
    }

    public function getStock(int $productId): int
    {
        $this->stockCalls[] = $productId;

        if ($this->stockError) {
            throw $this->stockError;
        }

        return $this->stock[$productId] ?? 0;
    }

    public function reserve(string $orderUuid, array $items): ReservationResult
    {
        $this->reserveCalls[$orderUuid] = $items;

        if ($this->reserveError) {
            throw $this->reserveError;
        }

        return new ReservationResult($this->approved, $this->reason, $this->shortages, $orderUuid);
    }

    public function release(string $orderUuid): bool
    {
        $this->released[] = $orderUuid;

        if ($this->releaseError) {
            throw $this->releaseError;
        }

        return true;
    }
}
