<?php

namespace App\Modules\Orders\Providers;

use App\Modules\Orders\Contracts\InventoryClient;
use App\Modules\Orders\Contracts\OrderService;
use App\Modules\Orders\Services\FakeInventoryClient;
use App\Modules\Orders\Services\OrderServiceImpl;
use Illuminate\Support\ServiceProvider;

class OrderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(InventoryClient::class, function () {
            return match (config('inventory.driver', 'fake')) {
                default => new FakeInventoryClient(),
            };
        });

        $this->app->bind(OrderService::class, OrderServiceImpl::class);
    }
}
