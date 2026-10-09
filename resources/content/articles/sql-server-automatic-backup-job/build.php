<?php

// Read the article from its original HTML file; this package is not another article.
$directory = __DIR__;
$metadata = json_decode(file_get_contents($directory.'/metadata.json'), true, 512, JSON_THROW_ON_ERROR);
$escape = fn (string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$source = file_get_contents($directory.'/../../../legacy/articles/sql-server-automatic-backup-job.html');
preg_match('~<script\b[^>]*id="article-localizations"[^>]*>(.*?)</script>~is', $source, $localized);
$localizations = isset($localized[1]) ? json_decode($localized[1], true, 512, JSON_THROW_ON_ERROR) : null;
$default = $localizations['en'] ?? [
    'title' => $metadata['title_en'], 'meta_title' => $metadata['meta_title'],
    'description' => $metadata['meta_description'],
    'faq' => array_map(fn ($item) => [$item['question'], $item['answer']], $metadata['faq']),
];
if (! preg_match('~<article\b[^>]*class="article-body"[^>]*>(.*?)</article>~is', $source, $body)) {
    throw new RuntimeException('Original article body is missing.');
}
$content = preg_replace('~<footer\b[^>]*class="article-footer"[^>]*>.*?</footer>~is', '', $body[1]);
require_once dirname(__DIR__, 4).'/app/Services/ArticleStructure.php';
$content = (new \App\Services\ArticleStructure)->repair($content, 'sql-server-automatic-backup-job');
$questions = [];
foreach ($default['faq'] as [$question, $answer]) {
    $questions[] = ['@type' => 'Question', 'name' => $question, 'acceptedAnswer' => [
        '@type' => 'Answer', 'text' => $answer,
    ]];
}
$dom = new DOMDocument;
@$dom->loadHTML('<?xml encoding="UTF-8">'.$content, LIBXML_NONET);
$toc = '';
foreach ((new DOMXPath($dom))->query('//section[@id]/h2') as $heading) {
    $id = $heading->parentNode->getAttribute('id');
    $translations = $heading->hasAttribute('data-en')
        ? ' data-en="'.$escape($heading->getAttribute('data-en')).'" data-fa="'.$escape($heading->getAttribute('data-fa')).'"'
        : '';
    $toc .= '<li class="article-nav-item"><a href="#'.$escape($id).'"><span'.$translations.'>'
        .$escape(trim($heading->textContent)).'</span></a></li>';
}

return [
    'title' => $default['title'],
    'excerpt' => $default['description'],
    'content' => $content,
    'meta_title' => $default['meta_title'],
    'meta_description' => $default['description'],
    'presentation' => [
        'localizations' => $localizations,
        'content_language' => 'en',
        'hero_title_en' => $default['title'], 'hero_title_fa' => $localizations['fa']['title'] ?? $metadata['title_fa'],
        'card_title_en' => $default['title'], 'card_title_fa' => $localizations['fa']['title'] ?? $metadata['title_fa'],
        'card_excerpt_en' => $metadata['excerpt_en'], 'card_excerpt_fa' => $metadata['excerpt_fa'],
        'original_excerpt' => $metadata['excerpt_en'],
        'excerpt_translations' => ['fa' => $metadata['excerpt_fa']],
        'toc_html' => $toc,
        'editorial_package' => 'sql-server-automatic-backup-job',
    ],
    'seo_data' => [
        'og_title' => $default['meta_title'], 'og_description' => $default['description'],
        'twitter_title' => $default['meta_title'], 'twitter_description' => $default['description'],
        'schema' => [
            '@context' => 'https://schema.org', '@type' => 'Article', 'inLanguage' => 'en',
            'headline' => $default['title'], 'description' => $default['description'],
        ],
        'faq_schema' => [
            '@context' => 'https://schema.org', '@type' => 'FAQPage', 'inLanguage' => 'en',
            'mainEntity' => $questions,
        ],
    ],
];
