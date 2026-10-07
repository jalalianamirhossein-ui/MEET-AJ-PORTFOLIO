<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMXPath;

/** Shared reading layout, without persisting changes to authored content. */
class ArticlePresentation
{
    public function prepare(string $html): string
    {
        // Preserve executable blocks byte-for-byte, including highlighted spans.
        $blocks = [];
        $html = preg_replace_callback('~<pre\b[^>]*>.*?</pre>~is', function ($match) use (&$blocks) {
            $index = count($blocks);
            $blocks[$index] = $match[0];

            return '<div data-article-code-slot="'.$index.'"></div>';
        }, $html) ?? $html;
        $dom = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8"><div id="article-presentation-root">'.$html.'</div>', LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        $xp = new DOMXPath($dom);
        $root = $dom->getElementById('article-presentation-root');
        if (! $root) {
            return str_replace(array_map(fn ($i) => '<div data-article-code-slot="'.$i.'"></div>', array_keys($blocks)), $blocks, $html);
        }

        foreach ($xp->query('.//section', $root) as $section) {
            $this->addClass($section, 'article-section');
            if (in_array($section->getAttribute('id'), ['architecture', 'enterprise-architecture'], true)) {
                $this->addClass($section, 'article-architecture');
            }
        }
        foreach (iterator_to_array($xp->query('.//p', $root)) as $paragraph) {
            $blank = preg_replace('/[\s\x{00A0}\x{200B}]+/u', '', $paragraph->textContent) === '';
            if ($blank && ! $paragraph->hasAttribute('data-en') && ! $paragraph->hasAttribute('data-fa')
                && $xp->query('.//*[not(self::br)]', $paragraph)->length === 0) {
                $paragraph->parentNode->removeChild($paragraph);
            }
        }

        // Normalize both current h3 questions and older FAQ question nodes
        // that use a different heading level or only the shared CSS class.
        $faqQuestions = $xp->query(
            './/section[@id="faq"]//*[self::h3 or self::h4 or contains(concat(" ", normalize-space(@class), " "), " article-faq-question ")][not(ancestor::details)]',
            $root
        );
        foreach (iterator_to_array($faqQuestions) as $question) {
            if (! $question->parentNode) {
                continue;
            }
            $container = $question->parentNode;
            $details = $dom->createElement('details');
            $details->setAttribute('class', 'article-faq-item article-faq-disclosure');
            $summary = $dom->createElement('summary');
            $answer = $dom->createElement('div');
            $answer->setAttribute('class', 'article-faq-content');
            $details->appendChild($summary);
            $details->appendChild($answer);
            if ($container instanceof DOMElement && $this->hasClass($container, 'article-faq-item')) {
                // Existing card-shaped FAQs may wrap the answer in another div.
                $container->parentNode->replaceChild($details, $container);
                $summary->appendChild($question);
                while ($container->firstChild) {
                    $answer->appendChild($container->firstChild);
                }
            } else {
                // Older articles use consecutive h3 + answer nodes in the section.
                $container->insertBefore($details, $question);
                $next = $question->nextSibling;
                $summary->appendChild($question);
                while ($next && ! ($next instanceof DOMElement && in_array(strtolower($next->tagName), ['h2', 'h3', 'details'], true))) {
                    $following = $next->nextSibling;
                    $answer->appendChild($next);
                    $next = $following;
                }
            }
        }

        foreach ($xp->query('.//*[@data-article-code-slot]', $root) as $slot) {
            $block = $blocks[(int) $slot->getAttribute('data-article-code-slot')];
            if ($xp->query('ancestor::*[contains(concat(" ", normalize-space(@class), " "), " article-code ")]', $slot)->length > 0) {
                continue;
            }
            $wrapper = $dom->createElement('div');
            $wrapper->setAttribute('class', 'article-code');
            $architecture = $xp->query('ancestor::section[contains(concat(" ", normalize-space(@class), " "), " article-architecture ")]', $slot)->length > 0;
            $text = html_entity_decode(strip_tags($block), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $flow = $architecture && preg_match('/(?:^Normal:|Client\s*(?:->|↓)|[┌└├])/mu', $text);
            if ($flow) {
                $this->addClass($wrapper, 'article-flow');
            }
            $header = $dom->createElement('div');
            $header->setAttribute('class', 'article-code-header');
            preg_match('/\blanguage-([a-z0-9-]+)/i', $block, $language);
            $label = $dom->createElement('span', $flow ? 'FLOW' : strtoupper($language[1] ?? 'CODE'));
            $label->setAttribute('class', 'article-code-label');
            $button = $dom->createElement('button');
            $button->setAttribute('type', 'button');
            $button->setAttribute('class', 'article-copy-button');
            $button->setAttribute('aria-label', 'Copy code');
            $button->setAttribute('data-en-aria-label', 'Copy code');
            $button->setAttribute('data-fa-aria-label', 'کپی کد');
            $icon = $dom->createElement('i');
            $icon->setAttribute('class', 'bi bi-clipboard');
            $icon->setAttribute('aria-hidden', 'true');
            $button->appendChild($icon);
            $header->appendChild($label);
            $header->appendChild($button);
            $slot->parentNode->replaceChild($wrapper, $slot);
            $wrapper->appendChild($header);
            $wrapper->appendChild($slot);
        }

        $output = '';
        foreach ($root->childNodes as $child) {
            $output .= $dom->saveHTML($child);
        }
        foreach ($blocks as $index => $block) {
            $output = str_replace('<div data-article-code-slot="'.$index.'"></div>', $block, $output);
        }

        return $output;
    }

    private function hasClass(DOMElement $element, string $class): bool
    {
        return in_array($class, preg_split('/\s+/', trim($element->getAttribute('class'))) ?: [], true);
    }

    private function addClass(DOMElement $element, string $class): void
    {
        if (! $this->hasClass($element, $class)) {
            $element->setAttribute('class', trim($element->getAttribute('class').' '.$class));
        }
    }
}
