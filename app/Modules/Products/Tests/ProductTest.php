<?php

namespace App\Modules\Products\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Modules\Products\Models\Category;
use App\Modules\Products\Models\Product;
use App\Modules\Users\Models\User;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_anyone_can_view_products()
    {
        $category = Category::create(['name' => 'Electronics']);
        Product::create([
            'name' => 'Laptop',
            'price' => 1200.00,
            'category_id' => $category->id
        ]);

        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200)
                 ->assertJsonStructure(['success', 'data' => ['data']]);
    }

    public function test_admin_can_create_product()
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password123'),
            'role' => 'admin'
        ]);

        $category = Category::create(['name' => 'Phones']);

        $response = $this->actingAs($admin, 'sanctum')
                         ->postJson('/api/v1/products', [
                             'name' => 'iPhone 15',
                             'price' => 999.99,
                             'category_id' => $category->id
                         ]);

        $response->assertStatus(201)
                 ->assertJsonPath('success', true);
    }
}