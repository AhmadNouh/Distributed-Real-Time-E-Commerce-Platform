<?php

namespace App\Modules\Products\Providers;

use App\Modules\Products\Contracts\ProductService;
use App\Modules\Products\Services\ProductServiceImpl;
use Illuminate\Support\ServiceProvider;

class ProductServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ProductService::class, ProductServiceImpl::class);
    }
}
