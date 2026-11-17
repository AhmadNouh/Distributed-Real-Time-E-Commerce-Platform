<?php

namespace App\Modules\Orders\Mails;

use App\Modules\Orders\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function build()
    {
        $statusLabel = $this->getStatusLabel($this->order->status);

        $htmlContent = "
            <div dir='rtl' style='font-family: Tahoma, Arial, sans-serif; background-color: #f4f6f9; padding: 30px; text-align: right;'>
                <div style='max-width: 550px; margin: 0 auto; background-color: #ffffff; border-radius: 10px; padding: 25px; border: 1px solid #e2e8f0;'>
                    <h2 style='color: #1e293b; margin-top: 0;'>تحديث بخصوص طلبك #{$this->order->id}</h2>
                    <p style='color: #475569; font-size: 16px; line-height: 1.6;'>
                        مرحباً،<br>
                        نود إعلامك بأنه تم تغيير حالة طلبك رقم <strong>#{$this->order->id}</strong> إلى:
                    </p>
                    <div style='background-color: #ecfdf5; border-right: 4px solid #10b981; padding: 12px 18px; margin: 20px 0; border-radius: 6px;'>
                        <span style='color: #065f46; font-weight: bold; font-size: 17px;'>{$statusLabel}</span>
                    </div>
                    <p style='color: #94a3b8; font-size: 13px; margin-bottom: 0;'>
                        شكراً لتسوقك معنا!
                    </p>
                </div>
            </div>
        ";

        return $this->subject("تحديث حالة الطلب #{$this->order->id}")
                    ->html($htmlContent);
    }

  
    private function getStatusLabel(string $status): string
    {
        return match ($status) {
            'pending'    => 'قيد الانتظار',
            'processing' => 'جاري التجهيز',
            'shipped'    => 'تم الشحن',
            'delivered'  => 'تم التوصيل',
            'cancelled'  => 'ملغي',
            default      => $status,
        };
    }
}