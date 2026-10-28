<?php
namespace App\Modules\Notifications\Services;
interface NotificationService
{
    public static function notify($user, string $message);
}
