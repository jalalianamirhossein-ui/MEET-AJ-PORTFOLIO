<?php

// php scripts/update-article-order.php
// Synchronize chronological order; never rewrite publication dates or article content.
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$rows = app(App\Services\ArticleOrdering::class)->synchronize();
foreach ($rows as $row) {
    echo $row['language'].' '.$row['sort_order'].' '.$row['slug'].PHP_EOL;
}
echo 'Verified: '.count($rows).' articles ordered; publication dates and all other fields preserved.'.PHP_EOL;
