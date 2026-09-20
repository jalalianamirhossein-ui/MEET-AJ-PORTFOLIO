<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;

/**
 * Keep CSRF verification active for form endpoints while running the feature
 * suite as well as in normal HTTP requests.
 */
class EnforceCsrfToken extends ValidateCsrfToken
{
    protected function runningUnitTests(): bool
    {
        return false;
    }
}
