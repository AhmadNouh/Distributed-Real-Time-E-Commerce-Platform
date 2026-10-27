<?php
namespace App\Shared\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiter;

class RateLimitLogin
{
    public function handle(Request $request, Closure $next, $maxAttempts = 5, $decayMinutes = 1)
    {
        $key = $request->ip() . ':' . $request->input('email');
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return response()->json([
                'error' => ['message'=>'Too many attempts. Try again later.','code'=>429,'details'=>[]]
            ], 429);
        }
        \Illuminate\Support\Facades\RateLimiter::hit($key, $decayMinutes * 60);
        return $next($request);
    }
}
