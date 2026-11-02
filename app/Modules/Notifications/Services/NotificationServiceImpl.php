<?php

namespace App\Modules\Notifications\Services;

use App\Modules\Notifications\Contracts\NotificationService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class NotificationServiceImpl implements NotificationService
{
    public function notify(object $user, string $message): void
    {
        $user->notify(new class($message) extends Notification {
            public function __construct(private readonly string $message) {}

            public function via($notifiable): array
            {
                return ['database'];
            }

            public function toArray($notifiable): array
            {
                return ['message' => $this->message];
            }

            public function databaseType($notifiable): string
            {
                return 'order';
            }
        });
    }

    public function paginate(object $user, bool $unreadOnly = false, int $perPage = 15): LengthAwarePaginator
    {
        $query = $unreadOnly ? $user->unreadNotifications() : $user->notifications();

        return $query->paginate($perPage);
    }

    public function unreadCount(object $user): int
    {
        return $user->unreadNotifications()->count();
    }

    public function markAsRead(object $user, string $id): ?DatabaseNotification
    {
        $notification = $user->notifications()->where('id', $id)->first();

        if (! $notification) {
            return null;
        }

        if (is_null($notification->read_at)) {
            $notification->markAsRead();
        }

        return $notification;
    }

    public function markAllAsRead(object $user): void
    {
        $user->unreadNotifications()->update(['read_at' => now()]);
    }

    public function delete(object $user, string $id): bool
    {
        $notification = $user->notifications()->where('id', $id)->first();

        if (! $notification) {
            return false;
        }

        return (bool) $notification->delete();
    }
}
