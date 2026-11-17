<?php

namespace App\Providers;


use App\Modules\Orders\Events\OrderCreated;
use App\Modules\Orders\Events\OrderStatusUpdated;
use App\Modules\Orders\Listeners\SendOrderConfirmationNotification;
use App\Modules\Orders\Listeners\SendOrderStatusUpdatedNotification;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom([
            app_path('Modules/Users/Database/Migrations'),
            app_path('Modules/Products/Database/Migrations'),
            app_path('Modules/Orders/Database/Migrations'),
            app_path('Modules/Notifications/Database/Migrations'),
        ]);

        
        Event::listen(OrderCreated::class,SendOrderConfirmationNotification::class);
        Event::listen(OrderStatusUpdated::class,SendOrderStatusUpdatedNotification::class);
    }
}
