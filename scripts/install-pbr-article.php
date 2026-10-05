<?php

// Preview: php scripts/install-pbr-article.php
// Apply to this environment: php scripts/install-pbr-article.php --apply
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use App\Services\LegacyArticleImporter;
use Illuminate\Support\Facades\DB;

$slug = 'mikrotik-ping-triggered-policy-routing';
if (! in_array('--apply', $argv, true)) {
    echo json_encode(app(LegacyArticleImporter::class)->import(true, true, [$slug]), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).PHP_EOL;
    exit(0);
}

$directory = storage_path('app/private/article-revisions');
if (! is_dir($directory) && ! mkdir($directory, 0700, true) && ! is_dir($directory)) {
    throw new RuntimeException('Cannot create backup directory.');
}
$backup = $directory.'/before-pbr-'.gmdate('Ymd-His').'-'.bin2hex(random_bytes(4));
if (DB::connection()->getDriverName() === 'sqlite') {
    $pdo = DB::connection()->getPdo();
    $pdo->exec('VACUUM INTO '.$pdo->quote($backup.'.sqlite'));
} else {
    // Capture all article records affected by import/tag synchronization.
    $snapshot = ['articles' => DB::table('articles')->get(), 'categories' => DB::table('categories')->get(),
        'tags' => DB::table('tags')->get(), 'article_tag' => DB::table('article_tag')->get(),
        'article_redirects' => DB::table('article_redirects')->get()];
    if (file_put_contents($backup.'.json', json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)) === false) {
        throw new RuntimeException('Cannot save content snapshot.');
    }
}
DB::transaction(function () use ($slug): void {
    $result = app(LegacyArticleImporter::class)->import(false, true, [$slug]);
    Article::where('slug', $slug)->where('language', 'en')->update(['sort_order' => 0]);
    Article::where('slug', 'linux-security-auditor-bash')->where('language', 'en')->update(['sort_order' => 1]);
    $first = app(App\Http\Controllers\ArticleController::class)->index(Illuminate\Http\Request::create('/articles'))->getData()['articles']->first();
    if ($first?->slug !== $slug) {
        throw new RuntimeException('First article verification failed; import rolled back.');
    }
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).PHP_EOL;
});
echo 'Backup: '.$backup.PHP_EOL.'Verified: PBR article is first.'.PHP_EOL;
