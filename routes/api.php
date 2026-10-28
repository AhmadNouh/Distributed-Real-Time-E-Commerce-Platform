<?php
use Illuminate\Support\Facades\Route;
use Modules\User\Http\Controllers\LoginController;

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('register', [\App\Modules\Users\Http\Controllers\LoginController::class, 'register']);
        Route::post('login', [\App\Modules\Users\Http\Controllers\LoginController::class, '__invoke'])->middleware('ratelimit:5,1');
        Route::post('logout', [\App\Modules\Users\Http\Controllers\LoginController::class, 'logout'])->middleware('auth:api');
        Route::get('me', [\App\Modules\Users\Http\Controllers\LoginController::class, 'me'])->middleware('auth:api');
    });
    Route::prefix('products')->group(function () {
        Route::get('categories', [\App\Modules\Products\Http\Controllers\CategoryController::class, 'index']);
        Route::get('/', [\App\Modules\Products\Http\Controllers\ProductController::class, 'index']);
        Route::get('/{id}', [\App\Modules\Products\Http\Controllers\ProductController::class, 'show']);
        Route::post('/', [\App\Modules\Products\Http\Controllers\ProductController::class, 'store'])->middleware(['auth:api', 'admin']);
        Route::put('/{id}', [\App\Modules\Products\Http\Controllers\ProductController::class, 'update'])->middleware(['auth:api', 'admin']);
        Route::delete('/{id}', [\App\Modules\Products\Http\Controllers\ProductController::class, 'destroy'])->middleware(['auth:api', 'admin']);
    });
    Route::prefix('orders')->group(function () {
        Route::post('/', [\App\Modules\Orders\Http\Controllers\OrderController::class, 'store'])->middleware('auth:api');
        Route::get('/', [\App\Modules\Orders\Http\Controllers\OrderController::class, 'index'])->middleware('auth:api');
        Route::get('/{id}', [\App\Modules\Orders\Http\Controllers\OrderController::class, 'show'])->middleware('auth:api');
        Route::patch('/{id}/status', [\App\Modules\Orders\Http\Controllers\OrderController::class, 'updateStatus'])->middleware(['auth:api', 'admin']);
    });
    Route::prefix('notifications')->group(function () {
        Route::get('/', [\App\Modules\Notifications\Http\Controllers\NotificationController::class, 'index'])->middleware('auth:api');
        Route::patch('/{id}/read', [\App\Modules\Notifications\Http\Controllers\NotificationController::class, 'read'])->middleware('auth:api');
    });
});
