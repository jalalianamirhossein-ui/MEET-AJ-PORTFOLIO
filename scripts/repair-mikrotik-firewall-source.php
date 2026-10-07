<?php

require dirname(__DIR__).'/app/Services/MikrotikFirewallArticleRepair.php';
$root = dirname(__DIR__);
$migration = file_get_contents($root.'/database/migrations/2026_10_06_000027_add_mikrotik_firewall_hardening_article.php');
preg_match('~\$localizations = (\[.*?\n        \]);~s', $migration, $matches);
// The metadata comes from the project's original publication migration.
$enTitle = 'MikroTik Firewall Hardening for Enterprise Networks Using Input and Forward Chains';
$faTitle = 'هاردنینگ فایروال MikroTik برای شبکه سازمانی با Input و Forward Chain';
$enDescription = 'A production-oriented RouterOS v7 firewall policy for enterprise VLANs, with explicit input and forward rules, management protection, segmentation, logging and verification.';
$faDescription = 'راهنمای عملیاتی و Production-oriented برای Policy فایروال RouterOS v7 در شبکه سازمانی، با تمرکز بر Input و Forward، حفاظت Management، تفکیک VLAN، Logging و تست.';
$localizations = eval('return '.$matches[1].';');
$path = $root.'/resources/legacy/articles/mikrotik-firewall-hardening-input-forward-chain.html';
$html = file_get_contents($path);
preg_match('~<article\b[^>]*>(.*?)</article>~s', $html, $body);
$repaired = (new App\Services\MikrotikFirewallArticleRepair)->repair($body[1], $localizations);
$html = str_replace($body[0], '<article class="article-body">'.$repaired.'</article>', $html);
if (! str_contains($html, 'id="article-localizations"')) {
    $html = str_replace('</head>', '<script type="application/json" id="article-localizations">'.json_encode($localizations, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG).'</script></head>', $html);
}
file_put_contents($path, $html);
echo "Firewall article source repaired.\n";
