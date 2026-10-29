<?php

namespace App\Modules\Orders\Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Orders\Models\Order;
use App\Modules\Users\Models\User;
use Illuminate\Support\Str;

class OrderDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $customer = User::where('role', 'customer')->first();

        if ($customer) {
            $order = Order::create([
                'user_id' => $customer->id,
                'status' => 'pending',
                'total' => 1275.00,
                'uuid' => Str::uuid(),
            ]);

            // إضافة عناصر للطلب (تأكد من وجود منتجات IDs 1 و 2)
            $order->items()->createMany([
                ['product_id' => 1, 'quantity' => 1, 'price' => 1200.00],
                ['product_id' => 2, 'quantity' => 1, 'price' => 75.00],
            ]);
        }
    }
}