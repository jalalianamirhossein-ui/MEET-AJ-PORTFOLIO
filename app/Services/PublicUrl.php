<?php

namespace App\Services;

class PublicUrl
{
    public static function link(mixed $value, string $fallback = '#'): string
    {
        return self::safe($value, ['http', 'https', 'mailto', 'tel'], $fallback);
    }

    public static function media(mixed $value): string
    {
        return self::safe($value, ['http', 'https'], '');
    }

    private static function safe(mixed $value, array $schemes, string $fallback): string
    {
        if (! is_string($value) || preg_match('/[\x00-\x1f\x7f\\\\]/', $value)) {
            return $fallback;
        }
        $value = trim($value);
        if ($value === '') {
            return $fallback;
        }
        if (preg_match('/^([a-z][a-z0-9+.-]*):/i', $value, $match)
            && ! in_array(strtolower($match[1]), $schemes, true)) {
            return $fallback;
        }

        return $value;
    }

    public static function content(array $content): array
    {
        foreach ($content as $key => $value) {
            if (is_array($value)) {
                $content[$key] = self::content($value);
            } elseif (is_string($key) && preg_match('/(?:^|_)(?:href|url|image)$/', $key)) {
                $content[$key] = preg_match('/(?:image|map_url)$/', $key) ? self::media($value) : self::link($value);
            }
        }

        return $content;
    }
}
