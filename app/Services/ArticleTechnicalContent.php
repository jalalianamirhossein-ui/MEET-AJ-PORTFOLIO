<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMXPath;

/** Content-only migration support; the JSON also owns the source compiler inputs. */
class ArticleTechnicalContent
{
    public function enrich(string $html, string $slug): string
    {
        $inputs = json_decode(file_get_contents(resource_path('content/article-technical-content.json')), true, flags: JSON_THROW_ON_ERROR);
        $article = $inputs['articles'][$slug] ?? null;
        if (! $article) { return $html; }
        $dom = new DOMDocument;
        @$dom->loadHTML('<?xml encoding="UTF-8"><div id="technical-migration-root">'.$html.'</div>', LIBXML_NONET);
        $xp = new DOMXPath($dom);
        foreach ($article['replace_sections'] ?? [] as $id) {
            $section = $dom->getElementById($id);
            if (! $section) { return $html; }
            if ($dom->getElementById('technical-'.$id)) { continue; }
            $fingerprint = hash('sha256', trim(preg_replace('/\s+/u', ' ', $section->textContent)));
            if (! in_array($fingerprint, $article['approved_section_text_hashes'][$id] ?? [], true)) {
                // A customized CMS procedure must be reviewed, never overwritten.
                return $html;
            }
        }
        $changed = false;
        foreach ($article['code_replacements'] ?? [] as [$old, $new]) {
            foreach ($xp->query('//pre//code') as $code) {
                $text = str_replace($old, $new, $code->textContent);
                if ($text !== $code->textContent) {
                    while ($code->firstChild) { $code->removeChild($code->firstChild); }
                    $code->appendChild($dom->createTextNode($text));
                    $changed = true;
                }
            }
        }
        foreach ($article['sections'] as $id => $blocks) {
            $section = $dom->getElementById($id);
            if (! $section || $section->tagName !== 'section' || $dom->getElementById('technical-'.$id)) { continue; }
            if (in_array($id, $article['replace_sections'] ?? [], true)) {
                foreach (iterator_to_array($section->childNodes) as $node) {
                    if (! ($node instanceof DOMElement && $node->tagName === 'h2')) { $section->removeChild($node); }
                }
            }
            $container = $dom->createElement('div');
            $container->setAttribute('id', 'technical-'.$id);
            foreach ($blocks as $block) {
                if ($block['type'] === 'code') {
                    $pre = $dom->createElement('pre'); $pre->setAttribute('dir', 'ltr');
                    $code = $dom->createElement('code'); $code->setAttribute('class', 'language-'.$block['language']);
                    $code->appendChild($dom->createTextNode($block['text']));
                    $pre->appendChild($code); $container->appendChild($pre);
                    continue;
                }
                $tag = $block['type'] === 'reference' ? 'a' : $block['type'];
                $leaf = $dom->createElement($tag);
                $leaf->setAttribute('data-en', $block['en']); $leaf->setAttribute('data-fa', $block['fa']);
                $leaf->appendChild($dom->createTextNode($block['en']));
                if (isset($block['id'])) { $leaf->setAttribute('id', $block['id']); }
                if ($tag === 'a') {
                    $leaf->setAttribute('href', $block['url']); $leaf->setAttribute('rel', 'noopener');
                    $paragraph = $dom->createElement('p'); $paragraph->appendChild($leaf);
                    $container->appendChild($paragraph);
                } else { $container->appendChild($leaf); }
            }
            $section->appendChild($container); $changed = true;
        }
        if (! $changed) { return $html; }
        $root = $dom->getElementById('technical-migration-root');
        $output = '';
        foreach ($root->childNodes as $node) { $output .= $dom->saveHTML($node); }
        return app(ArticleStructure::class)->order($output, $slug);
    }
}
