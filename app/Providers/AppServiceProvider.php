<?php

namespace App\Providers;

use App\Events\FinalActivityCompleted;
use App\Listeners\ShowCompletionModal;
use Event;
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
    }
}
