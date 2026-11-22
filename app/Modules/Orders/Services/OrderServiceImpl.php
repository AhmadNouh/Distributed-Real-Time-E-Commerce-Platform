<?php

namespace App\Modules\Orders\Services;

use App\Modules\Orders\Contracts\OrderService;
use App\Modules\Orders\Events\OrderCreated;
use App\Modules\Orders\Events\OrderCreatedRealTime;
use App\Modules\Orders\Events\OrderStatusChanged;
use App\Modules\Orders\Events\OrderStatusUpdated;
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

        $reservation = $this->inventory->reserve($orderUuid, $reservationItems);

        if (! $reservation->approved) {
            throw $this->stockRejection($reservation);
        }

        try {
           $order = DB::transaction(function () use ($user, $orderUuid, $lines, $total) {
                $order = Order::create([
                    'user_id' => $user->id,
                    'status'  => 'pending',
                    'total'   => $total,
                    'uuid'    => $orderUuid,
                ]);

                $createdItems = $order->items()->createMany($lines);

                $order->setRelation('items', $createdItems);

                return $order;
            });
        } 
        catch (Throwable $e) {
            if (Order::where('uuid', $orderUuid)->exists()) {
                Log::warning('Order already stored, keeping its warehouse reservation', [
                    'order_uuid' => $orderUuid,
                ]);
            } else {
                $this->releaseQuietly($orderUuid, $e);
            }

            throw $e;
        }
        
        try {
        //    OrderCreated::dispatch($order);
            OrderCreatedRealTime::dispatch($order);
        } 
        
        catch (Throwable $e) {
            Log::error('Failed to dispatch OrderCreated event', [
                'order_uuid' => $order->uuid,
                'error' => $e->getMessage(),
            ]);
        }
    
        return $order;
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


        if ($status === 'cancelled') {
            $this->releaseQuietly($order->uuid, null);
        }

        try {
            OrderStatusUpdated::dispatch($order);
        } 
        
        catch (Throwable $e) {
            Log::error('Failed to dispatch OrderStatusUpdated event', [
                'order_uuid' => $order->uuid,
                'error' => $e->getMessage(),
            ]);
        }

        return $order;
    }


    public function find(int $id): ?Order
    {
        return Order::with('items')->find($id);
    }

    public function paginateFor(object $user, int $perPage = 15): LengthAwarePaginator
    {
        $query = $user->isAdmin() ? Order::query() : Order::where('user_id', $user->id);

        return $query->with('items.product')->paginate($perPage);
    }

   
    public function findAllRecent(int $limit): \Illuminate\Database\Eloquent\Collection
    {
        return Order::latest()->take($limit)->get();
    }

    public function findWithEager(int $limit): \Illuminate\Database\Eloquent\Collection
    {
        return Order::with(['items.product'])->latest()->take($limit)->get();
    }

    
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
