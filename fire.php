<?php
$o = \App\Modules\Orders\Models\Order::with("user")->latest()->first();
echo "order=" . ($o ? $o->id : "NULL") . " user=" . ($o && $o->user ? $o->user->email : "NULL") . PHP_EOL;
\App\Modules\Orders\Events\OrderCreated::dispatch($o);
echo "dispatched" . PHP_EOL;

