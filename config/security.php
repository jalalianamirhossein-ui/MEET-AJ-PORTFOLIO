<?php

return [
    // HTTP hosting is supported; enable redirects explicitly for HTTPS-only hosting.
    'enforce_https' => env('FORCE_HTTPS', false),
    // Comma-separated proxy IPs/CIDRs. Direct connections never trust forwarded headers.
    'trusted_proxies' => array_values(array_filter(array_map('trim', explode(',', (string) env('TRUSTED_PROXIES', ''))))),
];
