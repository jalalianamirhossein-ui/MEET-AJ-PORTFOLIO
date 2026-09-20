<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AcceptLegacyCsrfToken
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->has('_token')) {
            $legacy = $request->input('csrf_token') ?: $request->header('X-CSRF-TOKEN');
            if (is_string($legacy) && $legacy !== '') {
                $request->merge(['_token' => $legacy]);
            }
        }

        return $next($request);
    }
}
