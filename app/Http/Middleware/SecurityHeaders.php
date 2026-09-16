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

        $path = '/'.ltrim($request->path(), '/');
        $private = str_starts_with($path, '/admin')
            || str_starts_with($path, '/livewire')
            || str_starts_with($path, '/forms');

        if ($private || $request->isMethod('POST')) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
        } elseif ($request->isMethod('GET') && str_contains((string) $response->headers->get('Content-Type'), 'text/html')) {
            $response->headers->set('Cache-Control', 'public, max-age=0, must-revalidate');
        }

        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
