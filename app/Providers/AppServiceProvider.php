<?php

namespace App\Providers;

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
    }
}