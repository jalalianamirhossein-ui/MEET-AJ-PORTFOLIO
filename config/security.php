<?php

return [
    'enforce_https' => env('FORCE_HTTPS', env('APP_ENV') === 'production'),
    // Comma-separated proxy IPs/CIDRs. Direct connections never trust forwarded headers.
    'trusted_proxies' => array_values(array_filter(array_map('trim', explode(',', (string) env('TRUSTED_PROXIES', ''))))),
];
