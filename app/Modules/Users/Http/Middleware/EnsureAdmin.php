<?php

namespace App\Modules\Users\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        // التحقق عبر حراسة الـ api للتأكد من جلب الأدمن بدقة
        $user = auth('api')->user();

        if ($user && $user->role === 'admin') {
            return $next($request);
        }

        return response()->json([
            'success' => false,
            'message' => 'Forbidden. Admins only.',
            'debug_role' => $user ? $user->role : 'no user found'
        ], 403);
    }
}