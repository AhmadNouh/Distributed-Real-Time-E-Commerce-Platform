<?php

namespace App\Modules\Notifications\Providers;

use App\Modules\Notifications\Contracts\NotificationService;
use App\Modules\Notifications\Services\NotificationServiceImpl;
use Illuminate\Support\ServiceProvider;

class NotificationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(NotificationService::class, NotificationServiceImpl::class);
    }
}
