<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$out = [];
$out['driver'] = DB::getDriverName();
$out['database'] = DB::getDatabaseName();

$tables = [];
foreach (Schema::getTables() as $t) {
    $name = $t['name'];
    $cols = [];
    foreach (Schema::getColumns($name) as $c) {
        $cols[] = $c['name'].':'.$c['type'].($c['nullable'] ? ' null' : '');
    }
    $idx = [];
    foreach (Schema::getIndexes($name) as $i) {
        $idx[] = $i['name'].'('.implode(',', $i['columns']).')'.($i['unique'] ? ' UNIQUE' : '').($i['primary'] ? ' PRIMARY' : '');
    }
    $fks = [];
    foreach (Schema::getForeignKeys($name) as $f) {
        $fks[] = implode(',', $f['columns']).' -> '.$f['foreign_table'].'('.implode(',', $f['foreign_columns']).') onDelete='.($f['on_delete'] ?? '-');
    }
    $tables[$name] = [
        'rows' => DB::table($name)->count(),
        'columns' => $cols,
        'indexes' => $idx,
        'foreign_keys' => $fks,
    ];
}
$out['tables'] = $tables;

$out['articles'] = [
    'total' => DB::table('articles')->count(),
    'by_language_status' => DB::table('articles')->selectRaw('language, status, count(*) as c')->groupBy('language', 'status')->get()->toArray(),
    'with_published_at' => DB::table('articles')->whereNotNull('published_at')->count(),
    'redirects' => DB::table('article_redirects')->count(),
];

$out['categories'] = DB::table('categories')->select('id', 'name', 'slug', 'language', 'translation_key')->get()->toArray();
$out['tags'] = DB::table('tags')->select('id', 'name', 'slug')->orderBy('name')->get()->toArray();
$out['article_tag_links'] = DB::table('article_tag')->count();

$out['services'] = DB::table('services')
    ->select('id', 'slug', 'title', 'language', 'status', 'price', 'price_currency', 'price_unit', 'sort_order')
    ->orderBy('sort_order')
    ->get()
    ->toArray();

$out['requests'] = [
    'total' => DB::table('requests')->count(),
    'by_status' => DB::table('requests')->selectRaw('status, count(*) as c')->groupBy('status')->get()->toArray(),
];

$out['users'] = DB::table('users')->selectRaw('role, count(*) as c')->groupBy('role')->get()->toArray();

file_put_contents(__DIR__.'/_docsync_probe.json', json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
echo "written\n";