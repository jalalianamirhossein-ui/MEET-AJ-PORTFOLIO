<?php
return [
    'default' => env('DB_CONNECTION', 'mysql'),
    'connections' => [
        'mysql' => [
            'driver' => 'mysql', 'url' => env('DB_URL'), 'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'), 'database' => env('DB_DATABASE', 'meetaj'),
            'username' => env('DB_USERNAME', 'meetaj'), 'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''), 'charset' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '', 'prefix_indexes' => true, 'strict' => true, 'engine' => null,
        ],
        'sqlite' => [
            'driver' => 'sqlite',
            'database' => (($database = env('DB_DATABASE', database_path('database.sqlite'))) === ':memory:')
                ? ':memory:'
                : ((preg_match('/^(?:[A-Za-z]:[\\\\\\/]|\\/)/', (string) $database) ? $database : base_path($database))),
            'prefix' => '',
            'foreign_key_constraints' => true,
            'busy_timeout' => 5000,
        ],
    ],
    'migrations' => ['table' => 'migrations', 'update_date_on_publish' => true],
];
