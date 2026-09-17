<?php

use App\Http\Middleware\AcceptLegacyCsrfToken;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

$basePath = dirname(__DIR__);
foreach ([
    $basePath.'/storage/app',
    $basePath.'/storage/app/public',
    $basePath.'/storage/framework/cache/data',
    $basePath.'/storage/framework/sessions',
    $basePath.'/storage/framework/testing',
    $basePath.'/storage/framework/views',
    $basePath.'/storage/logs',
    $basePath.'/bootstrap/cache',
] as $directory) {
    if (! is_dir($directory)) {
        mkdir($directory, 0775, true);
    }
}

return Application::configure(basePath: $basePath)
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
