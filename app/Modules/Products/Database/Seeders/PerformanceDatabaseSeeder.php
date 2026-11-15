<?php

namespace App\Modules\Products\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PerformanceDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $products = [];
        for ($i = 1; $i <= 30000; $i++) {
            $products[] = [
                'name'        => 'Perf Product ' . $i . ' ' . ['Pro','Max','Lite','Ultra'][($i % 4)],
                'description' => 'Bulk seeded product for performance testing',
                'price'       => rand(5, 2000) + (rand(0, 99) / 100),
                'category_id' => rand(1, 2), 
                'created_at'  => now(),
                'updated_at'  => now(),
            ];
        }
        foreach (array_chunk($products, 1000) as $chunk) {
            DB::table('products')->insert($chunk);
        }

    
        $statuses = ['pending', 'paid', 'shipped', 'cancelled'];
        $orders = [];
        for ($i = 1; $i <= 20000; $i++) {
            $orders[] = [
                'user_id'    => 1,
                'status'     => $statuses[array_rand($statuses)],
                'created_at' => now()->subDays(rand(0, 60))->subMinutes(rand(0, 1440)),
                'updated_at' => now(),
            ];
        }
        foreach (array_chunk($orders, 1000) as $chunk) {
            DB::table('orders')->insert($chunk);
        }

        $minId = DB::table('products')->min('id');
        $maxId = DB::table('products')->max('id');
        $minOrder = DB::table('orders')->min('id');
        $maxOrder = DB::table('orders')->max('id');

        $items = [];
        for ($o = $minOrder; $o <= $maxOrder; $o++) {
            foreach (range(1, rand(1, 3)) as $_) {
                $items[] = [
                    'order_id'   => $o,
                    'product_id' => rand($minId, $maxId),
                    'quantity'   => rand(1, 5),
                    'price'      => rand(5, 2000) + (rand(0, 99) / 100),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        foreach (array_chunk($items, 1000) as $chunk) {
            DB::table('order_items')->insert($chunk);
        }
    }
}