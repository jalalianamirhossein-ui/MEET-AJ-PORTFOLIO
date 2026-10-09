<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        // These directives preserve existing inline scripts, styles and embeds.
        $response->headers->set('Content-Security-Policy', "base-uri 'self'; object-src 'none'; frame-ancestors 'self'");
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        $path = '/'.ltrim($request->path(), '/');
        $private = str_starts_with($path, '/admin')
            || str_starts_with($path, '/livewire')
            || str_starts_with($path, '/forms')
            || str_starts_with($path, '/filament')
            || $request->query->has('signature');

        if ($private || $request->isMethod('POST') || $request->routeIs('home')) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
        } elseif ($request->isMethod('GET') && str_contains((string) $response->headers->get('Content-Type'), 'text/html')) {
            // Article HTML follows a cookie preference; shared caches must not mix locales.
            $response->headers->set('Cache-Control', ($request->routeIs('articles.show') ? 'private' : 'public').', max-age=0, must-revalidate');
        }

        $contentType = (string) $response->headers->get('Content-Type');
        if ($contentType !== '' && str_contains($contentType, 'text/html') && ! str_contains(strtolower($contentType), 'charset')) {
            $response->headers->set('Content-Type', 'text/html; charset=UTF-8');
        }

        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', config('security.enforce_https') ? 'max-age=31536000' : 'max-age=0');
        }

        return $response;
    }
}
