<?php

namespace Tests\Feature;

use Dotenv\Dotenv;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class ProductionEnvironmentSetupTest extends TestCase
{
    public function test_setup_preserves_literal_secrets_and_refuses_to_replace_an_existing_environment(): void
    {
        $directory = storage_path('framework/testing/env-'.bin2hex(random_bytes(8)));
        File::ensureDirectoryExists($directory);
        try {
            File::copy(base_path('.env.production.example'), $directory.'/.env.production.example');
            $password = 'spaces # "quotes" $'.'{APP_NAME} \\ and apostrophe\'';
            $environment = [
                'MEETAJ_SETUP_APP_URL' => 'https://example.test',
                'MEETAJ_SETUP_DB_DATABASE' => 'production',
                'MEETAJ_SETUP_DB_USERNAME' => 'site-user',
                'MEETAJ_SETUP_DB_PASSWORD' => $password,
                'MEETAJ_SETUP_CONTACT_NOTIFICATION_EMAIL' => '',
            ];
            $command = [PHP_BINARY, base_path('scripts/write-production-env.php'), $directory];
            $first = new Process($command, base_path(), $environment);
            $first->run();
            $this->assertTrue($first->isSuccessful(), $first->getErrorOutput());
            $parsed = Dotenv::parse(File::get($directory.'/.env'));
            $this->assertSame($password, $parsed['DB_PASSWORD']);
            $this->assertSame('https://example.test', $parsed['APP_URL']);
            $this->assertSame('127.0.0.1', $parsed['DB_HOST']);
            $this->assertStringNotContainsString($password, $first->getOutput().$first->getErrorOutput());

            File::append($directory.'/.env', "\n# Existing installation: preserve key and settings\n");
            $before = hash_file('sha256', $directory.'/.env');
            $second = new Process($command, base_path(), $environment);
            $second->run();
            $this->assertSame(1, $second->getExitCode());
            $this->assertSame($before, hash_file('sha256', $directory.'/.env'));
        } finally {
            File::deleteDirectory($directory);
        }
    }
}
