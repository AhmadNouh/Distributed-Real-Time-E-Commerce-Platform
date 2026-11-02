<?php

namespace App\Modules\Notifications\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Notifications\Contracts\NotificationService;
use App\Modules\Notifications\Http\Resources\NotificationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NotificationController extends Controller
{
    public function __construct(
        private readonly NotificationService $notifications
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $notifications = $this->notifications->paginate(
            $user,
            $request->boolean('unread_only'),
            $request->integer('per_page', 15)
        );

        return response()->json([
            'success' => true,
            'data'    => NotificationResource::collection($notifications),
            'meta'    => [
                'current_page' => $notifications->currentPage(),
                'last_page'    => $notifications->lastPage(),
                'per_page'     => $notifications->perPage(),
                'total'        => $notifications->total(),
                'unread_count' => $this->notifications->unreadCount($user),
            ],
        ], Response::HTTP_OK);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => [
                'unread_count' => $this->notifications->unreadCount($request->user()),
            ],
        ], Response::HTTP_OK);
    }

    public function read(Request $request, string $id): JsonResponse // mark as read
    {
        $notification = $this->notifications->markAsRead($request->user(), $id);

        if (! $notification) {
            return response()->json([
                'success' => false,
                'message' => 'الإشعار غير موجود',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم وضع علامة على الإشعار كمقروء',
            'data'    => new NotificationResource($notification),
        ], Response::HTTP_OK);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $this->notifications->markAllAsRead($request->user());

        return response()->json([
            'success' => true,
            'message' => 'تم وضع علامة على جميع الإشعارات كمقروء',
        ], Response::HTTP_OK);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        if (! $this->notifications->delete($request->user(), $id)) {
            return response()->json([
                'success' => false,
                'message' => 'الإشعار غير موجود',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الإشعار بنجاح',
        ], Response::HTTP_OK);
    }
}
