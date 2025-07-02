<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ApiRateLimit
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $key = 'api'): Response
    {
        $identifier = $request->ip();
        $maxAttempts = config('app.rate_limits.' . $key, 60);
        $decayMinutes = 1;

        if (RateLimiter::tooManyAttempts($key . ':' . $identifier, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key . ':' . $identifier);
            
            return response()->json([
                'error' => 'Too many requests',
                'message' => 'Rate limit exceeded. Try again in ' . $seconds . ' seconds.',
                'retry_after' => $seconds
            ], 429);
        }

        RateLimiter::hit($key . ':' . $identifier, $decayMinutes * 60);

        $response = $next($request);

        // Add rate limit headers
        $response->headers->set('X-RateLimit-Limit', $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', 
            max(0, $maxAttempts - RateLimiter::attempts($key . ':' . $identifier))
        );

        return $response;
    }
}
