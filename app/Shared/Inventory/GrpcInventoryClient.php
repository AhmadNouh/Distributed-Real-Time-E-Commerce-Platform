<?php

namespace App\Shared\Inventory;

use App\Shared\Contracts\InventoryClient;
use App\Shared\Contracts\ReservationResult;
use App\Shared\Exceptions\WarehouseTimeoutException;
use App\Shared\Exceptions\WarehouseUnavailableException;        
use Throwable;
use Warehouse\V1\GetStockRequest;
use Warehouse\V1\OrderItem;
use Warehouse\V1\ReleaseRequest;
use Warehouse\V1\ReserveRequest;
use Warehouse\V1\WarehouseServiceClient;

/**
 * gRPC adapter for the standalone Go warehouse service.
 *
 * Contract: proto/warehouse/v1/warehouse.proto
 * Generated stubs: app/Shared/Grpc/Generated/Warehouse/V1
 *
 * Every call carries a deadline (`inventory.grpc.timeout`), so a stopped or
 * hung warehouse can never block an HTTP request forever: the RPC fails with
 * DEADLINE_EXCEEDED and we translate it into a 503 the user can understand.
 */
class GrpcInventoryClient implements InventoryClient
{
    private const CODE_OK = 0;
    private const CODE_DEADLINE_EXCEEDED = 4;
    private const CODE_UNAVAILABLE = 14;

    private readonly string $address;
    private readonly float $timeout;
    private readonly int $maxReceiveMessageSize;

    private ?WarehouseServiceClient $client = null;

    public function __construct(
        ?string $address = null,
        ?float $timeout = null,
        ?int $maxReceiveMessageSize = null,
    ) {
        $this->address = $address ?: (string) config('inventory.grpc.address', 'warehouse:50051');
        $this->timeout = $timeout ?: (float) config('inventory.grpc.timeout', 2.0);
        $this->maxReceiveMessageSize = $maxReceiveMessageSize
            ?: (int) config('inventory.grpc.max_receive_message_size', 4 * 1024 * 1024);
    }


    public function setStock(int $productId, int $quantity): bool
    {
        $request = new \Warehouse\V1\SetStockRequest();
        $request->setProductId($productId);
        $request->setQuantity($quantity);

        [$response] = $this->invoke(
            fn (array $options, array $metadata) => $this->client()->SetStock($request, $metadata, $options)
        );

        return (bool) $response->getSuccess();
    }


    public function getStock(int $productId): int
    {
        $request = new GetStockRequest();
        $request->setProductId($productId);

        [$response] = $this->invoke(
            fn (array $options, array $metadata) => $this->client()->GetStock($request, $metadata, $options)
        );

        return max(0, (int) $response->getAvailable());
    }

    public function reserve(string $orderUuid, array $items): ReservationResult
    {
        $request = new ReserveRequest();
        $request->setOrderUuid($orderUuid);

        $lines = [];

        foreach ($items as $item) {
            $line = new OrderItem();
            $line->setProductId((int) $item['product_id']);
            $line->setQuantity((int) $item['quantity']);
            $lines[] = $line;
        }

        $request->setItems($lines);

        [$response] = $this->invoke(
            fn (array $options, array $metadata) => $this->client()->Reserve($request, $metadata, $options)
        );

        $shortages = [];

        foreach ($response->getShortages() as $shortage) {
            $shortages[] = [
                'product_id' => (int) $shortage->getProductId(),
                'requested' => (int) $shortage->getRequested(),
                'available' => (int) $shortage->getAvailable(),
            ];
        }

        $reason = $response->getReason();

        return new ReservationResult(
            approved: (bool) $response->getApproved(),
            reason: $reason !== '' ? $reason : null,
            shortages: $shortages,
            reservationId: $response->getReservationId() ?: null,
        );
    }

    public function release(string $orderUuid): bool
    {
        $request = new ReleaseRequest();
        $request->setOrderUuid($orderUuid);

        [$response] = $this->invoke(
            fn (array $options, array $metadata) => $this->client()->Release($request, $metadata, $options)
        );

        return (bool) $response->getReleased();
    }

    /**
     * Runs one unary RPC and normalises transport errors into domain exceptions.
     *
     * @return array{0: object}
     *
     * @throws WarehouseTimeoutException|WarehouseUnavailableException
     */
    private function invoke(callable $call): array
    {
        $options = [
            // gRPC PHP expects the deadline in microseconds.
            'timeout' => (int) round($this->timeout * 1_000_000),
        ];

        try {
            $result = $call($options, []);
        } catch (Throwable $e) {
            throw new WarehouseUnavailableException(
                'Warehouse service is unreachable at ' . $this->address . ': ' . $e->getMessage()
            );
        }

        // Generated stubs return a UnaryCall object; waiting on it yields
        // [$response, $status]. Some versions return the pair directly.
        if (is_object($result) && method_exists($result, 'wait')) {
            $result = $result->wait();
        }

        if (! is_array($result) || count($result) < 2) {
            throw new WarehouseUnavailableException('Unexpected response from the warehouse service.');
        }

        [$response, $status] = $result;

        $code = (int) ($status->code ?? self::CODE_UNAVAILABLE);
        $details = (string) ($status->details ?? '');

        if ($code === self::CODE_OK) {
            return [$response];
        }

        if ($code === self::CODE_DEADLINE_EXCEEDED) {
            throw new WarehouseTimeoutException(
                sprintf('Warehouse service did not answer within %.1fs.', $this->timeout)
            );
        }

        if ($code === self::CODE_UNAVAILABLE) {
            throw new WarehouseUnavailableException(
                'Warehouse service is unavailable (' . $this->address . ').' . ($details !== '' ? ' ' . $details : '')
            );
        }

        throw new WarehouseUnavailableException(
            sprintf('Warehouse service error (gRPC code %d)%s', $code, $details !== '' ? ': ' . $details : '.')
        );
    }

    private function client(): WarehouseServiceClient
    {
        return $this->client ??= new WarehouseServiceClient($this->address, [
            'credentials' => \Grpc\ChannelCredentials::createInsecure(),
            'grpc.max_receive_message_length' => $this->maxReceiveMessageSize,
        ]);
    }
}   
