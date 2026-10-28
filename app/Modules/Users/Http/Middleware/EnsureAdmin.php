<?php

namespace App\Modules\Users\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth('api')->user();

        if (! $user || $user->role !== 'admin') {
            abort(403, 'Forbidden.');
        }

        return $next($request);
    }
}