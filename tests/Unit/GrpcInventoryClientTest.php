<?php

namespace Tests\Unit;

use App\Shared\Exceptions\WarehouseTimeoutException;
use App\Shared\Exceptions\WarehouseUnavailableException;
use App\Shared\Inventory\GrpcInventoryClient;
use Grpc\BaseStub;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use RuntimeException;
use Throwable;
use Warehouse\V1\GetStockResponse;
use Warehouse\V1\ItemShortage;
use Warehouse\V1\ReleaseResponse;
use Warehouse\V1\ReserveResponse;
use Warehouse\V1\WarehouseServiceClient;

/**
 * Unit tests for the gRPC adapter's mapping layer.
 *
 * No network and no warehouse container: the generated stub is replaced by a
 * collable stub that returns canned [$response, $status] pairs, which is
 * exactly the shape \Grpc\UnaryCall::wait() produces. This pins down the
 * deadline -> 503 conversion that makes a stopped warehouse fail fast.
 */
class GrpcInventoryClientTest extends TestCase
{
    private const ADDRESS = 'warehouse.test:50051';
    private const TIMEOUT = 2.0;

    public function test_get_stock_returns_the_available_quantity_and_sends_a_deadline(): void
    {
        [$client, $stub] = $this->clientWith([
            $this->ok((new GetStockResponse())->setAvailable(12)),
        ]);

        $this->assertSame(12, $client->getStock(1));

        $this->assertSame('GetStock', $stub->calls[0]['method']);
        $this->assertSame(2_000_000, $stub->calls[0]['options']['timeout'], 'the deadline is sent in microseconds');
    }

    public function test_a_negative_available_quantity_is_clamped_to_zero(): void
    {
        [$client] = $this->clientWith([
            $this->ok((new GetStockResponse())->setAvailable(-3)),
        ]);

        $this->assertSame(0, $client->getStock(1));
    }

    public function test_reserve_maps_the_shortage_report(): void
    {
        $shortage = (new ItemShortage())
            ->setProductId(7)
            ->setRequested(4)
            ->setAvailable(1);

        $response = (new ReserveResponse())
            ->setApproved(false)
            ->setReason('INSUFFICIENT_STOCK')
            ->setShortages([$shortage]);

        [$client, $stub] = $this->clientWith([$this->ok($response)]);

        $result = $client->reserve('order-1', [
            ['product_id' => 7, 'quantity' => 4],
        ]);

        $this->assertFalse($result->approved);
        $this->assertTrue($result->isOutOfStock());
        $this->assertSame(
            [['product_id' => 7, 'requested' => 4, 'available' => 1]],
            $result->shortages()
        );
        $this->assertNull($result->reservationId, 'an empty reservation id is normalised to null');
        $this->assertSame('Reserve', $stub->calls[0]['method']);
    }

    public function test_an_approved_reservation_keeps_the_reservation_id(): void
    {
        $response = (new ReserveResponse())
            ->setApproved(true)
            ->setReason('OK')
            ->setReservationId('order-1');

        [$client] = $this->clientWith([$this->ok($response)]);

        $result = $client->reserve('order-1', [['product_id' => 1, 'quantity' => 1]]);

        $this->assertTrue($result->approved);
        $this->assertSame('order-1', $result->reservationId);
        $this->assertSame([], $result->shortages());
    }

    public function test_release_returns_the_reported_flag(): void
    {
        [$client] = $this->clientWith([
            $this->ok((new ReleaseResponse())->setReleased(true)),
        ]);

        $this->assertTrue($client->release('order-1'));
    }

    public function test_a_deadline_is_translated_into_a_timeout_exception(): void
    {
        [$client] = $this->clientWith([
            $this->statusPair(4, 'Deadline Exceeded'),
        ]);

        $this->expectException(WarehouseTimeoutException::class);
        $this->expectExceptionMessage('Warehouse service did not answer within 2.0s.');

        $client->getStock(1);
    }

    public function test_an_unavailable_status_names_the_address_and_details(): void
    {
        [$client] = $this->clientWith([
            $this->statusPair(14, 'connection refused'),
        ]);

        try {
            $client->getStock(1);
            $this->fail('An UNAVAILABLE status must throw.');
        } catch (WarehouseUnavailableException $e) {
            $this->assertStringContainsString(self::ADDRESS, $e->getMessage());
            $this->assertStringContainsString('connection refused', $e->getMessage());
        }
    }

    public function test_other_grpc_codes_are_reported_with_their_number(): void
    {
        [$client] = $this->clientWith([
            $this->statusPair(13, 'internal boom'),
        ]);

        try {
            $client->getStock(1);
            $this->fail('A non-zero status must throw.');
        } catch (WarehouseUnavailableException $e) {
            $this->assertStringContainsString('gRPC code 13', $e->getMessage());
            $this->assertStringContainsString('internal boom', $e->getMessage());
        }
    }

    public function test_a_transport_failure_is_translated(): void
    {
        [$client] = $this->clientWith([new RuntimeException('Connection refused')]);

        try {
            $client->getStock(1);
            $this->fail('A transport failure must throw.');
        } catch (WarehouseUnavailableException $e) {
            $this->assertStringContainsString('unreachable', $e->getMessage());
            $this->assertStringContainsString(self::ADDRESS, $e->getMessage());
        }
    }

    public function test_an_unexpected_result_shape_is_rejected(): void
    {
        [$client] = $this->clientWith([[]]);

        $this->expectException(WarehouseUnavailableException::class);
        $this->expectExceptionMessage('Unexpected response from the warehouse service.');

        $client->getStock(1);
    }

    /**
     * @param  array<int, array{0: object, 1: object}|Throwable|array{}>  $outcomes
     * @return array{0: GrpcInventoryClient, 1: object}
     */
    private function clientWith(array $outcomes): array
    {
        if (! class_exists(StubWarehouseServiceClient::class)) {
            $this->markTestSkipped('ext-grpc is required to stub the generated gRPC client.');
        }

        $stub = new StubWarehouseServiceClient($outcomes);

        $client = new GrpcInventoryClient(self::ADDRESS, self::TIMEOUT, 4 * 1024 * 1024);

        (new ReflectionProperty(GrpcInventoryClient::class, 'client'))->setValue($client, $stub);

        return [$client, $stub];
    }

    /**
     * @return array{0: object, 1: object}
     */
    private function ok(object $response): array
    {
        return [$response, (object) ['code' => 0, 'details' => '']];
    }

    /**
     * @return array{0: null, 1: object}
     */
    private function statusPair(int $code, string $details): array
    {
        return [null, (object) ['code' => $code, 'details' => $details]];
    }
}

/**
 * Mirrors \Grpc\UnaryCall: the adapter calls wait() and destructures the pair.
 */
final class FakeUnaryCall
{
    /**
     * @param  array<int, mixed>  $result
     */
    public function __construct(private array $result) {}

    /**
     * @return array<int, mixed>
     */
    public function wait(): array
    {
        return $this->result;
    }
}

/*
 * The generated client extends \Grpc\BaseStub, so this stub can only be
 * declared when ext-grpc is loaded. The guard keeps the suite runnable on a
 * machine without the extension (the test is skipped instead of fataling).
 */
if (class_exists(BaseStub::class)) {
    final class StubWarehouseServiceClient extends WarehouseServiceClient
    {
        /** @var array<int, array{method: string, options: array<string, mixed>}> */
        public array $calls = [];

        /**
         * @param  array<int, mixed>  $outcomes
         */
        public function __construct(private array $outcomes = []) {}

        public function GetStock(\Warehouse\V1\GetStockRequest $argument, $metadata = [], $options = [])
        {
            return $this->next('GetStock', $options);
        }

        public function Reserve(\Warehouse\V1\ReserveRequest $argument, $metadata = [], $options = [])
        {
            return $this->next('Reserve', $options);
        }

        public function Release(\Warehouse\V1\ReleaseRequest $argument, $metadata = [], $options = [])
        {
            return $this->next('Release', $options);
        }

        /**
         * @param  array<string, mixed>  $options
         */
        private function next(string $method, array $options): FakeUnaryCall
        {
            $outcome = array_shift($this->outcomes);

            $this->calls[] = ['method' => $method, 'options' => $options];

            if ($outcome instanceof Throwable) {
                throw $outcome;
            }

            return new FakeUnaryCall($outcome);
        }
    }
}
