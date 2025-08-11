<?php

use App\Http\Middleware\AdminOnly;
use App\Http\Middleware\CheckAccountLock;
use App\Http\Middleware\CheckRegistrationLock;
use App\Http\Middleware\UpdateLastActiveAt;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // $middleware->trustProxies(at: '*');
        $middleware->alias([
            'admin' => AdminOnly::class,
            'update.last.active' => UpdateLastActiveAt::class,
            'check.account.lock' => CheckAccountLock::class,
            'registration.lock' => CheckRegistrationLock::class,
            'email.rate.limiter' => \App\Http\Middleware\EmailRateLimiter::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->withSchedule(function (Schedule $schedule) {
        // uncomment to enable email reminders
        // $schedule->command('emails:send-inactivity-reminders')->dailyAt('12:00');
    })
    ->create();
