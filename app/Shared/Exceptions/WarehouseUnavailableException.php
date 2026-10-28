<?php
namespace App\Shared\Exceptions;

use Exception;

class WarehouseUnavailableException extends Exception
{
    protected $message = 'Warehouse service unavailable';
    protected $code = 503;
}
