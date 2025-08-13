<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'no-referrer');

        $isProd = config('app.env') === 'production';
        if ($isProd) {
            // HSTS only in production
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        // Build CSP: same strict policy in dev and prod (no inline/eval). Only difference: dev allows Vite dev host for modules/HMR.
        $devHosts = [
            'http://localhost:5173',
            'https://localhost:5173',
            'http://127.0.0.1:5173',
            'https://127.0.0.1:5173',
        ];
        $appUrl = config('app.url', env('APP_URL'));
        if (!empty($appUrl)) {
            $host = parse_url($appUrl, PHP_URL_HOST);
            if (!empty($host)) {
                $devHosts[] = 'http://' . $host . ':5173';
                $devHosts[] = 'https://' . $host . ':5173';
            }
        }

        $allowedScriptHosts = $isProd ? [] : $devHosts;
        $allowedStyleHosts = $isProd ? [] : $devHosts;
        $allowedFontHosts = $isProd ? [] : $devHosts;
        $allowedWorkerHosts = $isProd ? [] : $devHosts;
        $allowedConnectHosts = $isProd ? [] : $devHosts;

        $scriptSrc = array_merge(["'self'"], $allowedScriptHosts);
        $styleSrc = array_merge(["'self'"], $allowedStyleHosts);
        $imgSrc = ["'self'", 'data:'];
        $fontSrc = array_merge(["'self'", 'data:'], $allowedFontHosts);
        $connectSrc = array_merge(["'self'"], $allowedConnectHosts, $isProd ? [] : ['ws:', 'wss:']);
        $workerSrc = array_merge(["'self'", 'blob:'], $allowedWorkerHosts);

        $csp = sprintf(
            "default-src 'self'; object-src 'none'; base-uri 'self'; frame-ancestors 'none'; script-src %s; style-src %s; img-src %s; font-src %s; connect-src %s; worker-src %s; form-action 'self'",
            implode(' ', $scriptSrc),
            implode(' ', $styleSrc),
            implode(' ', $imgSrc),
            implode(' ', $fontSrc),
            implode(' ', $connectSrc),
            implode(' ', $workerSrc)
        );
        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}


