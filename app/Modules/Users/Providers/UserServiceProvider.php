<?php

namespace App\Modules\Users\Providers;

use App\Modules\Users\Contracts\AuthService;
use App\Modules\Users\Contracts\UserService;
use App\Modules\Users\Services\AuthServiceImpl;
use App\Modules\Users\Services\UserServiceImpl;
use Illuminate\Support\ServiceProvider;

class UserServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AuthService::class, AuthServiceImpl::class);
        $this->app->bind(UserService::class, UserServiceImpl::class);
    }
}
