<?php
namespace App\Modules\Users\Providers;
use Illuminate\Support\ServiceProvider;
class ServiceProvider extends ServiceProvider
{
    public function register() {}
    public function boot() { \App::register(\App\Modules\Users\Providers\ServiceProvider::class); }
}
