<?php

namespace App\Modules\Orders\Contracts;

use App\Modules\Orders\Contracts\ReservationResult;

/**
 * Port used by the Orders module to talk to an inventory/warehouse service.
 * The implementation is chosen at runtime (see OrderServiceProvider),
 * so the module can switch from the fake client to a real one later.
 */
interface InventoryClient
{
    public function getStock(int $productId): int;

    public function reserve(string $orderUuid, array $items): ReservationResult;

    public function confirm(string $orderUuid): bool;

    public function release(string $orderUuid): bool;
}
