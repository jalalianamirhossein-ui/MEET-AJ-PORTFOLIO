<?php

namespace App\Services;

use App\Models\Article;

class ArticleLocalization
{
    public function apply(Article $article, string $locale, bool $renderContent = true): Article
    {
        $localizations = data_get($article->presentation, 'localizations');
        if (! is_array($localizations) || ! isset($localizations['fa'], $localizations['en'])) {
            return $article;
        }

        $locale = $locale === 'fa' ? 'fa' : 'en';
        $text = $localizations[$locale];
        $base = $article->publicUrl();
        $canonical = $base.($locale === 'fa' ? '?lang=fa' : '');
        // Only this request's model is changed; the database identity stays shared.
        if ($renderContent) {
            $article->content = $this->html((string) $article->content, $locale);
        }
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
            'alternates' => ['fa' => $base.'?lang=fa', 'en' => $base, 'x-default' => $base],
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
        // Also remove the retired section from previously imported database rows.
        if (preg_match('~<details\b[^>]*\bid="legacy-history"[^>]*>~i', $html, $start, PREG_OFFSET_CAPTURE)) {
            $offset = $start[0][1];
            $cursor = $offset + strlen($start[0][0]);
            $depth = 1;
            preg_match_all('~</?details\b[^>]*>~i', substr($html, $cursor), $tags, PREG_OFFSET_CAPTURE);
            foreach ($tags[0] as [$tag, $position]) {
                $depth += str_starts_with($tag, '</') ? -1 : 1;
                if ($depth === 0) {
                    $html = substr($html, 0, $offset).substr($html, $cursor + $position + strlen($tag));
                    break;
                }
            }
        }
        // Executable blocks remain byte-for-byte identical across locales.
        $protected = [];
        $html = preg_replace_callback('~<pre\b.*?</pre>~is', function ($match) use (&$protected) {
            $key = '__ARTICLE_CODE_'.count($protected).'__';
            $protected[$key] = $match[0];

            return $key;
        }, $html) ?? $html;
        $html = preg_replace_callback('~<(h[1-6]|p|span|li|th|td|summary)\b([^>]*\bdata-'.$locale.'="([^"]*)"[^>]*)>(.*?)</\1\s*>~is', function ($match) use ($locale) {
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
        $html = preg_replace('~lang="(?:fa|en)" dir="(?:rtl|ltr)"~', 'lang="'.$locale.'" dir="'.($locale === 'fa' ? 'rtl' : 'ltr').'"', $html) ?? $html;

        return strtr($html, $protected);
    }
}
