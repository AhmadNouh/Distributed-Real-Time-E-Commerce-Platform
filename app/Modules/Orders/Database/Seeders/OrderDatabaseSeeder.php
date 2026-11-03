<?php

namespace App\Modules\Orders\Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Orders\Models\Order;
use App\Modules\Products\Models\Product;
use App\Modules\Users\Models\User;
use Illuminate\Support\Str;

class OrderDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $customer = User::where('role', 'customer')->first();

        if (! $customer) {
            return;
        }

        // Never seed a second demo order for the same customer: the seeder has
        // to stay safe to re-run (php artisan db:seed is run again after every
        // migrate:fresh during the demos).
        if (Order::where('user_id', $customer->id)->exists()) {
            return;
        }

        // Resolve the products by name instead of hard-coding ids: PostgreSQL
        // sequences are not reset when rows are deleted, so product ids drift
        // (13/14 instead of 1/2) and a hard-coded FK insert breaks the seeder.
        $laptop = Product::where('name', 'ASUS TUF Laptop')->first();
        $keyboard = Product::where('name', 'Mechanical Keyboard')->first();

        if (! $laptop || ! $keyboard) {
            return;
        }

        $lines = [
            ['product' => $laptop, 'quantity' => 1],
            ['product' => $keyboard, 'quantity' => 1],
        ];

        $total = collect($lines)->sum(fn (array $line) => $line['product']->price * $line['quantity']);

        $order = Order::create([
            'user_id' => $customer->id,
            'status' => 'pending',
            'total' => $total,
            'uuid' => Str::uuid(),
        ]);

        $order->items()->createMany(
            collect($lines)->map(fn (array $line) => [
                'product_id' => $line['product']->id,
                'quantity' => $line['quantity'],
                'price' => $line['product']->price,
            ])->all()
        );
    }
}
