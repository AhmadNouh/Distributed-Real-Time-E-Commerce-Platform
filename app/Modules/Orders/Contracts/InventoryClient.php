<?php
namespace App\Modules\Orders\Contracts;
interface InventoryClient {
    public function getStock(int $productId): int;
    public function reserve(string $orderUuid, array $items): ReservationResult;
    public function confirm(string $orderUuid): bool;
    public function release(string $orderUuid): bool;
}
