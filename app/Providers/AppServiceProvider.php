<?php

namespace App\Providers;

use App\Http\Middleware\AdminOnly;
use App\Models\Module;
use App\Observers\ModuleObserver;
use App\Services\ProgressService;
use Event;
use Illuminate\Auth\SessionGuard;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
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
        $this->configureRateLimiters();

        Module::observe(ModuleObserver::class);

        Livewire::addPersistentMiddleware([
            AdminOnly::class,
        ]);

        Auth::extend('custom-session', function ($app, $name, array $config) {
            $provider = Auth::createUserProvider($config['provider']);
            $guard = new SessionGuard($name, $provider, $app['session.store'], $app['request']);
            //set cookie jar
            if (method_exists($guard, 'setCookieJar')) {
                $guard->setCookieJar($app['cookie']);
            }
            //remember duration - 8 weeks
            $guard->setRememberDuration(60*24*7*8);

            return $guard;
        });

        if (config('app.env') === 'production') {
            \URL::forceScheme('https');
        }

        // markdown directive - secured against XSS
        Blade::directive('markdown', function ($expression) {
            return '<?php 
                $content_for_markdown = is_string(' . $expression . ') ? ' . $expression . ' : "";
                
                // secure CommonMark configuration
                $config = [
                    "html_input" => "escape",  // escape all HTML input
                    "allow_unsafe_links" => false,  // disallow javascript: and data: URIs
                ];
                
                $environment = new \League\CommonMark\Environment\Environment($config);
                $environment->addExtension(new \League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension());
                
                $converter = new \League\CommonMark\MarkdownConverter($environment);
                $htmlContent = $converter->convert($content_for_markdown)->getContent();
                
                echo "<div class=\"markdown\">" . $htmlContent . "</div>"; 
            ?>';
        });
    }

    private function configureRateLimiters(): void
    {
        $local = app()->isLocal();

        // Unauthenticated endpoints only.
        // We disable in local dev to avoid fighting the limiter during testing.

        // 10 attempts per minute per IP (login)
        RateLimiter::for('login', function (Request $request) use ($local) {
            return $local ? Limit::none() : Limit::perMinute(10)->by($request->ip());
        });

        // 4 attempts per minute per IP (registration)
        RateLimiter::for('register', function (Request $request) use ($local) {
            return $local ? Limit::none() : Limit::perMinute(4)->by($request->ip());
        });

        // 4 attempts per minute per IP (password reset email)
        RateLimiter::for('password-reset', function (Request $request) use ($local) {
            return $local ? Limit::none() : Limit::perMinute(4)->by($request->ip());
        });

        // 4 attempts per minute per IP (resend verification email)
        RateLimiter::for('verify-email', function (Request $request) use ($local) {
            return $local ? Limit::none() : Limit::perMinute(4)->by($request->ip());
        });
    }
}
