<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AcceptLegacyCsrfToken
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->has('csrf_token') && ! $request->has('_token')) {
            $request->merge(['_token' => $request->input('csrf_token')]);
        }

        return $next($request);
    }
}
