<?php

namespace App\Services;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

class ArticleHtmlSanitizer
{
    public function sanitize(string $html): string
    {
        $config = (new HtmlSanitizerConfig)
            ->allowSafeElements()
            ->allowRelativeLinks()
            ->allowRelativeMedias()
            ->allowMediaSchemes(['http', 'https'])
            ->allowElement('button', ['type'])
            ->allowElement('section')
            ->allowElement('article')
            ->allowElement('nav')
            ->allowElement('footer')
            ->allowElement('bdi')
            ->allowAttribute('class', '*')
            ->allowAttribute('id', '*')
            ->allowAttribute('style', '*')
            ->allowAttribute('data-en', '*')
            ->allowAttribute('data-fa', '*')
            ->allowAttribute('data-en-alt', '*')
            ->allowAttribute('data-fa-alt', '*')
            ->allowAttribute('data-en-title', '*')
            ->allowAttribute('data-fa-title', '*')
            ->allowAttribute('data-en-aria-label', '*')
            ->allowAttribute('data-fa-aria-label', '*')
            ->allowAttribute('lang', '*')
            ->allowAttribute('dir', '*')
            ->allowAttribute('scope', '*')
            ->allowAttribute('aria-labelledby', '*')
            ->allowAttribute('data-aos', '*')
            ->allowAttribute('data-aos-delay', '*')
            ->allowAttribute('aria-hidden', '*')
            ->allowAttribute('aria-label', '*')
            ->allowAttribute('role', '*')
            ->allowAttribute('datetime', '*')
            ->allowAttribute('target', '*')
            ->allowAttribute('rel', '*')
            ->allowAttribute('loading', '*')
            ->allowAttribute('decoding', '*')
            ->allowAttribute('width', '*')
            ->allowAttribute('height', '*')
            ->allowAttribute('alt', '*')
            ->allowAttribute('src', '*')
            ->allowAttribute('href', '*')
            ->allowAttribute('title', '*')
            ->allowAttribute('download', ['a'])
            ->forceAttribute('a', 'rel', 'noopener noreferrer')
            ->withMaxInputLength(2_000_000);

        // Retain exact whitespace only for code blocks with inert markup.
        // Arbitrary HTML inside <pre> must still pass through the sanitizer.
        $blocks = [];
        $prefix = 'ARTICLE_SAFE_CODE_'.bin2hex(random_bytes(16)).'_';
        $html = preg_replace_callback('~<pre\b[^>]*>.*?</pre>~is', function ($match) use (&$blocks, $prefix) {
            if (! $this->isSafeCodeBlock($match[0])) {
                return $match[0];
            }
            $token = $prefix.count($blocks);
            $blocks[$token] = $match[0];

            return $token;
        }, $html) ?? $html;

        $safe = (new HtmlSanitizer($config))->sanitize($html);

        // Restore placeholders only in text, never in an attribute or URL.
        return preg_replace_callback('~(^|>)([^<]*)~s',
            fn ($match) => $match[1].strtr($match[2], $blocks), $safe) ?? $safe;
    }

    private function isSafeCodeBlock(string $block): bool
    {
        $dom = new \DOMDocument;
        @$dom->loadHTML('<?xml encoding="UTF-8"><div>'.$block.'</div>', LIBXML_NONET);
        foreach ($dom->getElementsByTagName('*') as $element) {
            if (! in_array($element->tagName, ['html', 'body', 'div', 'pre', 'code', 'span', 'br'], true)) {
                return false;
            }
            foreach ($element->attributes as $attribute) {
                if ($attribute->name === 'style' && preg_match('/^\s*text-align\s*:\s*(?:left|right|center)\s*;?\s*$/i', $attribute->value)) {
                    continue;
                }
                if (! in_array($attribute->name, ['class', 'id', 'lang', 'dir', 'title'], true)) {
                    return false;
                }
            }
        }

        return true;
    }
}
