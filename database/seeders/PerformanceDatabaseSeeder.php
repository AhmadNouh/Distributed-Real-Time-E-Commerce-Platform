<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PerformanceDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1) 20,000 منتج — يبدأ بعد المنتجات الموجودة تلقائيًا (لا يلمس 1..10 المرتبطة بالـ warehouse!)
        $products = [];
        for ($i = 1; $i <= 20000; $i++) {
            $products[] = [
                'name'        => 'Perf Product ' . $i . ' ' . ['Pro','Max','Lite','Ultra'][($i % 4)],
                'description' => 'Bulk seeded product for performance testing',
                'price'       => rand(5, 2000) + (rand(0, 99) / 100),
                'category_id' => rand(1, 2), // التصنيفات الموجودة عندك فقط
                'created_at'  => now(),
                'updated_at'  => now(),
            ];
        }
        foreach (array_chunk($products, 1000) as $chunk) {
            DB::table('products')->insert($chunk); // insert مباشر = أسرع بـ 50x من Eloquent
        }

        // 2) 20,000 طلب + عناصرها — عدّل أسماء الأعمدة/القيم إذا كانت مختلفة عندك
        $statuses = ['pending', 'paid', 'shipped', 'cancelled'];
        $orders = [];
        for ($i = 1; $i <= 20000; $i++) {
            $orders[] = [
                'user_id' => 1,
                'status'  => $statuses[array_rand($statuses)],
                'total'   => 0.00,
                'uuid'    => \Ramsey\Uuid\Uuid::uuid4()->toString(),
            ];
        }
        foreach (array_chunk($orders, 1000) as $chunk) {
            DB::table('orders')->insert($chunk);
        }

        // 3) عناصر الطلبات: 1-3 عناصر لكل طلب (~40k صف)
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
                ];
            }
        }
        foreach (array_chunk($items, 1000) as $chunk) {
            DB::table('order_items')->insert($chunk);
        }
    }
}