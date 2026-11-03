<?php

namespace App\Shared\Contracts;

/**
 * Port for the standalone warehouse (inventory) service.
 *
 * Both the Products module (to display available quantity) and the Orders
 * module (to reserve/release stock) depend on this shared port, never on each
 * other. The concrete implementation is chosen by InventoryServiceProvider:
 *
 *   INVENTORY_DRIVER=fake  -> App\Shared\Inventory\FakeInventoryClient
 *   INVENTORY_DRIVER=grpc  -> App\Shared\Inventory\GrpcInventoryClient
 */
interface InventoryClient
{
    /**
     * Sellable quantity (physical quantity minus reservations).
     * Returns 0 when the warehouse does not know the product.
     *
     * @throws \App\Shared\Exceptions\WarehouseUnavailableException
     */
    public function getStock(int $productId): int;

    /**
     * Reserves every line of an order in one atomic call.
     *
     * @param  array<int, array{product_id: int, quantity: int}>  $items
     *
     * @throws \App\Shared\Exceptions\WarehouseUnavailableException
     */
    public function reserve(string $orderUuid, array $items): ReservationResult;

    /**
     * Returns the reserved quantities of an order to the pool (compensation).
     *
     * @throws \App\Shared\Exceptions\WarehouseUnavailableException
     */
    public function release(string $orderUuid): bool;
}
