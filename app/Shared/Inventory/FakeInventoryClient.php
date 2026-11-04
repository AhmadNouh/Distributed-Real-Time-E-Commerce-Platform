<?php

namespace App\Shared\Inventory;

use App\Shared\Contracts\InventoryClient;
use App\Shared\Contracts\ReservationResult;
use App\Shared\Exceptions\WarehouseUnavailableException;

/**
 * In-process implementation used by tests and by INVENTORY_DRIVER=fake.
 * It keeps reservations in memory, so it is NOT shared between requests:
 * it exists only to keep unit tests fast and network-free.
 */
class FakeInventoryClient implements InventoryClient
{
    /** @var array<int, int> */
    private array $stock;

    /** @var array<string, array<int, array{product_id: int, quantity: int}>> */
    private array $reservations = [];

    public function __construct(?array $stock = null)
    {
        $this->stock = $stock ?? config('inventory.fake.stock', []);
    }

    public function getStock(int $productId): int
    {
        return (int) ($this->stock[$productId] ?? 100);
    }

    public function setStock(int $productId, int $quantity): bool
{
        $this->stock[$productId] = $quantity;
    
        return true;
    }

    public function reserve(string $orderUuid, array $items): ReservationResult
    {
        if (isset($this->reservations[$orderUuid])) {
            return new ReservationResult(true, 'OK', [], $orderUuid);
        }

        try {
            $shortages = [];

            foreach ($items as $item) {
                $productId = (int) $item['product_id'];
                $quantity = (int) $item['quantity'];
                $available = (int) ($this->stock[$productId] ?? 100);

                if ($available < $quantity) {
                    $shortages[] = [
                        'product_id' => $productId,
                        'requested' => $quantity,
                        'available' => $available,
                    ];
                }
            }

            if ($shortages !== []) {
                return new ReservationResult(false, 'INSUFFICIENT_STOCK', $shortages);
            }

            foreach ($items as $item) {
                $productId = (int) $item['product_id'];
                $this->stock[$productId] = (int) ($this->stock[$productId] ?? 100) - (int) $item['quantity'];
            }

            $this->reservations[$orderUuid] = $items;

            return new ReservationResult(true, 'OK', [], $orderUuid);
        } catch (\Throwable $e) {
            throw new WarehouseUnavailableException('Warehouse service unavailable: ' . $e->getMessage());
        }
    }

    public function release(string $orderUuid): bool
    {
        if (! isset($this->reservations[$orderUuid])) {
            return false;
        }

        foreach ($this->reservations[$orderUuid] as $item) {
            $productId = (int) $item['product_id'];
            $this->stock[$productId] = (int) ($this->stock[$productId] ?? 100) + (int) $item['quantity'];
        }

        unset($this->reservations[$orderUuid]);

        return true;
    }
}
