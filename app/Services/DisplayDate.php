<?php

namespace App\Services;

use DateTimeInterface;
use IntlDateFormatter;
use RuntimeException;

class DisplayDate
{
    public function format(DateTimeInterface $date, string $locale, bool $yearOnly = false): string
    {
        $persian = $locale === 'fa';
        $formatter = new IntlDateFormatter(
            $persian ? 'fa_IR@calendar=persian' : 'en_US@calendar=gregorian',
            IntlDateFormatter::NONE,
            IntlDateFormatter::NONE,
            config('cms.display_timezone', 'Asia/Tehran'),
            $persian ? IntlDateFormatter::TRADITIONAL : IntlDateFormatter::GREGORIAN,
            $yearOnly ? 'y' : ($persian ? 'd MMMM y' : 'MMM d, y'),
        );
        $formatted = $formatter->format($date);
        if ($formatted === false) {
            throw new RuntimeException('Cannot format the display date.');
        }

        return $formatted;
    }
}
