<?php

namespace App\Http\Middleware;

use App\Services\ModuleScheduleService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UpdateLastActiveAt
{
    public function __construct(
        private ModuleScheduleService $moduleScheduleService,
    ) {}

    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $this->moduleScheduleService->syncForUser(Auth::user());
        }

        return $next($request);
    }
}
