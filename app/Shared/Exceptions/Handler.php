<?php
namespace App\Shared\Exceptions;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
class Handler extends ExceptionHandler
{
    public function render($request, Throwable $e)
    {
        if ($request->is('api/*')) {
            $code = $e->getCode() ?: 500;
            if ($code == 0) $code = 500;
            $message = $e->getMessage() ?: 'Server error';
            return response()->json([
                'error' => [
                    'code' => $code,
                    'message' => $message,
                    'details' => [],
                ]
            ], $code);
        }
        return parent::render($request, $e);
    }
}
