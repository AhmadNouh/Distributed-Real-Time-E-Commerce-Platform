<?php

namespace App\Modules\Orders\Mails;

use App\Modules\Orders\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderConfirmationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public Order $order)
    {
        //
    }

    public function build() {
         return $this->subject("تأكيد استلام الطلب رقم #{$this->order->id}")
          ->html("<h1>مرحباً {$this->order->user->name}</h1><p>تم استلام طلبك رقم #{$this->order->id} بنجاح ومجموعه: {$this->order->total}</p>");
    }
   
   
  
}
