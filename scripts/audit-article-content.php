<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

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
        $rows[] = [
            'slug' => $article->slug, 'locale' => $locale,
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
$path = storage_path('app/article-content-audit-'.($argv[1] ?? 'current').'.json');
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
);
echo 'Editions with structural problems: '.count($issues).PHP_EOL;
exit($issues === [] ? 0 : 1);
