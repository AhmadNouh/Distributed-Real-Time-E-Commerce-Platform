<?php

namespace App\Modules\Orders\Services;

use App\Modules\Orders\Contracts\OrderService;
use App\Modules\Orders\Events\OrderCreated;
use App\Modules\Orders\Events\OrderStatusChanged;
use App\Modules\Orders\Models\Order;
use App\Modules\Products\Contracts\ProductService;
use App\Shared\Contracts\InventoryClient;
use App\Shared\Exceptions\InsufficientStockException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Throwable;

class OrderServiceImpl implements OrderService
{
    private const TRANSITIONS = [
        'pending' => ['confirmed', 'cancelled'],
        'confirmed' => ['shipped', 'cancelled'],
        'shipped' => ['delivered'],
    ];

    public function __construct(
        private readonly ProductService $products,
        private readonly InventoryClient $inventory,
    ) {}

    /**
     * Order creation flow (requirement 2):
     *
     *   1. resolve every product through the Products module (read only)
     *   2. ask the warehouse to reserve ALL lines in a single gRPC call
     *   3. only if the warehouse approves, persist the order
     *   4. if persisting fails, release the reservation (compensation)
     *
     * Nothing is written to the orders tables before step 3 succeeds, so a
     * rejected reservation can never leave a half-created order behind.
     */
    public function createFor(object $user, array $items, ?string $uuid = null): Order
    {
        $orderUuid = $uuid ?: (string) Str::uuid();

        $lines = [];
        $reservationItems = [];
        $total = 0.0;

        foreach ($items as $item) {
            $product = $this->products->find((int) $item['product_id']);

            if (! $product) {
                throw ValidationException::withMessages([
                    'items' => "Product {$item['product_id']} does not exist.",
                ]);
            }

            $quantity = (int) $item['quantity'];
            $price = (float) $product->price;

            $lines[] = [
                'product_id' => $product->id,
                'quantity' => $quantity,
                'price' => $price,
            ];

            $reservationItems[] = [
                'product_id' => $product->id,
                'quantity' => $quantity,
            ];

            $total += $price * $quantity;
        }

        // One call for the whole order: all lines are reserved or none is.
        $reservation = $this->inventory->reserve($orderUuid, $reservationItems);

        if (! $reservation->approved) {
            throw $this->stockRejection($reservation);
        }

        try {
            return DB::transaction(function () use ($user, $orderUuid, $lines, $total) {
                $order = Order::create([
                    'user_id' => $user->id,
                    'status' => 'pending',
                    'total' => $total,
                    'uuid' => $orderUuid,
                ]);

                foreach ($lines as $line) {
                    $order->items()->create($line);
                }

                event(new OrderCreated($order));

                return $order->load('items');
            });
        } catch (Throwable $e) {
            // The stock is already reserved at this point: give it back before
            // surfacing the failure, otherwise the quantity would stay held by
            // an order that does not exist.
            //
            // Exception: if this uuid already belongs to a stored order, the
            // reservation is theirs (typical client retry) and must NOT be
            // released, otherwise a real order would lose its stock.
            if (Order::where('uuid', $orderUuid)->exists()) {
                Log::warning('Order already stored, keeping its warehouse reservation', [
                    'order_uuid' => $orderUuid,
                ]);
            } else {
                $this->releaseQuietly($orderUuid, $e);
            }

            throw $e;
        }
    }

    public function find(int $id): ?Order
    {
        return Order::with('items')->find($id);
    }

    public function paginateFor(object $user, int $perPage = 15): LengthAwarePaginator
    {
        $query = $user->isAdmin() ? Order::query() : Order::where('user_id', $user->id);

        return $query->with('items')->paginate($perPage);
    }

    public function changeStatus(int $orderId, string $status): Order
    {
        $order = Order::find($orderId);

        if (! $order) {
            throw (new ModelNotFoundException())->setModel(Order::class, [$orderId]);
        }

        $allowed = self::TRANSITIONS[$order->status] ?? [];

        if (! in_array($status, $allowed, true)) {
            throw new InvalidArgumentException(
                "Invalid transition from {$order->status} to {$status}."
            );
        }

        $order->update(['status' => $status]);

        event(new OrderStatusChanged($order));

        if ($status === 'cancelled') {
            // Cancelling means the goods go back on the shelf.
            $this->releaseQuietly($order->uuid, null);
        }

        return $order;
    }

    /**
     * Turns a rejected reservation into an exception with a message a customer
     * can actually read (product name, requested vs available).
     */
    private function stockRejection(\App\Shared\Contracts\ReservationResult $reservation): InsufficientStockException
    {
        $shortages = [];

        foreach ($reservation->shortages() as $shortage) {
            $product = $this->products->find((int) $shortage['product_id']);

            $shortages[] = $shortage + [
                'name' => $product?->name ?? 'Product #' . $shortage['product_id'],
            ];
        }

        $parts = array_map(
            fn (array $s) => sprintf('%s: requested %d, available %d', $s['name'], $s['requested'], $s['available']),
            $shortages,
        );

        $message = $reservation->reason === 'PRODUCT_NOT_FOUND'
            ? 'One or more products are not tracked by the warehouse.'
            : 'Insufficient stock for this order.';

        if ($parts !== []) {
            $message .= ' ' . implode('; ', $parts) . '.';
        }

        return new InsufficientStockException($shortages, $message);
    }

    /**
     * Compensation helper: never lets a release failure hide the original error.
     */
    private function releaseQuietly(string $orderUuid, ?Throwable $original): void
    {
        try {
            $this->inventory->release($orderUuid);
        } catch (Throwable $releaseError) {
            Log::critical('Failed to release warehouse reservation', [
                'order_uuid' => $orderUuid,
                'release_error' => $releaseError->getMessage(),
                'original_error' => $original?->getMessage(),
            ]);
        }
    }
}
