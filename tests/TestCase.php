<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\RateLimiter;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        // PHPUnit's forced <env> values update getenv() and $_ENV, but not
        // inherited $_SERVER entries. Laravel reads those entries first.
        // Synchronize the configured test values before the application boots,
        // including when launched from an already-booted Artisan process.
        foreach ([
            'APP_ENV', 'APP_KEY', 'APP_URL',
            'DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD',
            'CACHE_STORE', 'SESSION_DRIVER', 'SESSION_SECURE_COOKIE',
            'BCRYPT_ROUNDS', 'MAIL_MAILER', 'CONTACT_NOTIFICATION_EMAIL',
        ] as $name) {
            if (array_key_exists($name, $_ENV)) {
                $_SERVER[$name] = $_ENV[$name];
            }
        }

        $connection = (string) ($_SERVER['DB_CONNECTION'] ?? $_ENV['DB_CONNECTION'] ?? getenv('DB_CONNECTION') ?: '');
        if ($connection !== 'mysql') {
            // Keep the default suite on an isolated in-memory database.
            putenv('DB_CONNECTION=sqlite');
            putenv('DB_DATABASE=:memory:');
            $_ENV['DB_CONNECTION'] = 'sqlite';
            $_ENV['DB_DATABASE'] = ':memory:';
            $_SERVER['DB_CONNECTION'] = 'sqlite';
            $_SERVER['DB_DATABASE'] = ':memory:';
        }

        parent::setUp();
        RateLimiter::clear('contact:127.0.0.1');
    }
}
