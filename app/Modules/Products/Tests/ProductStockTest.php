<?php

namespace App\Modules\Products\Tests;

use App\Modules\Products\Models\Category;
use App\Modules\Products\Models\Product;
use App\Shared\Contracts\InventoryClient;
use App\Shared\Exceptions\WarehouseUnavailableException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\SpyInventoryClient;
use Tests\TestCase;

/**
 * Requirement 2: product details must show the quantity that the warehouse
 * service reports, and must keep working when the warehouse is down.
 */
class ProductStockTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_details_include_the_available_quantity(): void
    {
        $inventory = new SpyInventoryClient();
        $this->app->instance(InventoryClient::class, $inventory);

        $product = $this->product();
        $inventory->stock[$product->id] = 7;

        $response = $this->getJson("/api/v1/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $product->id)
            ->assertJsonPath('data.available_quantity', 7)
            ->assertJsonPath('data.stock_status', 'available');

        $this->assertSame([$product->id], $inventory->stockCalls);
    }

    public function test_product_details_still_render_when_the_warehouse_is_down(): void
    {
        $inventory = new SpyInventoryClient();
        $inventory->stockError = new WarehouseUnavailableException('Warehouse service is unavailable.');
        $this->app->instance(InventoryClient::class, $inventory);

        $product = $this->product();

        $response = $this->getJson("/api/v1/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $product->id)
            ->assertJsonPath('data.available_quantity', null)
            ->assertJsonPath('data.stock_status', 'unavailable');
    }

    private function product(): Product
    {
        $category = Category::create(['name' => 'Electronics']);

        return Product::create([
            'name' => 'Laptop',
            'price' => 1200,
            'category_id' => $category->id,
        ]);
    }
}
