<?php

use App\Http\Middleware\AcceptLegacyCsrfToken;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(web: __DIR__.'/../routes/web.php', commands: __DIR__.'/../routes/console.php')
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(prepend: [
            AcceptLegacyCsrfToken::class,
        ]);
        $middleware->appendToGroup('web', SecurityHeaders::class);
        $middleware->redirectGuestsTo('/admin/login');
        $middleware->redirectUsersTo('/admin');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->shouldRenderJsonWhen(fn (Request $request, Throwable $e) => $request->expectsJson());
    })
    ->create();
