<?php
namespace App\Modules\Notifications\Listeners;
use App\Modules\Orders\Events\OrderCreated;
use App\Modules\Orders\Events\OrderStatusChanged;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class SendOrderNotification implements ShouldQueue
{
    public function handle($event)
    {
        if ($event instanceof OrderCreated) {
            $user = $event->order->user;
            \App\Modules\Notifications\Services\NotificationServiceImpl::notify($user, 'Order created: #'.$event->order->id);
        } elseif ($event instanceof OrderStatusChanged) {
            $user = $event->order->user;
            \App\Modules\Notifications\Services\NotificationServiceImpl::notify($user, 'Order #'.$event->order->id.' status changed to '.$event->order->status);
        }
    }
}
