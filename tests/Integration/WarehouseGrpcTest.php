<?php

namespace Tests\Integration;

use App\Shared\Inventory\GrpcInventoryClient;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Real gRPC integration tests: no fakes, they talk to the running Go warehouse
 * service (proto/warehouse/v1/warehouse.proto).
 *
 * They are skipped by default because they need `docker compose up -d
 * warehouse warehouse-db`. Run them with:
 *
 *   docker compose exec -T -e RUN_GRPC_TESTS=1 app php artisan test --testsuite=Integration
 */
class WarehouseGrpcTest extends TestCase
{
    private GrpcInventoryClient $client;

    /** Product ids seeded by services/warehouse/internal/warehouse/schema.sql. */
    private const PLENTY_OF_STOCK = 8;   // 100 units
    private const ANOTHER_PRODUCT = 7;   // 100 units
    private const SCARCE_PRODUCT = 6;    // 100 units, we ask for a million

    protected function setUp(): void
    {
        parent::setUp();

        if (getenv('RUN_GRPC_TESTS') !== '1') {
            $this->markTestSkipped('Set RUN_GRPC_TESTS=1 to run the real warehouse integration tests.');
        }

        if (! extension_loaded('grpc')) {
            $this->markTestSkipped('The PHP grpc extension is not loaded.');
        }

        $this->client = new GrpcInventoryClient(
            address: (string) env('WAREHOUSE_ADDR', 'warehouse:50051'),
            timeout: (float) env('WAREHOUSE_TIMEOUT', 2.0),
        );
    }

    public function test_reserving_then_releasing_restores_the_available_quantity(): void
    {
        $orderUuid = (string) Str::uuid();

        $before = $this->client->getStock(self::PLENTY_OF_STOCK);

        $result = $this->client->reserve($orderUuid, [
            ['product_id' => self::PLENTY_OF_STOCK, 'quantity' => 3],
        ]);

        $this->assertTrue($result->approved, 'The warehouse should approve a small reservation.');
        $this->assertSame('OK', $result->reason);
        $this->assertSame($before - 3, $this->client->getStock(self::PLENTY_OF_STOCK));

        $this->assertTrue($this->client->release($orderUuid));
        $this->assertSame($before, $this->client->getStock(self::PLENTY_OF_STOCK));
    }

    public function test_reserving_more_than_available_is_rejected_with_a_shortage(): void
    {
        $result = $this->client->reserve((string) Str::uuid(), [
            ['product_id' => self::SCARCE_PRODUCT, 'quantity' => 1_000_000],
        ]);

        $this->assertFalse($result->approved);
        $this->assertSame('INSUFFICIENT_STOCK', $result->reason);
        $this->assertNotEmpty($result->shortages());
        $this->assertSame(self::SCARCE_PRODUCT, $result->shortages()[0]['product_id']);
        $this->assertSame(1_000_000, $result->shortages()[0]['requested']);
        $this->assertSame(
            $this->client->getStock(self::SCARCE_PRODUCT),
            $result->shortages()[0]['available'],
        );
    }

    public function test_a_multi_line_order_is_all_or_nothing(): void
    {
        $orderUuid = (string) Str::uuid();

        $before = $this->client->getStock(self::PLENTY_OF_STOCK);

        $result = $this->client->reserve($orderUuid, [
            ['product_id' => self::PLENTY_OF_STOCK, 'quantity' => 1],
            ['product_id' => self::SCARCE_PRODUCT, 'quantity' => 1_000_000],
        ]);

        $this->assertFalse($result->approved);
        $this->assertSame(
            $before,
            $this->client->getStock(self::PLENTY_OF_STOCK),
            'The line that did have stock must not be reserved when another line fails.',
        );
    }

    public function test_reserve_is_idempotent_per_order_uuid(): void
    {
        $orderUuid = (string) Str::uuid();

        $first = $this->client->reserve($orderUuid, [
            ['product_id' => self::ANOTHER_PRODUCT, 'quantity' => 2],
        ]);

        $this->assertTrue($first->approved);

        $afterFirst = $this->client->getStock(self::ANOTHER_PRODUCT);

        $second = $this->client->reserve($orderUuid, [
            ['product_id' => self::ANOTHER_PRODUCT, 'quantity' => 2],
        ]);

        $this->assertTrue($second->approved, 'A retry of the same order must stay approved.');
        $this->assertSame(
            $afterFirst,
            $this->client->getStock(self::ANOTHER_PRODUCT),
            'A retry must not reserve a second time.',
        );

        $this->client->release($orderUuid);
    }

    public function test_release_is_idempotent(): void
    {
        $orderUuid = (string) Str::uuid();

        $this->client->reserve($orderUuid, [
            ['product_id' => self::ANOTHER_PRODUCT, 'quantity' => 1],
        ]);

        $this->assertTrue($this->client->release($orderUuid));
        $this->assertTrue($this->client->release($orderUuid), 'Releasing twice must stay safe.');
    }

    public function test_unknown_product_has_no_stock(): void
    {
        $this->assertSame(0, $this->client->getStock(999_999));
    }
}
