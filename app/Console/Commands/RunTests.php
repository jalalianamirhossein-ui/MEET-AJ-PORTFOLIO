<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RunTests extends Command
{
    protected $signature = 'test {args?* : Arguments forwarded to PHPUnit}';

    protected $description = 'Run the PHPUnit suite (php artisan test)';

    public function handle(): int
    {
        $phpunit = base_path('vendor/phpunit/phpunit/phpunit');
        if (! is_file($phpunit)) {
            $this->error('PHPUnit is not installed. Run composer install.');

            return self::FAILURE;
        }

        $args = $this->argument('args') ?? [];
        $command = array_merge([PHP_BINARY, $phpunit], $args);
        $cmd = implode(' ', array_map('escapeshellarg', $command));
        passthru($cmd, $code);

        return $code === 0 ? self::SUCCESS : self::FAILURE;
    }
}
