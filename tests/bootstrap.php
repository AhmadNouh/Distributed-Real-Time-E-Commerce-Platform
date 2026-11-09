<?php

/**
 * PHPUnit bootstrap.
 *
 * `php artisan test` boots the whole application in the parent process, which
 * loads `.env` and exports it. The PHPUnit child process inherits those
 * variables through $_SERVER, and Laravel's `env()` reads $_SERVER first, so a
 * plain `<env name="..." />` entry in phpunit.xml loses the battle.
 *
 * Pinning the values here - before the framework boots - is what actually
 * keeps the test suite on the testing database (`ecommerce_testing`) and on
 * the in-process inventory fake instead of the real gRPC warehouse.
 */

require __DIR__.'/../vendor/autoload.php';

$overrides = [
    'APP_ENV' => 'testing',
    'DB_CONNECTION' => 'pgsql',
    'DB_HOST' => 'postgres',
    'DB_PORT' => '5432',
    'DB_DATABASE' => 'ecommerce_testing',
    'DB_USERNAME' => 'postgres',
    'DB_PASSWORD' => 'postgres',
    'INVENTORY_DRIVER' => 'fake',
    'WAREHOUSE_ADDR' => 'warehouse:50051',
    'WAREHOUSE_TIMEOUT' => '2',
    'CACHE_STORE' => 'array',
    'QUEUE_CONNECTION' => 'sync',
    'MAIL_MAILER' => 'array',
    'SESSION_DRIVER' => 'array',
    'BCRYPT_ROUNDS' => '4',
];

foreach ($overrides as $key => $value) {
    $_SERVER[$key] = $value;
    $_ENV[$key] = $value;
    putenv("{$key}={$value}");
}
