<?php

namespace App\Modules\Orders\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Modules\Orders\Models\Order;
use App\Modules\Orders\Events\OrderCreated;
use App\Modules\Orders\Events\OrderStatusChanged;
use App\Modules\Products\Models\Product;

class OrderController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $query = $user->is_admin ? Order::query() : Order::where('user_id', $user->id);
        return response()->json($query->with('items')->paginate(15));
    }

    public function show($id)
    {
        $order = Order::with('items')->find($id);
        if (!$order) {
            return response()->json(['error' => ['message' => 'Not found', 'code' => 404, 'details' => []]], 404);
        }
        
        $user = Auth::user();
        if ($order->user_id !== $user->id && !$user->is_admin) {
            return response()->json(['error' => ['message' => 'Forbidden', 'code' => 403, 'details' => []]], 403);
        }
        
        return $order;
    }

    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1'
        ]);

        $user = Auth::user();
        $items = $request->input('items');

        return DB::transaction(function () use ($user, $items, $request) {
            $order = Order::create([
                'user_id' => $user->id,
                'status' => 'pending',
                'total' => 0,
                'uuid' => $request->get('uuid', Str::uuid()),
            ]);

            $total = 0;
            foreach ($items as $it) {
                $p = Product::find($it['product_id']);
                $qty = $it['quantity'];
                $price = $p ? $p->price : 0;

                $order->items()->create([
                    'product_id' => $it['product_id'],
                    'quantity' => $qty,
                    'price' => $price
                ]);

                $total += $price * $qty;
            }

            $order->update(['total' => $total]);

            event(new OrderCreated($order));

            return response()->json($order->load('items'), 201);
        });
    }
    
    
    public function updateStatus(Request $request, $id)
    {
        $order = Order::find($id);
        
        if (!$order) {
            return response()->json([
                'error' => [
                    'message' => 'Order not found',
                    'code' => 404,
                    'details' => []
                ]
            ], 404);
        }

        $user = Auth::user();
        if ($user->role !== 'admin') {
            return response()->json([
                'error' => [
                    'message' => 'Forbidden',
                    'code' => 403,
                    'details' => []
                ]
            ], 403);
        }

        $request->validate([
            'status' => 'required|in:pending,confirmed,shipped,delivered,cancelled'
        ]);
        
        $newStatus = $request->input('status');
        $valid = [
            'pending' => ['confirmed', 'cancelled'],
            'confirmed' => ['shipped', 'cancelled'],
            'shipped' => ['delivered']
        ];

        if (isset($valid[$order->status]) && !in_array($newStatus, $valid[$order->status])) {
            return response()->json([
                'error' => [
                    'message' => 'Invalid transition',
                    'code' => 422,
                    'details' => []
                ]
            ], 422);
        }

        $order->update(['status' => $newStatus]);
        event(new OrderStatusChanged($order));

        return response()->json($order);
    }
}