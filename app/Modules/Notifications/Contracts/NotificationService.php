<?php

namespace App\Modules\Notifications\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Notifications\DatabaseNotification;

/**
 * Public contract of the Notifications module.
 *
 * Orders (and any other module) sends notifications through this interface,
 * without knowing how they are stored or delivered.
 */
interface NotificationService
{
    public function notify(object $user, string $message): void;

    public function paginate(object $user, bool $unreadOnly = false, int $perPage = 15): LengthAwarePaginator;

    public function unreadCount(object $user): int;

    public function markAsRead(object $user, string $id): ?DatabaseNotification;

    public function markAllAsRead(object $user): void;

    public function delete(object $user, string $id): bool;
}
