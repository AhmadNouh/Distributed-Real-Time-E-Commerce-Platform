<?php

namespace App\Modules\Orders\Providers;

use App\Modules\Orders\Contracts\OrderService;
use App\Modules\Orders\Services\OrderServiceImpl;
use Illuminate\Support\ServiceProvider;

class OrderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // The warehouse client is a shared concern, bound by InventoryServiceProvider
        // (App\Providers\InventoryServiceProvider -> App\Shared\Contracts\InventoryClient).
        $this->app->bind(OrderService::class, OrderServiceImpl::class);
    }
}
