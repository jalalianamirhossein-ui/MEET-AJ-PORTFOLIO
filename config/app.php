<?php
return [
    'name' => env('APP_NAME', 'Meet AJ'), 'env' => env('APP_ENV', 'production'),
    'debug' => (bool) env('APP_DEBUG', false), 'url' => env('APP_URL', 'https://meetaj.ir'),
    'timezone' => env('APP_TIMEZONE', 'UTC'), 'locale' => env('APP_LOCALE', 'en'),
    'fallback_locale' => 'en', 'faker_locale' => 'en_US', 'cipher' => 'AES-256-CBC',
    'key' => env('APP_KEY'), 'previous_keys' => [],
    'maintenance' => ['driver' => 'file'],
];
