<?php

namespace App\Modules\Orders\Listeners;

use App\Modules\Orders\Events\OrderCreated;
use App\Modules\Orders\Events\OrderCreatedRealTime;
use App\Modules\Orders\Mails\OrderConfirmationMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendOrderConfirmationNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 120;

    public $tries = 3; 

    public $backoff = 3; // الوقت بين كل محاولة و لي بعدها

   
    // public function handle(OrderCreated $event): void
    // {
    //     //throw new \Exception("خطأ متعمد لاختبار تحويل الرسالة إلى DLQ");
        
    //     Mail::to($event->order->user->email)->send(new OrderConfirmationMail($event->order));
    // }


    //  public function failed(OrderCreated $event, \Throwable $exception): void {
    //      \Log::error("فشل إرسال بريد التأكيد للطلب رقم {$event->order->id}: " . $exception->getMessage()); 
    // }

      public function handle(OrderCreatedRealTime $event): void
    {
        //throw new \Exception("خطأ متعمد لاختبار تحويل الرسالة إلى DLQ");
        
        Mail::to($event->order->user->email)->send(new OrderConfirmationMail($event->order));
    }

    public function failed(OrderCreatedRealTime $event, \Throwable $exception): void {
         \Log::error("فشل إرسال بريد التأكيد للطلب رقم {$event->order->id}: " . $exception->getMessage()); 
    }
}
