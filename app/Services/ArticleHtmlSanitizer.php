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
            ->allowAttribute('class', '*')
            ->allowAttribute('id', '*')
            ->allowAttribute('style', '*')
            ->allowAttribute('data-en', '*')
            ->allowAttribute('data-fa', '*')
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
            ->allowElement('pre', '*')
            ->allowElement('code', '*')
            ->allowElement('svg', '*')
            ->allowElement('path', '*')
            ->allowElement('button', '*')
            ->allowElement('section', '*')
            ->allowElement('article', '*')
            ->allowElement('div', '*')
            ->allowElement('span', '*')
            ->allowElement('table', '*')
            ->allowElement('thead', '*')
            ->allowElement('tbody', '*')
            ->allowElement('tr', '*')
            ->allowElement('td', '*')
            ->allowElement('th', '*')
            ->withMaxInputLength(2_000_000);

        return (new HtmlSanitizer($config))->sanitize($html);
    }
}
