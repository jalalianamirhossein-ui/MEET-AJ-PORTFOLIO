<?php

// Render the editorial source and executable snippets without duplicating code.
$directory = __DIR__;
$metadata = json_decode(file_get_contents($directory.'/metadata.json'), true, 512, JSON_THROW_ON_ERROR);
$escape = fn (string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$content = preg_replace_callback('/\{\{CODE:([a-z0-9.-]+)\}\}/', function (array $match) use ($directory, $escape): string {
    $file = $directory.'/'.$match[1];
    if (! is_file($file)) {
        throw new RuntimeException('Missing article code file: '.$match[1]);
    }
    $language = str_ends_with($file, '.sql') ? 'sql' : 'powershell';

    return '<div class="article-code"><div class="article-code-header"><span class="article-code-label">'
        .$escape($match[1]).'</span><button type="button" class="article-copy-button" data-en="Copy" data-fa="کپی">کپی</button></div>'
        .'<pre><code class="language-'.$language.'">'.$escape(file_get_contents($file)).'</code></pre></div>';
}, file_get_contents($directory.'/article.html'));
$faqHtml = '';
$questions = [];
foreach ($metadata['faq'] as $item) {
    $faqHtml .= '<div class="article-faq-item"><h3 class="article-faq-question">'.$escape($item['question'])
        .'<i class="bi bi-chevron-down" aria-hidden="true"></i></h3><div class="article-faq-answer"><p>'
        .$escape($item['answer']).'</p></div></div>';
    $questions[] = ['@type' => 'Question', 'name' => $item['question'], 'acceptedAnswer' => [
        '@type' => 'Answer', 'text' => $item['answer'],
    ]];
}
$content = str_replace('{{FAQ}}', '<div class="article-faq-list">'.$faqHtml.'</div>', $content);
if (str_contains($content, '{{')) {
    throw new RuntimeException('Unresolved article template token.');
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
