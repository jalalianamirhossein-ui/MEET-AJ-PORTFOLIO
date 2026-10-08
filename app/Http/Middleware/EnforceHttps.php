<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceHttps
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('security.enforce_https') || $request->secure()) {
            return $next($request);
        }

        $origin = rtrim((string) config('app.url'), '/');
        // Use the configured origin, never a client-supplied Host header.
        abort_unless(parse_url($origin, PHP_URL_SCHEME) === 'https' && parse_url($origin, PHP_URL_HOST), 503);

        if (! $request->isMethodSafe()) {
            return response('HTTPS is required. Reload the page using HTTPS.', 400)
                ->header('Content-Type', 'text/plain; charset=UTF-8');
        }

        return redirect()->away($origin.'/'.ltrim($request->getRequestUri(), '/'), 308);
    }
}
