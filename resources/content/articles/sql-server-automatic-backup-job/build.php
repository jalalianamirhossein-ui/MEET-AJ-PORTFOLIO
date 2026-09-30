<?php

// Read the article from its original HTML file; this package is not another article.
$directory = __DIR__;
$metadata = json_decode(file_get_contents($directory.'/metadata.json'), true, 512, JSON_THROW_ON_ERROR);
$escape = fn (string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$source = file_get_contents($directory.'/../../../legacy/articles/sql-server-automatic-backup-job.html');
if (! preg_match('~<article\b[^>]*class="article-body"[^>]*>(.*?)</article>~is', $source, $body)) {
    throw new RuntimeException('Original article body is missing.');
}
$content = preg_replace('~<footer\b[^>]*class="article-footer"[^>]*>.*?</footer>~is', '', $body[1]);
$questions = [];
foreach ($metadata['faq'] as $item) {
    $questions[] = ['@type' => 'Question', 'name' => $item['question'], 'acceptedAnswer' => [
        '@type' => 'Answer', 'text' => $item['answer'],
    ]];
}
$dom = new DOMDocument;
@$dom->loadHTML('<?xml encoding="UTF-8">'.$content, LIBXML_NONET);
$toc = '';
foreach ((new DOMXPath($dom))->query('//section[@id]/h2') as $heading) {
    $id = $heading->parentNode->getAttribute('id');
    $toc .= '<li class="article-nav-item"><a href="#'.$escape($id).'"><span>'
        .$escape(trim($heading->textContent)).'</span></a></li>';
}

return [
    'title' => $metadata['title_en'],
    'excerpt' => $metadata['excerpt_en'],
    'content' => $content,
    'meta_title' => $metadata['meta_title'],
    'meta_description' => $metadata['meta_description'],
    'presentation' => [
        'content_language' => 'fa',
        'hero_title_en' => $metadata['title_en'], 'hero_title_fa' => $metadata['title_fa'],
        'card_title_en' => $metadata['title_en'], 'card_title_fa' => $metadata['title_fa'],
        'card_excerpt_en' => $metadata['excerpt_en'], 'card_excerpt_fa' => $metadata['excerpt_fa'],
        'original_excerpt' => $metadata['excerpt_en'],
        'excerpt_translations' => ['fa' => $metadata['excerpt_fa']],
        'toc_html' => $toc,
        'editorial_package' => 'sql-server-automatic-backup-job',
    ],
    'seo_data' => [
        'og_title' => $metadata['meta_title'], 'og_description' => $metadata['meta_description'],
        'twitter_title' => $metadata['meta_title'], 'twitter_description' => $metadata['meta_description'],
        'schema' => [
            '@context' => 'https://schema.org', '@type' => 'Article', 'inLanguage' => 'fa',
            'headline' => $metadata['title_fa'], 'description' => $metadata['meta_description'],
        ],
        'faq_schema' => [
            '@context' => 'https://schema.org', '@type' => 'FAQPage', 'inLanguage' => 'fa',
            'mainEntity' => $questions,
        ],
    ],
];
