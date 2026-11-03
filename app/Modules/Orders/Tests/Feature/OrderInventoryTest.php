<?php

namespace App\Modules\Orders\Tests\Feature;

use App\Modules\Orders\Models\Order;
use App\Modules\Products\Models\Category;
use App\Modules\Products\Models\Product;
use App\Modules\Users\Models\User;
use App\Shared\Contracts\InventoryClient;
use App\Shared\Exceptions\WarehouseTimeoutException;
use App\Shared\Exceptions\WarehouseUnavailableException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Support\SpyInventoryClient;
use Tests\TestCase;

/**
 * Requirement 2 failure handling, exercised through the HTTP API with the
 * warehouse port replaced by a spy (INVENTORY_DRIVER=fake).
 */
class OrderInventoryTest extends TestCase
{
    use RefreshDatabase;

    private SpyInventoryClient $inventory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->inventory = new SpyInventoryClient();
        $this->app->instance(InventoryClient::class, $this->inventory);
    }

    public function test_insufficient_stock_rejects_the_order_and_writes_nothing(): void
    {
        $customer = $this->customer();
        $product = $this->product(name: 'Mechanical Keyboard', price: 75);

        $this->inventory->approve(false, 'INSUFFICIENT_STOCK', [
            ['product_id' => $product->id, 'requested' => 5, 'available' => 1],
        ]);

        $response = $this->actingAs($customer, 'api')->postJson('/api/v1/orders', [
            'items' => [['product_id' => $product->id, 'quantity' => 5]],
        ]);

        $response->assertStatus(409)
            ->assertJsonPath('success', false)
            ->assertJsonPath('errors.stock.0.product_id', $product->id)
            ->assertJsonPath('errors.stock.0.requested', 5)
            ->assertJsonPath('errors.stock.0.available', 1);

        $this->assertStringContainsString('Insufficient stock', $response->json('message'));
        $this->assertStringContainsString('Mechanical Keyboard', $response->json('message'));

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
        $this->assertSame([], $this->inventory->released);
    }

    public function test_warehouse_down_returns_503_without_creating_an_order(): void
    {
        $customer = $this->customer();
        $product = $this->product();

        $this->inventory->reserveError = new WarehouseUnavailableException(
            'Warehouse service is unavailable (warehouse:50051).'
        );

        $response = $this->actingAs($customer, 'api')->postJson('/api/v1/orders', [
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ]);

        $response->assertStatus(503)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Warehouse service is unavailable (warehouse:50051).');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_warehouse_timeout_returns_503_quickly(): void
    {
        $customer = $this->customer();
        $product = $this->product();

        $this->inventory->reserveError = new WarehouseTimeoutException();

        $started = microtime(true);

        $response = $this->actingAs($customer, 'api')->postJson('/api/v1/orders', [
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ]);

        $elapsed = microtime(true) - $started;

        $response->assertStatus(503)
            ->assertJsonPath('success', false);

        $this->assertStringContainsString('did not respond in time', $response->json('message'));
        $this->assertLessThan(5, $elapsed, 'The request must not hang while the warehouse is down.');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_reservation_is_released_when_persisting_the_order_fails(): void
    {
        $customer = $this->customer();

        // price * quantity overflows the orders.total NUMERIC(10,2) column, so
        // the INSERT fails AFTER the warehouse approved the reservation.
        $product = $this->product(price: 99_999_999.99);

        $response = $this->actingAs($customer, 'api')->postJson('/api/v1/orders', [
            'items' => [['product_id' => $product->id, 'quantity' => 2]],
        ]);

        $response->assertStatus(500);

        $this->assertCount(1, $this->inventory->reserveCalls);

        $orderUuid = array_key_first($this->inventory->reserveCalls);

        $this->assertContains(
            $orderUuid,
            $this->inventory->released,
            'The reserved stock must be released when the order cannot be stored.'
        );

        // Nothing was persisted: the customer sees an error, not a phantom order.
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
    }

    public function test_a_retry_of_an_already_stored_order_keeps_its_reservation(): void
    {
        $customer = $this->customer();
        $product = $this->product();

        $existingUuid = (string) Str::uuid();

        Order::create([
            'user_id' => $customer->id,
            'status' => 'pending',
            'total' => 10,
            'uuid' => $existingUuid,
        ]);

        $response = $this->actingAs($customer, 'api')->postJson('/api/v1/orders', [
            'uuid' => $existingUuid,
            'items' => [['product_id' => $product->id, 'quantity' => 2]],
        ]);

        $response->assertStatus(500);

        $this->assertArrayHasKey($existingUuid, $this->inventory->reserveCalls);
        $this->assertNotContains(
            $existingUuid,
            $this->inventory->released,
            'A retry must not free the stock that belongs to the stored order.'
        );

        $this->assertDatabaseCount('orders', 1);
    }

    public function test_the_whole_order_is_reserved_in_a_single_call(): void
    {
        $customer = $this->customer();
        $first = $this->product(name: 'Laptop', price: 1200);
        $second = $this->product(name: 'Mouse', price: 20);

        $response = $this->actingAs($customer, 'api')->postJson('/api/v1/orders', [
            'items' => [
                ['product_id' => $first->id, 'quantity' => 1],
                ['product_id' => $second->id, 'quantity' => 3],
            ],
        ]);

        $response->assertStatus(201);

        $this->assertCount(1, $this->inventory->reserveCalls, 'Reserve must be called once per order.');

        $orderUuid = array_key_first($this->inventory->reserveCalls);

        $this->assertSame([
            ['product_id' => $first->id, 'quantity' => 1],
            ['product_id' => $second->id, 'quantity' => 3],
        ], $this->inventory->reserveCalls[$orderUuid]);

        $this->assertSame([], $this->inventory->released);
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('order_items', 2);
    }

    public function test_unknown_product_is_rejected_before_touching_the_warehouse(): void
    {
        $customer = $this->customer();

        $response = $this->actingAs($customer, 'api')->postJson('/api/v1/orders', [
            'items' => [['product_id' => 99999, 'quantity' => 1]],
        ]);

        $response->assertStatus(422);

        $this->assertSame([], $this->inventory->reserveCalls);
        $this->assertDatabaseCount('orders', 0);
    }

    private function customer(): User
    {
        return User::create([
            'name' => 'Customer',
            'email' => 'customer-' . Str::random(6) . '@test.com',
            'password' => bcrypt('password123'),
            'role' => 'customer',
        ]);
    }

    private function product(string $name = 'Test Product', float $price = 100): Product
    {
        $category = Category::firstOrCreate(['name' => 'General']);

        return Product::create([
            'name' => $name,
            'price' => $price,
            'category_id' => $category->id,
        ]);
    }
}
