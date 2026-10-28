<?php
namespace App\Modules\Orders\Repositories;
use App\Modules\Orders\Models\Order;
class OrderRepository { public function find($id) { return Order::find($id); } public function create(array $data): Order { return Order::create($data); } }
