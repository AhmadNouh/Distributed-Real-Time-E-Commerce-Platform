<?php
// GENERATED CODE -- DO NOT EDIT!

namespace Warehouse\V1;

/**
 * WarehouseService is the contract between the Laravel application and the
 * standalone warehouse (inventory) service. The service owns its own database;
 * the application never reads stock tables directly.
 */
class WarehouseServiceClient extends \Grpc\BaseStub {

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts, $channel = null) {
        parent::__construct($hostname, $opts, $channel);
    }

    /**
     * Returns how many units of a product are currently available for sale
     * (physical quantity minus units already reserved by other orders).
     * @param \Warehouse\V1\GetStockRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function GetStock(\Warehouse\V1\GetStockRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/warehouse.v1.WarehouseService/GetStock',
        $argument,
        ['\Warehouse\V1\GetStockResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * set stock 
     * @param \Warehouse\V1\SetStockRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function SetStock(\Warehouse\V1\SetStockRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/warehouse.v1.WarehouseService/SetStock',
        $argument,
        ['\Warehouse\V1\SetStockResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * Reserves every item of an order in ONE atomic call.
     * Either all requested quantities are reserved, or nothing is reserved.
     * The call is idempotent per order_uuid: repeating the same order_uuid
     * returns the original outcome instead of reserving twice.
     * @param \Warehouse\V1\ReserveRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function Reserve(\Warehouse\V1\ReserveRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/warehouse.v1.WarehouseService/Reserve',
        $argument,
        ['\Warehouse\V1\ReserveResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * Releases a previous reservation, returning the quantities to the pool.
     * Used for compensation when the order could not be persisted.
     * Idempotent: releasing an already released order returns released = true.
     * @param \Warehouse\V1\ReleaseRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function Release(\Warehouse\V1\ReleaseRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/warehouse.v1.WarehouseService/Release',
        $argument,
        ['\Warehouse\V1\ReleaseResponse', 'decode'],
        $metadata, $options);
    }

}
