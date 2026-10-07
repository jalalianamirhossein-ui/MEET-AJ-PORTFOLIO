<?php

namespace App\Services;

class MikrotikFirewallArticleRepair
{
    public function repair(string $html, array $localizations): string
    {
        // Use tags already supported by both server and browser localization.
        $html = preg_replace('~<(/?)strong\b~i', '<$1span', $html);
        $html = preg_replace('~<(/?)figcaption\b~i', '<$1p', $html);
        $translations = ['Admin PC' => 'رایانه مدیر', 'Users' => 'کاربران', 'Internet' => 'اینترنت', 'Guest' => 'مهمان', 'Servers' => 'سرورها', 'Server VLAN' => 'VLAN سرورها', 'Any' => 'همه سرویس‌ها', 'Other' => 'سایر سرویس‌ها', 'Allow' => 'مجاز', 'Drop' => 'مسدود', 'New / No DST-NAT' => 'اتصال جدید بدون DST-NAT'];
        $html = preg_replace_callback('~<td>([^<]*)</td>~', function ($match) use ($translations) {
            $en = $match[1];
            return isset($translations[$en]) ? '<td data-en="'.$en.'" data-fa="'.$translations[$en].'">'.$en.'</td>' : $match[0];
        }, $html);
        if (! str_contains($html, 'id="faq"')) {
            $faq = '<section id="faq"><h2 data-en="Frequently Asked Questions" data-fa="پرسش‌های متداول">Frequently Asked Questions</h2>';
            foreach ($localizations['en']['faq'] as $index => $pair) {
                $fa = $localizations['fa']['faq'][$index];
                foreach (['h3' => 0, 'p' => 1] as $tag => $position) {
                    $enText = htmlspecialchars($pair[$position], ENT_QUOTES, 'UTF-8');
                    $faText = htmlspecialchars($fa[$position], ENT_QUOTES, 'UTF-8');
                    $faq .= '<'.$tag.' data-en="'.$enText.'" data-fa="'.$faText.'">'.$enText.'</'.$tag.'>';
                }
            }
            $html .= $faq.'</section>';
        }
        return $html;
    }
}
