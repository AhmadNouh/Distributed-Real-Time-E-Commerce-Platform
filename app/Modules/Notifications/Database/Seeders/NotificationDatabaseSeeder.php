<?php

namespace App\Modules\Notifications\Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Users\Models\User;
use Illuminate\Support\Str;

class NotificationDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('role', 'customer')->first();

        if ($user) {
            $user->notifications()->create([
                'id' => (string) Str::uuid(),
                'type' => 'App\Modules\Notifications\Notifications\OrderCreatedNotification',
                'data' => [
                    'message' => 'Welcome to our e-commerce platform!',
                    'order_id' => 1
                ],
                'read_at' => null,
            ]);
        }
    }
}