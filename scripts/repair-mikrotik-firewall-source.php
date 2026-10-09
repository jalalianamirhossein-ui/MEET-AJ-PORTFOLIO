<?php

require dirname(__DIR__).'/app/Services/MikrotikFirewallArticleRepair.php';
$root = dirname(__DIR__);
$path = $root.'/resources/legacy/articles/mikrotik-firewall-hardening-input-forward-chain.html';
$html = file_get_contents($path);
$dom = new DOMDocument;
@$dom->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NONET);
$metadata = $dom->getElementById('article-localizations');
if (! $metadata) {
    throw new RuntimeException('Article localization metadata is missing. No source was changed.');
}
$localizations = json_decode($metadata->textContent, true, 512, JSON_THROW_ON_ERROR);
if (! is_array($localizations) || ! isset($localizations['en'], $localizations['fa'])) {
    throw new RuntimeException('Article localization metadata is invalid. No source was changed.');
}
preg_match('~<article\b[^>]*>(.*?)</article>~s', $html, $body);
$repaired = (new App\Services\MikrotikFirewallArticleRepair)->repair($body[1], $localizations);
$html = str_replace($body[0], '<article class="article-body">'.$repaired.'</article>', $html);
if (! str_contains($html, 'id="article-localizations"')) {
    $html = str_replace('</head>', '<script type="application/json" id="article-localizations">'.json_encode($localizations, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG).'</script></head>', $html);
}
$html = (new App\Services\ArticleStructure)->repair($html, 'mikrotik-firewall-hardening-input-forward-chain');
file_put_contents($path, $html);
echo "Firewall article source repaired.\n";
