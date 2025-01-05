<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EmailRateLimiter
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    protected $limiter;

    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }
    
    public function handle(Request $request, Closure $next): Response
    {
        // make key
        $key = $this->resolveRequestSignature($request);

        // limits for different email types
        $limits = [
            'verification' => ['attempts' => 3, 'decay' => 3600], // 3 per hour
            'contact' => ['attempts' => 5, 'decay' => 3600],      // 5 per hour
            'reminder' => ['attempts' => 1, 'decay' => 86400],    // 1 per day
        ];

        $type = $request->input('email_type', null);
        $limit = $limits[$type] ?? ['attempts' => 10, 'decay' => 3600]; // default 10 per hour

        // check if too many attempts on key
        if ($this->limiter->tooManyAttempts($key, $limit['attempts'])) {
            return response()->json([
                'message' => 'Too many email attempts. Please try again later.',
                'retry_after' => $this->limiter->availableIn($key)
            ], 429);
        }

        // hit the limiter
        $this->limiter->hit($key, $limit['decay']);

        return $next($request);
    }

    protected function resolveRequestSignature(Request $request): string
    {
        // make unique key with ip, email type, and email
        return sha1(
            $request->ip().'|'. $request->input('email_type', 'default').'|'.($request->input('email') ?? '')
        );
    }
}
