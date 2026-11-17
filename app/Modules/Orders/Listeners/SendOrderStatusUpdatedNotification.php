<?php

namespace App\Modules\Orders\Listeners;

use App\Modules\Orders\Events\OrderStatusUpdated;
use App\Modules\Orders\Mails\OrderStatusUpdatedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendOrderStatusUpdatedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    
    public string $queue = 'orders_queue';

   
    public int $tries = 3;

    
    public function handle(OrderStatusUpdated $event): void
    {

        Mail::to($event->order->user->email)->send(new OrderStatusUpdatedMail($event->order));
    }
}