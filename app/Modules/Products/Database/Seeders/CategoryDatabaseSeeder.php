<?php

namespace App\Modules\Products\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoryDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('categories')->updateOrInsert(
            ['id' => 1],
            ['name' => 'Laptop', 'created_at' => now(), 'updated_at' => now()]
        );

        DB::table('categories')->updateOrInsert(
            ['id' => 2],
            ['name' => 'Mobile', 'created_at' => now(), 'updated_at' => now()]
        );
    }
}