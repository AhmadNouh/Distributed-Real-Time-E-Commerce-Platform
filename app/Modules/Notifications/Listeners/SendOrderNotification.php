<?php

namespace App\Modules\Notifications\Listeners;

use App\Modules\Notifications\Contracts\NotificationService;
use App\Modules\Orders\Events\OrderCreated;
use App\Modules\Orders\Events\OrderStatusChanged;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendOrderNotification implements ShouldQueue
{
    public function __construct(
        private readonly NotificationService $notifications
    ) {}

    public function handle(OrderCreated|OrderStatusChanged $event): void
    {
        if ($event instanceof OrderCreated) {
            $this->notifications->notify(
                $event->order->user,
                'Order created: #' . $event->order->id
            );

            return;
        }

        $this->notifications->notify(
            $event->order->user,
            'Order #' . $event->order->id . ' status changed to ' . $event->order->status
        );
    }
}
