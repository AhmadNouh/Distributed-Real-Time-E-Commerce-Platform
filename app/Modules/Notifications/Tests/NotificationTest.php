<?php

namespace App\Modules\Notifications\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Modules\Users\Models\User;
use App\Modules\Orders\Models\Order;
use App\Modules\Orders\Events\OrderCreated;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_receives_notification_on_order_creation()
    {
        $user = User::create([
            'name' => 'Ahmad User',
            'email' => 'ahmad.notif@test.com',
            'password' => bcrypt('password123'),
            'role' => 'customer'
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'status' => 'pending',
            'total' => 250.00,
            'uuid' => \Illuminate\Support\Str::uuid()
        ]);

        event(new OrderCreated($order));

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $user->id,
            'notifiable_type' => User::class,
        ]);

        $response = $this->actingAs($user, 'sanctum')
                         ->getJson('/api/v1/notifications');

        $response->assertStatus(200)
                 ->assertJsonStructure([[ 'id', 'type', 'data', 'read_at' ]]);
    }
}