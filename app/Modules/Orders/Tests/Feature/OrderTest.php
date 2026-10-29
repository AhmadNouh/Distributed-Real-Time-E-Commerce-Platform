<?php

namespace App\Modules\Orders\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Modules\Users\Models\User;
use App\Modules\Products\Models\Category;
use App\Modules\Products\Models\Product;
use App\Modules\Orders\Models\Order;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_create_order()
    {
    
        $customer = User::create([
            'name' => 'Customer User',
            'email' => 'customer@test.com',
            'password' => bcrypt('password123'),
            'role' => 'customer'
        ]);

    
        $category = Category::create(['name' => 'General']);
        $product = Product::create([
            'name' => 'Test Product',
            'price' => 100.00,
            'category_id' => $category->id
        ]);

    
        $response = $this->actingAs($customer, 'sanctum')
                         ->postJson('/api/v1/orders', [
                             'items' => [
                                 [
                                     'product_id' => $product->id,
                                     'quantity' => 2
                                 ]
                             ]
                         ]);

        $response->assertStatus(201)
                 ->assertJsonPath('success', true) 
                 ->assertJsonPath('data.total', 200.00);
    }

    public function test_admin_can_update_order_status()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password123'),
            'role' => 'admin'
        ]);

        $customer = User::create([
            'name' => 'Customer',
            'email' => 'cust@test.com',
            'password' => bcrypt('password123'),
            'role' => 'customer'
        ]);


        $order = Order::create([
            'user_id' => $customer->id,
            'status' => 'pending',
            'total' => 150.00,
            'uuid' => \Illuminate\Support\Str::uuid()
        ]);

    
        $response = $this->actingAs($admin, 'sanctum')
                         ->patchJson("/api/v1/orders/{$order->id}/status", [
                             'status' => 'confirmed'
                         ]);

        $response->assertStatus(200)
                 ->assertJsonPath('status', 'confirmed');
    }
}