<?php

namespace App\Providers;

use App\Events\FinalActivityCompleted;
use App\Listeners\ShowCompletionModal;
use Event;
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

        //remember me for 7 days
        Auth::extend('session', function ($app, $name, array $config) {
            $guard = new \Illuminate\Auth\SessionGuard($name, Auth::createUserProvider($config['provider']), $app['session.store'], $app['request']);
            $guard->setRememberDuration(10080);
            return $guard;
        });
    }
}
