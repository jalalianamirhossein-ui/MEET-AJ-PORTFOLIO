<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$rows = DB::table('services')
    ->select('slug', 'title', 'price', 'price_currency', 'price_label', 'price_type', 'presentation')
    ->orderBy('sort_order')
    ->get();

foreach ($rows as $r) {
    $pres = json_decode((string) $r->presentation, true) ?: [];
    echo $r->slug.' | '.$r->title.' | '.$r->price.' '.$r->price_currency
        .' | label='.($r->price_label ?? '-')
        .' | type='.($r->price_type ?? '-')
        .' | presKeys='.implode(',', array_keys($pres))
        .PHP_EOL;
}

echo PHP_EOL.'article presentation keys: ';
$a = DB::table('articles')->value('presentation');
echo implode(',', array_keys(json_decode((string) $a, true) ?: [])).PHP_EOL;

echo 'article seo_data keys: ';
$s = DB::table('articles')->value('seo_data');
echo implode(',', array_keys(json_decode((string) $s, true) ?: [])).PHP_EOL;
