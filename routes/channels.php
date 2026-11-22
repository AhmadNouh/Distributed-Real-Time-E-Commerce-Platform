<?php

use App\Modules\Orders\Models\Order;
use App\Modules\Users\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Admin // this for create new order real time
Broadcast::channel('admin-orders', function (User $user) {
    return $user->role === 'admin';
});


// User & Admin // this for update order status
Broadcast::channel('orders.{orderId}', function (User $user, $orderId) {
    $order = Order::find($orderId);
    
    if (!$order) {
        return false;
    }

    return (int) $user->id === (int) $order->user_id || $user->role === 'admin';
});


