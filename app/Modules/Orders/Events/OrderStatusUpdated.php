<?php

namespace App\Modules\Orders\Events;

use App\Modules\Orders\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }


    public function broadcastOn(): array
    {
        return [
            new Channel('orders.' . $this->order->id),
        ];
    }

  
    public function broadcastAs(): string
    {
        return 'order.status.updated';
    }

  
    public function broadcastWith(): array
    {
        return [
            'order_id'   => $this->order->id,
            'status'     => $this->order->status,
            'updated_at' => $this->order->updated_at?->toIso8601String() ?? now()->toIso8601String(),
        ];
    }
}