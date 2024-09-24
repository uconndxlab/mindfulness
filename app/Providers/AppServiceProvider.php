<?php

namespace App\Providers;

use App\Events\FinalActivityCompleted;
use App\Listeners\ShowCompletionModal;
use Event;
use Illuminate\Auth\SessionGuard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(
            FinalActivityCompleted::class,
            [ShowCompletionModal::class, 'handle']
        );

        Auth::extend('custom-session', function ($app, $name, array $config) {
            $provider = Auth::createUserProvider($config['provider']);
            $guard = new SessionGuard($name, $provider, $app['session.store'], $app['request']);
            //set cookie jar
            if (method_exists($guard, 'setCookieJar')) {
                $guard->setCookieJar($app['cookie']);
            }
            //remember duration - 7 days
            $guard->setRememberDuration(10080);

            return $guard;
        });
    }
}
