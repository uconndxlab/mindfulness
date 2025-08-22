<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SessionPolicy
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $user = $request->user();
        if ($user && method_exists($user, 'isAdmin') && $user->isAdmin()) {
            // shorter session for admins and expire on close
            config(['session.lifetime' => (int) env('ADMIN_SESSION_LIFETIME', 30)]);
            config(['session.expire_on_close' => true]);

            // ensure admin sessions are non-persistent by clearing remember cookie
            $response->headers->clearCookie('remember_web');
        }

        return $response;
    }
}


