<?php

namespace App\Services;

/** Moves complete authored sections using the reviewed source inventory. */
class ArticleStructure
{
    private function policy(): array
    {
        static $policy;
        return $policy ??= json_decode(file_get_contents(dirname(__DIR__, 2).'/resources/content/article-structure.json'), true, flags: JSON_THROW_ON_ERROR);
    }

    public function canonicalSections(string $slug): array
    {
        return $this->policy()['articles'][$slug]['canonical_sections'] ?? [];
    }

    public function translate(string $html, string $slug): string
    {
        static $translations;
        $translations ??= json_decode(file_get_contents(dirname(__DIR__, 2).'/resources/content/article-translations.json'), true, flags: JSON_THROW_ON_ERROR);
        $map = $translations[$slug] ?? [];
        if (! $map) { return $html; }
        return preg_replace_callback('~<(p|li|h[234]|th|td|figcaption|summary)\b([^>]*)>(.*?)</\1>~is', function ($m) use ($map) {
            if (preg_match('~\bdata-en=|<(?:p|ul|ol|pre|table|div)\b~i', $m[2].$m[3])) { return $m[0]; }
            $text = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($m[3]), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
            if ($m[1] === 'h2' && ! isset($map[$text])) {
                $key = preg_replace('/^[0-9۰-۹٠-٩]+[.)]\s+/u', '', $text);
                foreach ($map as $original => $pair) {
                    if (preg_replace('/^[0-9۰-۹٠-٩]+[.)]\s+/u', '', $original) === $key) { $map[$text] = $pair; break; }
                }
            }
            if (! isset($map[$text])) { return $m[0]; }
            $pair = is_array($map[$text]) ? $map[$text] : ['en' => $map[$text], 'fa' => $text];
            return '<'.$m[1].$m[2].' data-en="'.htmlspecialchars($pair['en'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'" data-fa="'.htmlspecialchars($pair['fa'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'">'.$m[3].'</'.$m[1].'>';
        }, $html) ?? $html;
    }

    public function cleanReviews(string $html): string
    {
        $protected = [];
        $html = preg_replace_callback('~<(pre|code|script|style)\b[^>]*>.*?</\1>~is', function ($m) use (&$protected) {
            $key = '__STRUCTURE_PROTECTED_'.count($protected).'__';
            $protected[$key] = $m[0];
            return $key;
        }, $html) ?? $html;
        $clean = function (string $text): string {
            foreach ($this->policy()['review_patterns'] as [$pattern, $replacement]) {
                $text = preg_replace('~'.str_replace('~', '\\~', $pattern).'~iu', $replacement, $text) ?? $text;
            }
            return $text;
        };
        // Limit edits to visible prose and its language attributes; SEO/date metadata stays exact.
        $html = preg_replace_callback('~<(p|span|li)\b([^>]*)>(.*?)</\1>~is', function ($m) use ($clean) {
            $attrs = preg_replace_callback('~\bdata-(en|fa)=(["\'])(.*?)\2~s', function ($a) use ($clean) {
                $text = html_entity_decode($a[3], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $new = $clean($text);
                return $new === $text ? $a[0] : 'data-'.$a[1].'='.$a[2].htmlspecialchars($new, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').$a[2];
            }, $m[2]);
            $body = implode('', array_map(fn ($part) => str_starts_with($part, '<') ? $part : $clean($part), preg_split('~(<[^>]*>)~s', $m[3], flags: PREG_SPLIT_DELIM_CAPTURE)));
            if ($attrs === $m[2] && $body === $m[3]) { return $m[0]; }
            if (trim(strip_tags($body)) === '' && ! str_contains($body, '__STRUCTURE_PROTECTED_') && ! preg_match('~<(?:img|a)\b~', $body)) {
                if (! preg_match('~data-(?:en|fa)=["\'][^"\']+~', $attrs)) { return ''; }
            }
            return '<'.$m[1].$attrs.'>'.$body.'</'.$m[1].'>';
        }, $html) ?? $html;
        return strtr($html, $protected);
    }

    public function order(string $html, string $slug): string
    {
        $policy = $this->policy();
        $order = $policy['articles'][$slug]['order'] ?? $policy['default_order'];
        // Scan balanced containers; a nested section always travels with its parent.
        preg_match_all('~<pre\b[^>]*>.*?</pre>|</?section\b[^>]*>|<footer\b[^>]*>.*?</footer>~is', $html, $tags, PREG_OFFSET_CAPTURE);
        $depth = 0;
        $start = null;
        $sections = [];
        foreach ($tags[0] as [$tag, $offset]) {
            if (preg_match('~^<(?:pre|footer)\b~i', $tag)) { continue; }
            if (preg_match('~^<section\b~i', $tag)) {
                if ($depth++ === 0) { $start = $offset; }
            } elseif ($depth > 0 && --$depth === 0 && $start !== null) {
                $length = $offset + strlen($tag) - $start;
                $block = substr($html, $start, $length);
                if (! preg_match('~<h2\b~i', $block)) { continue; }
                // Authored fragment aliases take precedence over inferred canonical IDs.
                // For example, "Configuration backup" must remain in recovery, even
                // when the legacy recognizer assigns it the configuration ID.
                preg_match('~^<section\b[^>]*\bid=["\']([^"\']+)["\']~i', $block, $outer);
                preg_match('~^<section\b[^>]*>\s*<span\b[^>]*\bid=["\']([^"\']+)["\'][^>]*class=["\']article-section-anchor["\']~i', $block, $alias);
                $id = $outer[1] ?? '';
                if (in_array($id, $policy['default_order'], true) && isset($alias[1])) { $id = $alias[1]; }
                $ranks = [];
                foreach ([$id] as $id) {
                    $rank = array_search($id, $order, true);
                    if ($rank !== false) { $ranks[] = $rank; }
                }
                $sections[] = ['start' => $start, 'length' => $length, 'html' => $block,
                    'rank' => $ranks ? min($ranks) : intdiv(count($order), 2), 'index' => count($sections)];
            }
        }
        $sorted = $sections;
        usort($sorted, fn ($a, $b) => [$a['rank'], $a['index']] <=> [$b['rank'], $b['index']]);
        foreach (array_reverse(array_keys($sections)) as $i) {
            $html = substr_replace($html, $sorted[$i]['html'], $sections[$i]['start'], $sections[$i]['length']);
        }
        return $this->numberHeadings($html);
    }

    /** Integer prefixes on H2 only; dotted versions/IP addresses are not section numbers. */
    public function headingNumber(string $label): ?int
    {
        $label = strtr(trim($label), array_combine(preg_split('//u', '۰۱۲۳۴۵۶۷۸۹٠١٢٣٤٥٦٧٨٩', -1, PREG_SPLIT_NO_EMPTY), str_split('01234567890123456789')));
        return preg_match('/^([0-9]+)[.)]\s/u', $label, $m) ? (int) $m[1] : null;
    }

    private function numberText(int $number, string $original): string
    {
        $digits = preg_match('/[۰-۹]/u', $original) ? '۰۱۲۳۴۵۶۷۸۹' : (preg_match('/[٠-٩]/u', $original) ? '٠١٢٣٤٥٦٧٨٩' : '0123456789');
        return strtr((string) $number, array_combine(str_split('0123456789'), preg_split('//u', $digits, -1, PREG_SPLIT_NO_EMPTY)));
    }

    public function numberingIssues(string $html): array
    {
        $dom = new \DOMDocument;
        @$dom->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NONET);
        $xp = new \DOMXPath($dom);
        $sequences = ['en' => [], 'fa' => []]; $alignment = [];
        foreach ($xp->query('//h2[not(ancestor::pre) and not(ancestor::code) and not(ancestor::footer)]') as $heading) {
            $numbers = [];
            foreach (['en','fa'] as $locale) {
                $label = $heading->hasAttribute('data-'.$locale) ? $heading->getAttribute('data-'.$locale) : $heading->textContent;
                $numbers[$locale] = $this->headingNumber($label);
                if ($numbers[$locale] !== null) { $sequences[$locale][] = $numbers[$locale]; }
            }
            if ($numbers['en'] !== $numbers['fa']) { $alignment[] = trim($heading->textContent); }
        }
        $issues = [];
        foreach ($sequences as $locale => $numbers) {
            if ($numbers && $numbers !== range(1, count($numbers))) { $issues[$locale.'_sequence'] = $numbers; }
            if (count($numbers) !== count(array_unique($numbers))) { $issues[$locale.'_duplicates'] = $numbers; }
            foreach ($numbers as $i => $number) {
                if ($i > 0 && $number < $numbers[$i-1]) { $issues[$locale.'_backward'] = $numbers; break; }
            }
        }
        if ($alignment) { $issues['bilingual_alignment'] = $alignment; }
        return $issues;
    }

    /** Renumber authored H2s after ordering; protect executable blocks and retain every ID. */
    public function numberHeadings(string $html): string
    {
        $protected = [];
        $html = preg_replace_callback('~<(pre|code|script|style)\b[^>]*>.*?</\1>~is', function ($m) use (&$protected) {
            $key = '__NUMBER_PROTECTED_'.count($protected).'__'; $protected[$key] = $m[0]; return $key;
        }, $html) ?? $html;
        $mapping = []; $index = 0;
        $html = preg_replace_callback('~<h2\b([^>]*)>(.*?)</h2>~is', function ($m) use (&$mapping, &$index) {
            preg_match('~\bdata-en=(["\'])(.*?)\1~s', $m[1], $attr);
            $old = $this->headingNumber(html_entity_decode($attr[2] ?? strip_tags($m[2]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            if ($old === null) { return $m[0]; }
            $mapping[$old] = ++$index;
            $prefix = fn ($text) => preg_replace_callback('/^(\s*)([0-9۰-۹٠-٩]+)([.)]\s+)/u', fn ($n) => $n[1].$this->numberText($index, $n[2]).$n[3], $text) ?? $text;
            $attrs = preg_replace_callback('~\bdata-(en|fa)=(["\'])(.*?)\2~s', function ($a) use ($prefix) {
                $old = html_entity_decode($a[3], ENT_QUOTES | ENT_HTML5, 'UTF-8'); $new = $prefix($old);
                return $old === $new ? $a[0] : 'data-'.$a[1].'='.$a[2].htmlspecialchars($new, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').$a[2];
            }, $m[1]);
            $body = implode('', array_map(fn ($p) => str_starts_with($p, '<') ? $p : $prefix($p), preg_split('~(<[^>]*>)~s', $m[2], flags: PREG_SPLIT_DELIM_CAPTURE)));
            return '<h2'.$attrs.'>'.$body.'</h2>';
        }, $html) ?? $html;
        $references = fn ($text) => preg_replace_callback('/((?:\bsections?\b|\bchapters?\b|بخش(?:‌های|های)?|فصل(?:‌های|های)?)\s+)([0-9۰-۹٠-٩]+)(?:([–−-]|\s+(?:تا|و|to|through|and)\s+)([0-9۰-۹٠-٩]+))?/iu', function ($m) use ($mapping) {
            $integer = fn ($v) => $this->headingNumber($v.'. reference');
            $a = $mapping[$integer($m[2])] ?? $integer($m[2]);
            $b = isset($m[4]) ? ($mapping[$integer($m[4])] ?? $integer($m[4])) : null;
            if ($b !== null) {
                $values = in_array(trim($m[3]), ['و','and'], true) ? [$a,$b] : array_map(fn ($n) => $mapping[$n] ?? $n, range($integer($m[2]), $integer($m[4])));
                sort($values);
                if ($values === range(min($values), max($values))) { $a = min($values); $b = max($values); }
                else { return $m[1].implode(', ', array_map(fn ($n) => $this->numberText($n, $m[2]), $values)); }
            }
            return $m[1].$this->numberText($a,$m[2]).($b !== null ? $m[3].$this->numberText($b,$m[4]) : '');
        }, $text) ?? $text;
        $html = implode('', array_map(function ($part) use ($references) {
            if (! str_starts_with($part, '<')) { return $references($part); }
            return preg_replace_callback('~\bdata-(en|fa)=(["\'])(.*?)\2~s', function ($a) use ($references) {
                $old = html_entity_decode($a[3], ENT_QUOTES | ENT_HTML5, 'UTF-8'); $new = $references($old);
                return $old === $new ? $a[0] : 'data-'.$a[1].'='.$a[2].htmlspecialchars($new, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').$a[2];
            }, $part) ?? $part;
        }, preg_split('~(<[^>]*>)~s', $html, flags: PREG_SPLIT_DELIM_CAPTURE)));
        return strtr($html, $protected);
    }

    public function repair(string $html, string $slug): string
    {
        $html = $this->cleanReviews($html);
        if (preg_match('~<article\b[^>]*class=["\']article-body["\'][^>]*>~i', $html)) {
            $html = preg_replace_callback('~(<article\b[^>]*class=["\']article-body["\'][^>]*>)(.*?)(</article>)~is', fn ($m) => $m[1].$this->order($this->translate($m[2], $slug), $slug).$m[3], $html) ?? $html;
            preg_match('~<article\b[^>]*class=["\']article-body["\'][^>]*>(.*?)</article>~is', $html, $body);
            preg_match_all('~<section\b[^>]*\bid=["\']([^"\']+)~i', $body[1] ?? '', $ids);
            preg_match_all('~<li\b[^>]*class="article-nav-item"[^>]*>.*?</li>~is', $html, $items, PREG_OFFSET_CAPTURE);
            $sorted = $items[0];
            $rank = function ($item) use ($ids): int {
                preg_match('~href=["\']#([^"\']+)~', $item[0], $target);
                $index = array_search($target[1] ?? '', $ids[1], true);
                return $index === false ? count($ids[1]) : $index;
            };
            usort($sorted, fn ($a, $b) => $rank($a) <=> $rank($b));
            foreach (array_reverse(array_keys($items[0])) as $i) {
                $html = substr_replace($html, $sorted[$i][0], $items[0][$i][1], strlen($items[0][$i][0]));
            }
            $targets = [];
            preg_match_all('~<section\b[^>]*\bid=["\']([^"\']+)["\'][^>]*>\s*<h2\b([^>]*)>(.*?)</h2>~is', $body[1] ?? '', $sections, PREG_SET_ORDER);
            foreach ($sections as $section) {
                preg_match('~data-en=(["\'])(.*?)\1~s', $section[2], $label);
                $targets[$section[1]] = $this->headingNumber(html_entity_decode($label[2] ?? strip_tags($section[3]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            }
            $html = preg_replace_callback('~<li\b[^>]*class="article-nav-item"[^>]*>.*?</li>~is', function ($m) use ($targets) {
                preg_match('~href=["\']#([^"\']+)~', $m[0], $target); $number = $targets[$target[1] ?? ''] ?? null;
                if ($number === null) { return $m[0]; }
                $prefix = fn ($text) => preg_replace_callback('/^(\s*)([0-9۰-۹٠-٩]+)([.)]\s+)/u', fn ($n) => $n[1].$this->numberText($number,$n[2]).$n[3], $text) ?? $text;
                $block = preg_replace_callback('~\bdata-(en|fa)=(["\'])(.*?)\2~s', function ($a) use ($prefix) {
                    $old = html_entity_decode($a[3], ENT_QUOTES | ENT_HTML5, 'UTF-8'); $new = $prefix($old);
                    return $old === $new ? $a[0] : 'data-'.$a[1].'='.$a[2].htmlspecialchars($new, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').$a[2];
                }, $m[0]);
                return implode('', array_map(fn ($p) => str_starts_with($p, '<') ? $p : $prefix($p), preg_split('~(<[^>]*>)~s', $block, flags: PREG_SPLIT_DELIM_CAPTURE)));
            }, $html) ?? $html;
            $metadata = json_decode(file_get_contents(dirname(__DIR__, 2).'/resources/content/article-localizations.json'), true, flags: JSON_THROW_ON_ERROR);
            if (isset($metadata[$slug]) && ! str_contains($html, 'id="article-localizations"')) {
                $html = str_replace('</head>', '<script id="article-localizations" type="application/json">'.json_encode($metadata[$slug], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_THROW_ON_ERROR).'</script></head>', $html);
            }
            return $html;
        }
        return $this->order($this->translate($html, $slug), $slug);
    }
}
