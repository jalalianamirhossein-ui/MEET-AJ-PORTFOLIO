<?php

// Used by setup_env.sh for new installations. Never prints environment values.
require __DIR__.'/../vendor/autoload.php';

try {
    $directory = realpath($argv[1] ?? getcwd());
    if ($directory === false) {
        throw new RuntimeException('Project directory not found.');
    }
    $destination = $directory.'/.env';
    if (file_exists($destination) || is_link($destination)) {
        throw new RuntimeException('Existing .env was preserved; use the update procedure.');
    }
    $contents = @file_get_contents($directory.'/.env.production.example');
    if ($contents === false) {
        throw new RuntimeException('Production template not found.');
    }
    $values = ['DB_HOST' => '127.0.0.1'];
    foreach (['APP_URL', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD', 'CONTACT_NOTIFICATION_EMAIL'] as $key) {
        $value = getenv('MEETAJ_SETUP_'.$key);
        if ($value === false) {
            throw new RuntimeException('Missing setup input: '.$key);
        }
        $values[$key] = $value;
    }
    foreach ($values as $key => $value) {
        $quoted = '"'.str_replace(
            ['\\', '"', '$', "\r", "\n"],
            ['\\\\', '\\"', '\\$', '\\r', '\\n'],
            $value,
        ).'"';
        $contents = preg_replace_callback('/^'.preg_quote($key, '/').'=.*$/m', fn () => $key.'='.$quoted, $contents, -1, $count);
        if ($count !== 1) {
            throw new RuntimeException('Template must contain exactly one '.$key.' entry.');
        }
    }
    try {
        $parsed = Dotenv\Dotenv::parse($contents);
    } catch (Throwable) {
        throw new RuntimeException('Environment validation failed; no file was written.');
    }
    foreach ($values as $key => $value) {
        if (($parsed[$key] ?? null) !== $value) {
            throw new RuntimeException('Environment value did not round-trip: '.$key);
        }
    }
    umask(0077);
    $handle = @fopen($destination, 'x');
    if ($handle === false) {
        throw new RuntimeException('Cannot create .env; an existing file will not be overwritten.');
    }
    $written = fwrite($handle, $contents);
    fclose($handle);
    if ($written !== strlen($contents)) {
        unlink($destination);
        throw new RuntimeException('Environment write failed.');
    }
    echo "Created .env from the production template.\n";
} catch (RuntimeException $error) {
    fwrite(STDERR, $error->getMessage()."\n");
    exit(1);
}
