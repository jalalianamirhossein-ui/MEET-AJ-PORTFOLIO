<?php

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo json_encode([
    'app_url' => config('app.url'),
    'app_env' => config('app.env'),
    'session_secure' => config('session.secure'),
    'session_same_site' => config('session.same_site'),
    'session_domain' => config('session.domain'),
    'session_driver' => config('session.driver'),
    'session_cookie' => config('session.cookie'),
    'env_secure' => env('SESSION_SECURE_COOKIE'),
], JSON_PRETTY_PRINT), PHP_EOL;
