<?php

namespace App\Traits;

trait ApiResponse
{
    protected function successResponse(?string $message = 'Success', int $status = 200, array $headers = [])
    {
        return response()->json([
            'success' => true,
            'data' => null,
            'message' => $message,
            'errors' => null,
        ], $status, $headers);
    }

    protected function successDataResponse($data, ?string $message = 'Success', int $status = 200, array $headers = [])
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => $message,
            'errors' => null,
        ], $status, $headers);
    }

    protected function createdResponse($data, ?string $message = 'Created successfully', array $headers = [])
    {
        return $this->successDataResponse($data, $message, 201, $headers);
    }

    protected function noContentResponse(?string $message = 'No content')
    {
        return response()->json([
            'success' => true,
            'data' => null,
            'message' => $message,
            'errors' => null,
        ], 204);
    }

    protected function errorResponse(?string $message = 'Error', int $status = 400, $errors = null)
    {
        return response()->json([
            'success' => false,
            'data' => null,
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }

    protected function validationErrorResponse(array $errors, ?string $message = 'Validation failed')
    {
        return $this->errorResponse($message, 422, $errors);
    }

    protected function unauthorizedResponse(?string $message = 'Unauthorized')
    {
        return $this->errorResponse($message, 401);
    }

    protected function forbiddenResponse(?string $message = 'Forbidden')
    {
        return $this->errorResponse($message, 403);
    }

    protected function notFoundResponse(?string $message = 'Resource not found')
    {
        return $this->errorResponse($message, 404);
    }

    protected function serverErrorResponse(?string $message = 'Server error', ?\Throwable $e = null)
    {
        report($e);
        return $this->errorResponse($message, 500);
    }
}
