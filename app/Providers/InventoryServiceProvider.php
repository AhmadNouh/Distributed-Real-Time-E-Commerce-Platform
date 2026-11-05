<?php

namespace App\Providers;

use App\Shared\Contracts\InventoryClient;
use App\Shared\Inventory\FakeInventoryClient;
use App\Shared\Inventory\GrpcInventoryClient;
use Illuminate\Support\ServiceProvider;

class InventoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(InventoryClient::class, function () {
            return match (config('inventory.driver', 'fake')) {
                'grpc' => new GrpcInventoryClient(),
                default => new FakeInventoryClient(),
            };
        });
    }
}
