<?php

namespace App\Modules\Products\Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Products\Models\Product;

class ProductDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Product::insert([
            [
                'name' => 'ASUS TUF Laptop',
                'price' => 1200.00,
                'description' => 'High performance developer laptop',
                'category_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Mechanical Keyboard',
                'price' => 75.00,
                'description' => 'A17 mobile from Samsung',
                'category_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}