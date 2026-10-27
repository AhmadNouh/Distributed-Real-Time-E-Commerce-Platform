<?php
namespace App\Modules\Orders\Events;
use App\Modules\Orders\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;
class OrderCreated
{
    use Dispatchable;
    public $order;
    public function __construct(Order $order) { $this->order = $order; }
}
