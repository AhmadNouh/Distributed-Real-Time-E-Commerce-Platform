<?php

use App\Modules\Notifications\Providers\NotificationServiceProvider;
use App\Modules\Orders\Providers\OrderServiceProvider;
use App\Modules\Products\Providers\ProductServiceProvider;
use App\Modules\Users\Providers\UserServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\InventoryServiceProvider;

return [
    AppServiceProvider::class,
    InventoryServiceProvider::class,

    UserServiceProvider::class,
    ProductServiceProvider::class,
    OrderServiceProvider::class,
    NotificationServiceProvider::class,
];
