<?php

namespace App\Services;

use App\Models\Article;

class ArticleLocalization
{
    public function apply(Article $article, string $locale): Article
    {
        $localizations = data_get($article->presentation, 'localizations');
        if (! is_array($localizations) || ! isset($localizations['fa'], $localizations['en'])) {
            return $article;
        }

        $locale = $locale === 'en' ? 'en' : 'fa';
        $text = $localizations[$locale];
        $base = $article->publicUrl();
        $canonical = $base.($locale === 'en' ? '?lang=en' : '');
        // Only this request's model is changed; the database identity stays shared.
        $article->content = $this->html((string) $article->content, $locale);
        $article->meta_title = $text['meta_title'];
        $article->meta_description = $text['description'];
        $article->canonical_url = $canonical;
        $article->presentation = array_replace($article->presentation ?? [], [
            'content_language' => $locale,
            'toc_html' => $this->html((string) data_get($article->presentation, 'toc_html'), $locale),
            'hero_title_en' => $localizations['en']['title'],
            'hero_title_fa' => $localizations['fa']['title'],
            'excerpt_translations' => ['fa' => $localizations['fa']['description']],
        ]);
        $article->excerpt = $localizations['en']['description'];
        $article->seo_data = array_replace($article->seo_data ?? [], [
            'og_title' => $text['title'], 'og_description' => $text['description'],
            'twitter_title' => $text['title'], 'twitter_description' => $text['description'],
            'keywords' => implode(', ', $text['keywords']),
            'alternates' => ['fa' => $base, 'en' => $base.'?lang=en', 'x-default' => $base],
            'schema' => array_replace(data_get($article->seo_data, 'schema', []) ?? [], [
                '@context' => 'https://schema.org', '@type' => 'Article',
                'headline' => $text['title'], 'description' => $text['description'],
                'inLanguage' => $locale, 'url' => $canonical, 'mainEntityOfPage' => $canonical,
            ]),
            'faq_schema' => [
                '@context' => 'https://schema.org', '@type' => 'FAQPage', 'inLanguage' => $locale,
                'mainEntity' => array_map(fn ($pair) => [
                    '@type' => 'Question', 'name' => $pair[0],
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => $pair[1]],
                ], $text['faq']),
            ],
        ]);

        return $article;
    }

    public function html(string $html, string $locale): string
    {
        // Executable blocks remain byte-for-byte identical across locales.
        $protected = [];
        $html = preg_replace_callback('~<pre\b.*?</pre>~is', function ($match) use (&$protected) {
            $key = '__ARTICLE_CODE_'.count($protected).'__';
            $protected[$key] = $match[0];

            return $key;
        }, $html) ?? $html;
        // Historical content remains in its original languages, visibly labeled.
        $html = preg_replace_callback('~(<details\b[^>]*id="legacy-history"[^>]*>)(.*?)(</details>)~is', function ($match) use (&$protected) {
            $parts = explode('</summary>', $match[2], 2);
            if (count($parts) !== 2) {
                return $match[0];
            }
            $key = '__ARTICLE_HISTORY__';
            $protected[$key] = $parts[1];

            return $match[1].$parts[0].'</summary>'.$key.$match[3];
        }, $html) ?? $html;
        $html = preg_replace_callback('~<(h[1-6]|p|span|li|th|td|summary)\b([^>]*\bdata-'.$locale.'="([^"]*)"[^>]*)>(.*?)</\1>~is', function ($match) use ($locale) {
            $value = html_entity_decode($match[3], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            // Keep source citation links when replacing the surrounding prose.
            preg_match_all('~<a\b[^>]*href="https://[^"]*"[^>]*>.*?</a>~is', $match[4], $links);
            if ($locale === 'en') {
                $links[0] = array_map(fn ($link) => preg_match('/\p{Arabic}/u', strip_tags($link))
                    ? preg_replace('~(>).*?(</a>)~s', '$1Official documentation$2', $link) : $link, $links[0]);
            }
            preg_match_all('~<i\b[^>]*aria-hidden="true"[^>]*>.*?</i>~is', $match[4], $icons);

            return '<'.$match[1].$match[2].'>'.htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                .($links[0] ? ' '.implode(' ', $links[0]) : '').implode('', $icons[0]).'</'.$match[1].'>';
        }, $html) ?? $html;
        $html = str_replace('lang="fa" dir="rtl"', 'lang="'.$locale.'" dir="'.($locale === 'fa' ? 'rtl' : 'ltr').'"', $html);

        return strtr($html, $protected);
    }
}
