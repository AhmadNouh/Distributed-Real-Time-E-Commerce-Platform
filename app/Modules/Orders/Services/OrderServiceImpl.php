<?php

namespace App\Modules\Orders\Services;

use App\Modules\Orders\Contracts\OrderService;
use App\Modules\Orders\Events\OrderCreated;
use App\Modules\Orders\Events\OrderStatusChanged;
use App\Modules\Orders\Models\Order;
use App\Modules\Products\Contracts\ProductService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class OrderServiceImpl implements OrderService
{
    private const TRANSITIONS = [
        'pending' => ['confirmed', 'cancelled'],
        'confirmed' => ['shipped', 'cancelled'],
        'shipped' => ['delivered'],
    ];

    public function __construct(
        private readonly ProductService $products
    ) {}

    public function createFor(object $user, array $items, ?string $uuid = null): Order
    {
        return DB::transaction(function () use ($user, $items, $uuid) {
            $order = Order::create([
                'user_id' => $user->id,
                'status' => 'pending',
                'total' => 0,
                'uuid' => $uuid ?: (string) Str::uuid(),
            ]);

            $total = 0;

            foreach ($items as $item) {
                $product = $this->products->find((int) $item['product_id']);

                if (! $product) {
                    throw ValidationException::withMessages([
                        'items' => "Product {$item['product_id']} does not exist.",
                    ]);
                }

                $quantity = (int) $item['quantity'];
                $price = (float) $product->price;

                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $price,
                ]);

                $total += $price * $quantity;
            }

            $order->update(['total' => $total]);

            event(new OrderCreated($order));

            return $order->load('items');
        });
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

        return $order;
    }
}
