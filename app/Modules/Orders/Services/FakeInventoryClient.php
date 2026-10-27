<?php
namespace App\Modules\Orders\Services;

use App\Modules\Orders\Contracts\InventoryClient;
use App\Modules\Orders\Contracts\ReservationResult;
use App\Shared\Exceptions\WarehouseUnavailableException;

class FakeInventoryClient implements InventoryClient
{
    public function getStock(int $productId): int
    {
        try {
            $stock = config('inventory.fake.stock', []);
            return $stock[$productId] ?? 100;
        } catch (\Exception $e) {
            throw new WarehouseUnavailableException();
        }
    }

    public function reserve(string $orderUuid, array $items): ReservationResult
    {
        try {
            $stockConfig = config('inventory.fake.stock', []);
            foreach ($items as $item) {
                $qty = $item['quantity'] ?? 1;
                $available = $stockConfig[$item['product_id']] ?? 100;
                if ($available < $qty) {
                    return new ReservationResult(false, 'Insufficient stock', $available);
                }
            }
            return new ReservationResult(true, null, 0);
        } catch (\Exception $e) {
            throw new WarehouseUnavailableException();
        }
    }

    public function confirm(string $orderUuid): bool { return true; }
    public function release(string $orderUuid): bool { return true; }
}
