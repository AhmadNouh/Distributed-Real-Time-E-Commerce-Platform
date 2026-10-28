<?php

namespace App\Modules\Notifications\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Notifications\Http\Resources\NotificationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = $request->boolean('unread_only')
            ? $user->unreadNotifications()
            : $user->notifications();

        $notifications = $query->paginate($request->integer('per_page', 15));

        return response()->json([
            'success' => true,
            'data'    => NotificationResource::collection($notifications),
            'meta'    => [
                'current_page' => $notifications->currentPage(),
                'last_page'    => $notifications->lastPage(),
                'per_page'     => $notifications->perPage(),
                'total'        => $notifications->total(),
                'unread_count' => $user->unreadNotifications()->count(),
            ],
        ], Response::HTTP_OK);
    }

    
    public function unreadCount(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => [
                'unread_count' => $request->user()->unreadNotifications()->count(),
            ],
        ], Response::HTTP_OK);
    }

    public function read(Request $request, string $id): JsonResponse // mark as read
    {
        $notification = $request->user()
            ->notifications()
            ->where('id', $id)
            ->first();

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'الإشعار غير موجود',
            ], Response::HTTP_NOT_FOUND);
        }

        if (is_null($notification->read_at)) {
            $notification->markAsRead();
        }

        return response()->json([
            'success' => true,
            'message' => 'تم وضع علامة على الإشعار كمقروء',
            'data'    => new NotificationResource($notification),
        ], Response::HTTP_OK);
    }

    
    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'تم وضع علامة على جميع الإشعارات كمقروء',
        ], Response::HTTP_OK);
    }


    public function destroy(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()
            ->notifications()
            ->where('id', $id)
            ->first();

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'الإشعار غير موجود',
            ], Response::HTTP_NOT_FOUND);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الإشعار بنجاح',
        ], Response::HTTP_OK);
    }
}