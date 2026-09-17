<?php

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$rows = App\Models\Request::query()
    ->whereIn('email', [
        'browser-contact@example.com',
        'browser-service@example.com',
        'live-qa-contact@example.com',
        'live-qa-service@example.com',
    ])
    ->get(['id', 'name', 'email', 'subject', 'service_id', 'status', 'created_at']);

foreach ($rows as $row) {
    echo json_encode($row->toArray(), JSON_UNESCAPED_UNICODE), PHP_EOL;
}
echo 'count='.$rows->count().PHP_EOL;
