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
            public function via($notifiable) { return ['database','mail']; }
            public function toMail($notifiable) { return (new \Illuminate\Notifications\Messages\MailMessage)->line($this->message); }
            public function toArray($notifiable) { return ['message'=>$this->message]; }
        });
        return $user->notifications()->create([
            'id'=>\Illuminate\Support\Str::uuid(),
            'type'=>get_class(new class($message) extends \Illuminate\Notifications\Notification {}),
            'notifiable_type'=>'App\Modules\Users\Models\User',
            'notifiable_id'=>$user->id,
            'data'=>json_encode(['message'=>$message]),
        ]);
    }
}
