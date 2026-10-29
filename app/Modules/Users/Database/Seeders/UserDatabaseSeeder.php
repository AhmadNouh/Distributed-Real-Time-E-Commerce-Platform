<?php

namespace App\Modules\Users\Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Users\Models\User;
use Illuminate\Support\Facades\Hash;

class UserDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        
        User::firstOrCreate(
            ['email' => 'customer@example.com'],
            [
                'name' => 'Customer User',
                'password' => Hash::make('password'),
                'role' => 'customer',
            ]
        );
    }
}