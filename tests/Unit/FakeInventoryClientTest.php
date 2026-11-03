<?php

namespace Tests\Unit;

use App\Shared\Inventory\FakeInventoryClient;
use PHPUnit\Framework\TestCase;

/**
 * The fake is not only a test double: it is also the INVENTORY_DRIVER=fake
 * implementation, so its reservation semantics are unit tested directly.
 */
class FakeInventoryClientTest extends TestCase
{
    public function test_it_reports_the_configured_stock(): void
    {
        $client = new FakeInventoryClient([1 => 7]);

        $this->assertSame(7, $client->getStock(1));
        $this->assertSame(100, $client->getStock(99), 'unknown products use the default quantity');
    }

    public function test_reserving_deducts_the_quantity(): void
    {
        $client = new FakeInventoryClient([1 => 5]);

        $result = $client->reserve('order-1', [
            ['product_id' => 1, 'quantity' => 2],
        ]);

        $this->assertTrue($result->approved);
        $this->assertSame(3, $client->getStock(1));
    }

    public function test_reserving_twice_with_the_same_uuid_only_deducts_once(): void
    {
        $client = new FakeInventoryClient([1 => 5]);
        $items = [['product_id' => 1, 'quantity' => 2]];

        $client->reserve('order-1', $items);
        $second = $client->reserve('order-1', $items);

        $this->assertTrue($second->approved, 'the retry replays the original outcome');
        $this->assertSame(3, $client->getStock(1), 'the retry must not reserve a second time');
    }

    public function test_a_multi_line_reservation_is_all_or_nothing(): void
    {
        $client = new FakeInventoryClient([1 => 5, 2 => 1]);

        $result = $client->reserve('order-1', [
            ['product_id' => 1, 'quantity' => 2],
            ['product_id' => 2, 'quantity' => 3],
        ]);

        $this->assertFalse($result->approved);
        $this->assertTrue($result->isOutOfStock());
        $this->assertSame(
            [['product_id' => 2, 'requested' => 3, 'available' => 1]],
            $result->shortages()
        );
        $this->assertSame(5, $client->getStock(1), 'the healthy line must not be deducted');
        $this->assertSame(1, $client->getStock(2));
    }

    public function test_releasing_returns_the_units_and_is_idempotent(): void
    {
        $client = new FakeInventoryClient([1 => 5]);
        $client->reserve('order-1', [['product_id' => 1, 'quantity' => 2]]);

        $this->assertTrue($client->release('order-1'));
        $this->assertSame(5, $client->getStock(1));

        $this->assertFalse($client->release('order-1'), 'an already released reservation is a no-op');
        $this->assertSame(5, $client->getStock(1));
    }
}
