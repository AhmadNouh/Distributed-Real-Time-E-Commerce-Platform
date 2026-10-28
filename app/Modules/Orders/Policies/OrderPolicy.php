<?php
namespace App\Modules\Orders\Policies;
use App\Modules\Users\Models\User;
use App\Modules\Orders\Models\Order;
class OrderPolicy
{
    public function view(User $user, Order $order) { return $user->id === $order->user_id || $user->isAdmin(); }
    public function changeStatus(User $user, Order $order) { return $user->isAdmin(); }
}
