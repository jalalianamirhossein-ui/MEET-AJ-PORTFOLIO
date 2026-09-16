<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\RateLimiter;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        $connection = (string) ($_SERVER['DB_CONNECTION'] ?? $_ENV['DB_CONNECTION'] ?? getenv('DB_CONNECTION') ?: '');
        if ($connection !== 'mysql') {
            // PHPUnit 11 ignores force="true" on <env>, so local .env would
            // otherwise point RefreshDatabase at the live sqlite file.
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
