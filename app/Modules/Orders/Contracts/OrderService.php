<?php

namespace App\Modules\Orders\Contracts;

use App\Modules\Orders\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Public contract of the Orders module.
 *
 * It orchestrates other modules through their contracts:
 * Products (prices) and Notifications (through Order events).
 */
interface OrderService
{
    public function createFor(object $user, array $items, ?string $uuid = null): Order;

    public function find(int $id): ?Order;

    public function paginateFor(object $user, int $perPage = 15): LengthAwarePaginator;

    /**
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \InvalidArgumentException when the status transition is not allowed
     */
    public function changeStatus(int $orderId, string $status): Order;
}
