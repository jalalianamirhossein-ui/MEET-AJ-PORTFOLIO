<?php

namespace App\Services;

use DOMDocument;
use DOMXPath;

/** Reviewed procedure/artifact contracts, applied to sources AND visible output. */
class ArticleTechnicalQuality
{
    public function issues(string $html, string $slug): array
    {
        $contracts = json_decode(file_get_contents(resource_path('content/article-technical-contracts.json')), true, flags: JSON_THROW_ON_ERROR);
        if (! isset($contracts['articles'][$slug])) {
            return ['unreviewed_article' => [$slug]];
        }
        $dom = new DOMDocument;
        @$dom->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NONET);
        $xp = new DOMXPath($dom);
        $issues = [];
        foreach ($contracts['articles'][$slug]['procedures'] as $procedure) {
            $section = $dom->getElementById($procedure['section']);
            if (! $section) {
                $issues['missing_procedure'][] = $procedure['section'];
                continue;
            }
            // The shared standardizer keeps old fragment destinations as spans
            // when consolidating an authored procedure into its canonical section.
            if ($section->tagName !== 'section') {
                $section = $xp->query('ancestor::section[1]', $section)->item(0) ?? $section;
            }
            $actual = [];
            foreach ($xp->query('.//pre', $section) as $block) {
                $text = str_replace(["\r\n", "\r"], "\n", $block->textContent);
                $actual[] = hash('sha256', $text);
            }
            foreach ($procedure['artifacts'] as $artifact) {
                if (! in_array($artifact['sha256'], $actual, true)) {
                    $issues['missing_or_changed_artifact'][] = $procedure['section'].': '.$artifact['description'];
                }
            }
        }
        // These reviewed content markers pair every explanatory leaf; executable
        // examples remain shared and must not be translated into different commands.
        foreach ($contracts['articles'][$slug]['bilingual_blocks'] as $block) {
            $node = $dom->getElementById($block['id']);
            if (! $node) {
                $issues['missing_technical_explanation'][] = $block['id'];
                continue;
            }
            foreach ($xp->query('.//*[self::p or self::h3 or self::h4 or self::a]', $node) as $leaf) {
                if ($xp->query('ancestor::pre', $leaf)->length) { continue; }
                if ($leaf->tagName === 'p' && $xp->query('./a', $leaf)->length) { continue; }
                if (trim($leaf->getAttribute('data-en')) === '' || trim($leaf->getAttribute('data-fa')) === '') {
                    $issues['incomplete_technical_translation'][] = $block['id'].': '.$leaf->tagName;
                }
            }
        }
        // This confirmed defect cannot be concealed by rewriting the contract.
        if ($slug === 'mikrotik-firewall-hardening-input-forward-chain') {
            foreach ($xp->query('//pre') as $block) {
                foreach (preg_split('/\R/', $block->textContent) as $line) {
                    if (str_starts_with(ltrim($line), '#')) { continue; }
                    if (str_contains($line, 'action=drop') && str_contains($line, 'limit=')) {
                        $issues['rate_limited_deny'][] = $line;
                    }
                }
            }
        }
        return $issues;
    }
}
