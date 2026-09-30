<?php

// Preview: php scripts/update-sql-backup-article.php
// Apply to this environment only: php scripts/update-sql-backup-article.php --apply
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use App\Services\ArticleHtmlSanitizer;
use Illuminate\Support\Facades\DB;

$package = require __DIR__.'/../resources/content/articles/sql-server-automatic-backup-job/build.php';
$package['content'] = app(ArticleHtmlSanitizer::class)->sanitize($package['content']);
$article = Article::where('slug', 'sql-server-automatic-backup-job')->firstOrFail();
echo 'Target: '.$article->path().PHP_EOL.'Content bytes: '.strlen($package['content']).PHP_EOL;
if (! in_array('--apply', $argv, true)) {
    echo 'Preview only. Use --apply to update this article; no SQL/PowerShell is executed.'.PHP_EOL;
    exit(0);
}
DB::transaction(function () use ($article, $package): void {
    $article = Article::whereKey($article->id)->lockForUpdate()->firstOrFail();
    $directory = storage_path('app/private/article-revisions');
    if (! is_dir($directory) && ! mkdir($directory, 0700, true) && ! is_dir($directory)) {
        throw new RuntimeException('Cannot create revision directory.');
    }
    $revision = $directory.'/sql-backup-'.gmdate('Ymd-His').'-'.bin2hex(random_bytes(4)).'.json';
    if (file_put_contents($revision, json_encode($article->getAttributes(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)) === false) {
        throw new RuntimeException('Cannot save original article revision.');
    }
    $presentation = array_replace($article->presentation ?? [], $package['presentation']);
    $seo = array_replace($article->seo_data ?? [], $package['seo_data']);
    $seo['schema']['datePublished'] = $article->published_at?->toIso8601String();
    $seo['schema']['dateModified'] = now()->toIso8601String();
    $article->fill(array_replace($package, ['presentation' => $presentation, 'seo_data' => $seo]));
    $article->save();
    echo 'Updated only the existing article. Original revision: '.$revision.PHP_EOL;
});
