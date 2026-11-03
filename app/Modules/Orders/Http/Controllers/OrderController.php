<?php

namespace App\Modules\Orders\Http\Controllers;

use App\Http\Controllers\Api\BaseApiController;
use App\Modules\Orders\Contracts\OrderService;
use App\Shared\Exceptions\InsufficientStockException;
use App\Shared\Exceptions\WarehouseUnavailableException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends BaseApiController
{
    public function __construct(
        private readonly OrderService $orders
    ) {}

    public function index(Request $request)
    {
        $orders = $this->orders->paginateFor(
            $request->user(),
            $request->integer('per_page', 15)
        );

        return $this->successDataResponse($orders, 'Orders retrieved successfully');
    }

    public function show(Request $request, $id)
    {
        $order = $this->orders->find((int) $id);

        if (! $order) {
            return $this->notFoundResponse('Order not found');
        }

        $user = $request->user();

        if ($order->user_id !== $user->id && ! $user->isAdmin()) {
            return $this->forbiddenResponse('Forbidden');
        }

        return $this->successDataResponse($order, 'Order details');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'uuid' => 'nullable|string|max:255',
        ]);

        try {
            $order = $this->orders->createFor(
                $request->user(),
                $validated['items'],
                $validated['uuid'] ?? null
            );
        } catch (InsufficientStockException $e) {
            // Warehouse said no: nothing was written, tell the user why.
            return $this->errorResponse(
                $e->getMessage(),
                Response::HTTP_CONFLICT,
                ['stock' => $e->shortages]
            );
        } catch (WarehouseUnavailableException $e) {
            // Warehouse stopped or exceeded the deadline: answer fast and clear.
            return $this->errorResponse(
                $e->getMessage(),
                Response::HTTP_SERVICE_UNAVAILABLE,
                ['warehouse' => [$e->getMessage()]]
            );
        }

        return $this->createdResponse($order, 'Order created successfully');
    }

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,shipped,delivered,cancelled',
        ]);

        try {
            $order = $this->orders->changeStatus((int) $id, $validated['status']);
        } catch (ModelNotFoundException) {
            return $this->notFoundResponse('Order not found');
        } catch (InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return response()->json($order);
    }
}
