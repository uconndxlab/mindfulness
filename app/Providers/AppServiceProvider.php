<?php

namespace App\Providers;

use App\Events\FinalActivityCompleted;
use App\Http\Middleware\AdminOnly;
use App\Listeners\ShowCompletionModal;
use App\Models\Module;
use App\Observers\ModuleObserver;
use App\Services\ProgressService;
use Event;
use Illuminate\Auth\SessionGuard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Livewire\Livewire;
use League\CommonMark\CommonMarkConverter;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ProgressService::class, function ($app) {
            return new ProgressService();
        });

        Paginator::useBootstrapFive();

        $this->app->singleton(CommonMarkConverter::class, function ($app) {
            return new CommonMarkConverter([
                'html_input' => 'strip',
                'allow_unsafe_links' => false,
            ]);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Module::observe(ModuleObserver::class);

        Livewire::addPersistentMiddleware([
            AdminOnly::class,
        ]);
        
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
            //remember duration - 30 days
            $guard->setRememberDuration(43200);

            return $guard;
        });

        if(config('app.env') === 'production') {
            \URL::forceScheme('https');
        }

        Blade::directive('markdown', function ($expression) {
            return '<?php echo "<div class=\"markdown\">" . app(\League\CommonMark\CommonMarkConverter::class)->convert(' . $expression . ') . "</div>"; ?>';
        });
    }
}
