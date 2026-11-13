<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOnly
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // check if user is admin
        if (! ($request->user() && $request->user()->isAdmin())) {
            return redirect()->route('explore.home');
        }

        return $next($request);
    }
}
