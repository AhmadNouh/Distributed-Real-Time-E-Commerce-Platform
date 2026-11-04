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
   
    public function setStock(int $productId, int $quantity): bool;
    
    public function getStock(int $productId): int;

    
    public function reserve(string $orderUuid, array $items): ReservationResult;

  
    public function release(string $orderUuid): bool;
}
