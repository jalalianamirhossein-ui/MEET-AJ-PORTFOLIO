<?php
// Run with the same PHP binary used for Composer/Artisan on the hosting account.
$checks = ['php_8_4_or_newer' => PHP_VERSION_ID >= 80400];
foreach (['ctype', 'curl', 'dom', 'fileinfo', 'filter', 'hash', 'intl', 'mbstring', 'openssl', 'pcre', 'pdo', 'pdo_mysql', 'session', 'tokenizer', 'xml', 'xmlwriter'] as $extension) {
    $checks['extension_'.$extension] = extension_loaded($extension);
}
foreach (['storage/app', 'storage/framework/cache/data', 'storage/framework/sessions', 'storage/framework/views', 'storage/logs', 'bootstrap/cache'] as $directory) {
    $checks['writable_'.$directory] = is_dir(__DIR__.'/../'.$directory) && is_writable(__DIR__.'/../'.$directory);
}
echo json_encode(['php' => PHP_VERSION, 'checks' => $checks], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
exit(in_array(false, $checks, true) ? 1 : 0);
