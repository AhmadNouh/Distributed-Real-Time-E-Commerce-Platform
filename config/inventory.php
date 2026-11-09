<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Inventory driver
    |--------------------------------------------------------------------------
    | fake -> in-process array (used by unit tests, no network)
    | grpc -> the standalone Go warehouse service defined in proto/warehouse/v1
    */
    'driver' => env('INVENTORY_DRIVER', 'fake'),

    /*
    |--------------------------------------------------------------------------
    | gRPC warehouse service
    |--------------------------------------------------------------------------
    | `timeout` is applied to every RPC as a deadline, so a stopped or hung
    | warehouse service can never make an HTTP request wait forever.
    */
    'grpc' => [
        'address' => env('WAREHOUSE_ADDR', 'warehouse:50051'),
        'timeout' => (float) env('WAREHOUSE_TIMEOUT', 2.0),
        'max_receive_message_size' => 4 * 1024 * 1024,
    ],

    'fake' => [
        'stock' => [
            1 => 1,
            2 => 100,
            3 => 100,
            4 => 100,
            5 => 100,
            6 => 100,
            7 => 100,
            8 => 100,
        ],
    ],
];
