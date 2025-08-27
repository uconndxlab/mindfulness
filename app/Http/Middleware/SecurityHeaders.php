<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    // SECURITY NOTE: These routes have relaxed CSP restrictions
    // - 'unsafe-eval' is required for Alpine.js (Livewire and Filament dependency)
    // - 'unsafe-inline' is needed for some Filament styles, does not work with nonce
    // - Risk mitigation: Admin routes should be behind strong authentication
    private const LIVEWIRE_ROUTES = [
        'admin.users',
        'admin.events', 
        'account',
        'help'
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // NONCE
        $nonce = base64_encode(random_bytes(20));
        $request->attributes->set('csp_nonce', $nonce);
        view()->share('cspNonce', $nonce);

        /** @var Response $response */
        $response = $next($request);

        $isProd = config('app.env') === 'production';
        
        // Core security headers
        $this->setSecurityHeaders($response, $isProd);
        
        // CSP
        $this->setContentSecurityPolicy($response, $request, $nonce, $isProd);
        
        // Additional security headers for production
        if ($isProd) {
            $this->setProductionSecurityHeaders($response);
        }

        return $response;
    }

    /**
     * Set core security headers
     */
    private function setSecurityHeaders(Response $response, bool $isProd): void
    {
        // Prevent clickjacking
        $response->headers->set('X-Frame-Options', 'DENY');
        
        // Prevent MIME type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        
        // Control referrer information
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        
        // Prevent cross-domain policy files
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');
        
        // Remove server information
        $response->headers->remove('Server');
        $response->headers->remove('X-Powered-By');
        
        // HSTS for production HTTPS
        if ($isProd) {
            $response->headers->set(
                'Strict-Transport-Security', 
                'max-age=31536000; includeSubDomains; preload'
            );
        }
    }

    /**
     * Set additional production security headers
     */
    private function setProductionSecurityHeaders(Response $response): void
    {
        // Expect Certificate Transparency
        $response->headers->set('Expect-CT', 'max-age=86400, enforce');
        
        // Cross-Origin policies
        $response->headers->set('Cross-Origin-Embedder-Policy', 'require-corp');
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        $response->headers->set('Cross-Origin-Resource-Policy', 'same-origin');
    }

    /**
     * Set Content Security Policy
     */
    private function setContentSecurityPolicy(Response $response, Request $request, string $nonce, bool $isProd): void
    {
        $viteHosts = $this->getViteHosts($isProd);
        
        // CSP directives
        $directives = [
            'default-src' => ["'self'"],
            'script-src' => $this->getScriptSrc($request, $nonce, $viteHosts, $isProd),
            'style-src' => $this->getStyleSrc($request, $nonce, $viteHosts, $isProd),
            'img-src' => $this->getImgSrc($request, $isProd),
            'font-src' => $this->getFontSrc($request, $viteHosts, $isProd),
            'connect-src' => $this->getConnectSrc($viteHosts, $isProd),
            'worker-src' => $this->getWorkerSrc($viteHosts, $isProd),
            'media-src' => $this->getMediaSrc($isProd),
            'object-src' => ["'none'"],
            'base-uri' => ["'self'"],
            'frame-ancestors' => ["'none'"],
            'form-action' => ["'self'"],
            'upgrade-insecure-requests' => $isProd ? [] : null,
        ];

        // Build CSP string
        $cspParts = [];
        foreach ($directives as $directive => $sources) {
            if ($sources === null) continue; // skip null directives
            
            if (empty($sources)) {
                $cspParts[] = $directive;
            } else {
                $cspParts[] = $directive . ' ' . implode(' ', $sources);
            }
        }

        $csp = implode('; ', $cspParts);
        $response->headers->set('Content-Security-Policy', $csp);
        
        if (!$isProd) {
            // uncomment to test CSP without breaking functionality
            // $response->headers->set('Content-Security-Policy-Report-Only', $csp);
        }
    }

    /**
     * Get Vite development server hosts
     */
    private function getViteHosts(bool $isProd): array
    {
        if ($isProd) {
            return [];
        }

        $hosts = [];
        $vitePort = 5173; // Default Vite port
        $baseHosts = ['localhost', '127.0.0.1'];
        
        // app domain - herd uses projname.test
        $appUrl = config('app.url');
        if ($appUrl) {
            $appHost = parse_url($appUrl, PHP_URL_HOST);
            if ($appHost && !in_array($appHost, $baseHosts)) {
                $baseHosts[] = $appHost;
            }
        }

        // build urls
        foreach ($baseHosts as $host) {
            $hosts[] = "http://{$host}:{$vitePort}";
            $hosts[] = "https://{$host}:{$vitePort}";
        }

        return array_unique($hosts);
    }

    /**
     * Get script-src directive
     */
    private function getScriptSrc(Request $request, string $nonce, array $viteHosts, bool $isProd): array
    {
        // 'unsafe-eval' is required for Livewire (required for Alpine.js expressions)
        $sources = ["'self'"];

        $routeName = $request->route()?->getName() ?? '';

        // 'unsafe-eval' and nonce for livewire (required for Alpine.js)
        if (in_array($routeName, self::LIVEWIRE_ROUTES)) {
            $sources[] = "'unsafe-eval'";
            $sources[] = "'nonce-{$nonce}'";
        }

        // Filament disabled - no special handling needed
        // if (str_contains($routeName, 'filament.admin-cms') || str_starts_with($request->getPathInfo(), '/admin/cms')) {
        //     $sources[] = "'unsafe-eval'";       // required for alipine.js
        //     $sources[] = "'unsafe-inline'";     // nonce does not work here
        // }
        
        // vite hosts included in development
        if (!$isProd) {
            $sources = array_merge($sources, $viteHosts);
        }

        return $sources;
    }

    /**
     * Get style-src directive
     */
    private function getStyleSrc(Request $request, string $nonce, array $viteHosts, bool $isProd): array
    {
        $sources = ["'self'"];

        $routeName = $request->route()?->getName() ?? '';

        // Filament disabled - always use nonce for better security
        $sources[] = "'nonce-{$nonce}'";
        
        // if (str_contains($routeName, 'filament.admin-cms') || str_starts_with($request->getPathInfo(), '/admin/cms')) {
        //     // nonce does not work here
        //     $sources[] = "'unsafe-inline'";
        //     // Add Bunny Fonts for Filament
        //     $sources[] = 'fonts.bunny.net';
        // }
        
        // vite hosts included in development
        if (!$isProd) {
            $sources = array_merge($sources, $viteHosts);
        }

        return $sources;
    }

    /**
     * Get img-src directive
     */
    private function getImgSrc(Request $request, bool $isProd): array
    {
        $sources = ["'self'", 'data:'];

        // Filament disabled - no external image services needed
        // $routeName = $request->route()?->getName() ?? '';
        // if (str_contains($routeName, 'filament.admin-cms') || str_starts_with($request->getPathInfo(), '/admin/cms')) {
        //     // Allow UI Avatars service used by Filament
        //     $sources[] = 'ui-avatars.com';
        //     $sources[] = 'https://ui-avatars.com';
        // }

        // image CDNS in prod
        if ($isProd) {
            // can add image CDN
        }

        return $sources;
    }

    /**
     * Get font-src directive
     */
    private function getFontSrc(Request $request, array $viteHosts, bool $isProd): array
    {
        $sources = ["'self'"];
        // removed data, if issues can be added back
        // $sources[] = 'data:';
        
        // Filament disabled - no external font services needed
        // $routeName = $request->route()?->getName() ?? '';
        // if (str_contains($routeName, 'filament.admin-cms') || str_starts_with($request->getPathInfo(), '/admin/cms')) {
        //     // Allow Bunny Fonts for Filament
        //     $sources[] = 'fonts.bunny.net';
        //     $sources[] = 'https://fonts.bunny.net';
        // }
        
        // add vite hosts
        if (!$isProd) {
            $sources = array_merge($sources, $viteHosts);
        }
        
        // can add font CDN

        return $sources;
    }

    /**
     * Get connect-src directive
     */
    private function getConnectSrc(array $viteHosts, bool $isProd): array
    {
        $sources = ["'self'"];
        
        // ws support for vite hosts in dev
        if (!$isProd) {
            $sources = array_merge($sources, $viteHosts, ['ws:', 'wss:']);
        }
        
        // can add API endpoints here

        return $sources;
    }

    /**
     * Get worker-src directive
     */
    private function getWorkerSrc(array $viteHosts, bool $isProd): array
    {
        $sources = ["'self'", 'blob:'];
        
        // add vite hosts in dev
        if (!$isProd) {
            $sources = array_merge($sources, $viteHosts);
        }

        return $sources;
    }

    /**
     * Get media-src directive
     */
    private function getMediaSrc(bool $isProd): array
    {
        $sources = ["'self'", 'data:'];
        // data: URLs for inline media (if needed)
        // $sources[] = 'data:';
        
        if ($isProd) {
            // can add media CDN hosts here
        }

        return $sources;
    }
}
