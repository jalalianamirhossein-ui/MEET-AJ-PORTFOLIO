<?php

namespace App\Services;

class ImagePaths
{
    public static function rewrite(string $value): string
    {
        $replacements = [];
        foreach (config('image-paths.legacy', []) as $old => $new) {
            $replacements['assets/img/'.$old] = 'assets/img/'.$new;
        }

        return strtr($value, $replacements);
    }

    public static function body(string $html, string $slug): string
    {
        $html = self::rewrite($html);

        return preg_replace_callback('~<img\b[^>]*>~i', function ($match) use ($slug) {
            if (str_contains($match[0], 'article-hero-thumbnail')) {
                return $match[0];
            }
            $replacements = [];
            foreach (config('image-paths.article_content.'.$slug, []) as $banner => $content) {
                $replacements['assets/img/'.$banner] = 'assets/img/'.$content;
            }

            return strtr($match[0], $replacements);
        }, $html) ?? $html;
    }

    public static function data(mixed $value): mixed
    {
        if (is_string($value)) {
            return self::rewrite($value);
        }
        if (is_array($value)) {
            $rewritten = array_map(self::data(...), $value);
            // Advance provenance only when the stored body matched the exact
            // pre-move source. Preserve unrelated editorial/source drift.
            $hashes = config('image-paths.source_hashes', [])[$value['source_file'] ?? ''] ?? null;
            if ($hashes && ($value['source_hash'] ?? null) === $hashes['before']) {
                $rewritten['source_hash'] = $hashes['after'];
            }

            return $rewritten;
        }

        return $value;
    }
}
