<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$reportName = preg_replace('/[^a-zA-Z0-9_-]/', '', $argv[1] ?? 'current');
$path = storage_path('app/article-content-audit-'.$reportName.'.json');

if (in_array('--isolated', $argv, true)) {
    config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'cache.default' => 'array']);
    Illuminate\Support\Facades\DB::purge('sqlite');
    Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    app(App\Services\LegacyArticleImporter::class)->import(false);
} else {
    // No migrations/imports in CMS mode. Refuse remote databases by default.
    $connection = config('database.default');
    $driver = config('database.connections.'.$connection.'.driver');
    $database = config('database.connections.'.$connection.'.database');
    $reason = null;
    if ($driver !== 'sqlite' && ! in_array('--allow-remote-read-only', $argv, true)) {
        $reason = 'Remote CMS audit not authorized; explicitly opt into a read-only connection on the operator-controlled database.';
    } elseif ($driver === 'sqlite' && ($database === ':memory:' || ! is_file($database))) {
        $reason = 'Configured local CMS database is unavailable; CMS-only records were not inspected.';
    }
    if ($reason) {
        file_put_contents($path, json_encode(['status'=>'unavailable','articles_audited'=>0,'reason'=>$reason], JSON_PRETTY_PRINT));
        fwrite(STDERR, $reason.PHP_EOL.'Report: '.$path.PHP_EOL); exit(2);
    }
    try {
        $db = Illuminate\Support\Facades\DB::connection();
        if ($driver === 'sqlite') { $db->statement('PRAGMA query_only = ON'); }
        elseif ($driver === 'pgsql') { $db->statement('SET default_transaction_read_only = on'); }
        elseif ($driver === 'mysql') { $db->statement('SET SESSION TRANSACTION READ ONLY'); }
        else { throw new RuntimeException('Unsupported read-only database driver'); }
        $db->beginTransaction();
    } catch (Throwable $error) {
        file_put_contents($path, json_encode(['status'=>'unavailable','articles_audited'=>0,'reason'=>'Read-only CMS connection failed.'], JSON_PRETTY_PRINT));
        fwrite(STDERR, 'Read-only CMS connection failed; no records audited. See '.$path.PHP_EOL); exit(2);
    }
}

$rows = [];
foreach (App\Models\Article::with('category')->get() as $article) {
    foreach (['en', 'fa'] as $locale) {
        $localized = app(App\Services\ArticleLocalization::class)->apply(clone $article, $locale);
        $html = $localized->displayContent();
        $dom = new DOMDocument();
        @$dom->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NONET);
        $xp = new DOMXPath($dom);
        $ids = [];
        foreach ($xp->query('//*[@id]') as $node) { $ids[] = $node->getAttribute('id'); }
        $headings = [];
        $labels = [];
        foreach ($xp->query('//h2') as $node) {
            $label = trim(preg_replace('/\s+/u', ' ', $node->textContent));
            $labels[] = mb_strtolower($label);
            $headings[] = ['id' => $node->getAttribute('id') ?: $node->parentNode->getAttribute('id'), 'text' => $label];
        }
        $broken = [];
        $toc = (string) data_get($localized->presentation, 'toc_html', '');
        preg_match_all('/href=["\']#([^"\']+)/', $html.$toc, $links);
        foreach ($links[1] as $target) {
            if (! in_array($target, $ids, true)) { $broken[] = $target; }
        }
        $empty = [];
        foreach ($xp->query('//section[h2]') as $section) {
            if (trim($section->textContent) === trim($xp->evaluate('string(./h2)', $section))
                && $xp->query('.//img|.//pre|.//table', $section)->length === 0) {
                $empty[] = $section->getAttribute('id');
            }
        }
        $paragraphs = [];
        foreach ($xp->query('//p[not(ancestor::footer)]') as $node) {
            $text = trim(preg_replace('/\s+/u', ' ', $node->textContent));
            if (mb_strlen($text) >= 80) { $paragraphs[] = $text; }
        }
        $repeatedProse = array_values(array_unique(array_diff_assoc($paragraphs, array_unique($paragraphs))));
        $emptyFaq = [];
        foreach ($xp->query('//section[@id="faq"]//details') as $item) {
            if (trim($xp->evaluate('string(./div)', $item)) === '') {
                $emptyFaq[] = trim($xp->evaluate('string(./summary)', $item));
            }
        }
        $references = [];
        foreach ($xp->query('//section[@id="official-references"]//a[@href]') as $link) {
            $references[] = $link->getAttribute('href');
        }
        $missingLanguages = [];
        $unlocalizedProse = [];
        foreach ($xp->query('//*[@data-en or @data-fa]') as $node) {
            // Intentionally blank deployment-checklist status cells have no translation.
            if (trim($node->textContent) === '' && $node->getAttribute('data-en') === '' && $node->getAttribute('data-fa') === '') { continue; }
            if (! $node->hasAttribute('data-en') || ! $node->hasAttribute('data-fa')
                || trim($node->getAttribute('data-en')) === '' || trim($node->getAttribute('data-fa')) === '') {
                $missingLanguages[] = $node->tagName.': '.mb_substr($node->textContent, 0, 100);
            }
            if (in_array($node->tagName, ['h2','h3','h4','p','span','a','li','th','td','figcaption','summary'], true)
                && $node->hasAttribute('data-'.$locale) && $xp->query('ancestor::pre', $node)->length === 0) {
                $normalize = fn ($value) => trim(preg_replace('/\s+/u', ' ', $value));
                $expected = $normalize($node->getAttribute('data-'.$locale));
                if ($expected !== '' && ! str_starts_with($normalize($node->textContent), $expected)) {
                    $unlocalizedProse[] = $node->tagName.': '.mb_substr($expected, 0, 100);
                }
            }
        }
        $hierarchy = [];
        $previousLevel = 1;
        foreach ($xp->query('//h2|//h3|//h4|//h5|//h6') as $node) {
            if ($xp->query('ancestor::pre|ancestor::footer', $node)->length > 0) { continue; }
            $level = (int) substr($node->tagName, 1);
            if ($level > $previousLevel + 1) { $hierarchy[] = trim($node->textContent); }
            $previousLevel = $level;
        }
        $structure = app(App\Services\ArticleStructure::class);
        $dependencies = [];
        foreach ([['introduction','conclusion'], ['prerequisites','configuration'], ['architecture','configuration'], ['architecture','troubleshooting'], ['architecture','conclusion'], ['conclusion','faq'], ['faq','official-references']] as [$earlier,$later]) {
            if (in_array($earlier,$ids,true) && in_array($later,$ids,true)
                && strpos($html,'id="'.$earlier.'"') > strpos($html,'id="'.$later.'"')) {
                $dependencies[] = $earlier.' after '.$later;
            }
        }
        $rows[] = [
            'slug' => $article->slug, 'status' => $article->status, 'locale' => $locale,
            'technical_content' => app(App\Services\ArticleTechnicalQuality::class)->issues($html, $article->slug),
            'publication_state' => $article->status === 'published' && $article->published_at?->isFuture() ? 'scheduled' : $article->status,
            'heading_numbering' => $structure->numberingIssues($html),
            'stored_heading_numbering' => $structure->numberingIssues((string) $article->content),
            'section_order' => $structure->order($html, $article->slug) !== $html,
            'visible_review_dates' => $structure->cleanReviews($html) !== $html,
            'stored_review_dates' => $structure->cleanReviews((string) $article->content) !== $article->content,
            'missing_language_variants' => $missingLanguages, 'heading_hierarchy' => $hierarchy,
            'unlocalized_prose' => $unlocalizedProse,
            'dependency_order' => $dependencies,
            'duplicate_ids' => array_values(array_diff_assoc($ids, array_unique($ids))),
            'duplicate_headings' => array_values(array_diff_assoc($labels, array_unique($labels))),
            'broken_anchors' => array_values(array_unique($broken)), 'empty_sections' => $empty,
            'faq_sections' => $xp->query('//section[@id="faq"]')->length,
            'faq_items' => $xp->query('//section[@id="faq"]//details')->length,
            'empty_faq_answers' => $emptyFaq, 'repeated_prose' => $repeatedProse,
            'duplicate_reference_urls' => array_values(array_unique(array_diff_assoc($references, array_unique($references)))),
            'headings' => $headings,
        ];
    }
}
if (isset($db)) { $db->rollBack(); }
file_put_contents($path, json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
foreach ($rows as $row) {
    unset($row['headings']);
    echo json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES).PHP_EOL;
}
echo 'Audited '.count($rows).' article editions; report: '.$path.PHP_EOL;
$issues = array_filter($rows, fn (array $row): bool =>
    $row['duplicate_ids'] !== [] || $row['duplicate_headings'] !== [] || $row['broken_anchors'] !== []
    || $row['empty_sections'] !== [] || $row['empty_faq_answers'] !== [] || $row['duplicate_reference_urls'] !== []
    || $row['faq_sections'] !== 1
    || $row['section_order'] || $row['visible_review_dates'] || $row['stored_review_dates']
    || $row['missing_language_variants'] !== [] || $row['heading_hierarchy'] !== []
    || $row['dependency_order'] !== []
    || $row['unlocalized_prose'] !== []
    || $row['heading_numbering'] !== [] || $row['stored_heading_numbering'] !== []
    || $row['technical_content'] !== []
);
echo 'Editions with structural problems: '.count($issues).PHP_EOL;
exit($issues === [] ? 0 : 1);
