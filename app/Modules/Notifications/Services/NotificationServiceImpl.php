<?php

namespace App\Modules\Notifications\Services;

use Illuminate\Notifications\Notification;

class NotificationServiceImpl implements NotificationService
{
    public static function notify($user, string $message)
    {
        $user->notify(new class($message) extends Notification {
            public $message;
            public function __construct($m) { $this->message = $m; }
            public function via($notifiable) { return ['database']; }
            public function toArray($notifiable) { return ['message' => $this->message]; }
        });
    }
}