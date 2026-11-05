<?php

namespace App\Providers;

use App\Modules\Notifications\Listeners\SendOrderNotification;
use App\Modules\Orders\Events\OrderCreated;
use App\Modules\Orders\Events\OrderStatusChanged;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom([
            app_path('Modules/Users/Database/Migrations'),
            app_path('Modules/Products/Database/Migrations'),
            app_path('Modules/Orders/Database/Migrations'),
            app_path('Modules/Notifications/Database/Migrations'),
        ]);

        Event::listen(OrderCreated::class, SendOrderNotification::class);
        Event::listen(OrderStatusChanged::class, SendOrderNotification::class);
    }
}
