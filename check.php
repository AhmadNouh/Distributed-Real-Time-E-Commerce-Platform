<?php
echo "default=" . config("queue.default") . PHP_EOL;
echo "rabbit_queue=" . config("queue.connections.rabbitmq.queue") . PHP_EOL;
echo "order_id=" . (\App\Modules\Orders\Models\Order::latest()->first()?->id ?? "NONE") . PHP_EOL;
echo "jobs_table=" . (\Illuminate\Support\Facades\Schema::hasTable("jobs") ? \Illuminate\Support\Facades\DB::table("jobs")->count() : "no table") . PHP_EOL;

