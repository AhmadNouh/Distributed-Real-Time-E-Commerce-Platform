<?php
return [
    'driver' => env('INVENTORY_DRIVER', 'fake'),
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
