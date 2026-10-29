<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;


use App\Modules\Users\Database\Seeders\UserDatabaseSeeder;
use App\Modules\Products\Database\Seeders\ProductDatabaseSeeder;
use App\Modules\Orders\Database\Seeders\OrderDatabaseSeeder;
use App\Modules\Notifications\Database\Seeders\NotificationDatabaseSeeder;
use App\Modules\Products\Database\Seeders\CategoryDatabaseSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserDatabaseSeeder::class,        
            CategoryDatabaseSeeder::class,
            ProductDatabaseSeeder::class,      
            OrderDatabaseSeeder::class,       
            NotificationDatabaseSeeder::class, 
        ]);
    }
}