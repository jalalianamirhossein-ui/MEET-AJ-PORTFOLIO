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
        return self::rewrite($html);
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
            if (isset($hashes[$value['source_hash'] ?? ''])) {
                $rewritten['source_hash'] = $hashes[$value['source_hash']];
            }

            return $rewritten;
        }

        return $value;
    }
}
