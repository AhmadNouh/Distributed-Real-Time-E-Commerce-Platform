<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Orders\Http\Controllers\OrderController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/orders', [OrderController::class, 'index']);
    
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    
    Route::post('/orders', [OrderController::class, 'store']);
    
    Route::middleware('role:admin')->group(function () {
        Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus']);
    });
});