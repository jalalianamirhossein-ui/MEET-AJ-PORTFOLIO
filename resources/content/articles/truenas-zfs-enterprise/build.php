<?php

declare(strict_types=1);

require dirname(__DIR__, 4).'/vendor/autoload.php';

use League\CommonMark\CommonMarkConverter;

$root = dirname(__DIR__).'/truenas-zfs-enterprise-nas';
$markdown = file_get_contents($root.'/article.fa.md');
$converter = new CommonMarkConverter([
    'html_input' => 'escape',
    'allow_unsafe_links' => false,
]);
$body = $converter->convert($markdown)->getContent();

$body = preg_replace('/^<h1>.*?<\/h1>\s*/s', '', $body, 1);
$body = str_replace('src="/assets/', 'loading="lazy" src="/assets/', $body);
$body = preg_replace('/<img loading="lazy" src="([^"]+)" alt="([^"]*)">/', '<img loading="lazy" src="$1" alt="$2" decoding="async">', $body);
$sectionNumber = 0;
$body = preg_replace_callback('/<h2>(.*?)<\/h2>/s', static function (array $match) use (&$sectionNumber): string {
    $sectionNumber++;
    $prefix = $sectionNumber > 1 ? '</section>' : '';
    return $prefix.'<section id="section-'.$sectionNumber.'"><h2>'.$match[1].'</h2>';
}, $body);
$body .= $sectionNumber > 0 ? '</section>' : '';

$title = 'راه‌اندازی TrueNAS از صفر؛ ساخت NAS سازمانی با ZFS';
$description = 'راهنمای Production-Ready راه‌اندازی TrueNAS با ZFS، ساخت Pool و Dataset، SMB، NFS، iSCSI، Snapshot، Replication، امنیت و Backup سه-دو-یک.';
$keywords = 'TrueNAS, ZFS, NAS سازمانی, RAIDZ, Dataset, Pool, VDEV, SMB, NFS, iSCSI, Snapshot, Replication, Backup';
$canonical = 'https://meetaj.ir/articles/truenas-zfs-enterprise';
$banner = '/assets/img/articles/banners/TrueNAS%20Enterprise%20Storage%20Solution.png';

$html = '<!doctype html><html lang="fa" dir="rtl" data-article-language="fa"><head>'
    .'<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">'
    .'<title>'.htmlspecialchars($title, ENT_QUOTES, 'UTF-8').'</title>'
    .'<meta name="article:content-language" content="fa">'
    .'<meta name="description" content="'.htmlspecialchars($description, ENT_QUOTES, 'UTF-8').'">'
    .'<meta name="keywords" content="'.htmlspecialchars($keywords, ENT_QUOTES, 'UTF-8').'">'
    .'<meta name="robots" content="index, follow"><link rel="canonical" href="'.$canonical.'">'
    .'<meta property="og:type" content="article"><meta property="og:title" content="'.htmlspecialchars($title, ENT_QUOTES, 'UTF-8').'">'
    .'<meta property="og:description" content="'.htmlspecialchars($description, ENT_QUOTES, 'UTF-8').'">'
    .'<meta property="og:image" content="'.$banner.'"><meta property="og:url" content="'.$canonical.'">'
    .'<meta name="twitter:card" content="summary_large_image">'
    .'<script type="application/ld+json">'.json_encode([
        '@context' => 'https://schema.org', '@type' => 'Article', 'headline' => $title,
        'description' => $description, 'inLanguage' => 'fa', 'mainEntityOfPage' => $canonical,
        'image' => $banner, 'author' => ['@type' => 'Organization', 'name' => 'Meet AJ'],
    ], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).'</script>'
    .'</head><body class="article-page theme-other">'
    .'<section class="article-hero article-header hero"><div class="container"><div class="article-hero-layout">'
    .'<div class="article-hero-copy"><span class="article-category" data-en="Infrastructure" data-fa="زیرساخت">Infrastructure</span>'
    .'<h1 class="article-title hero-title" data-en="'.htmlspecialchars($title, ENT_QUOTES, 'UTF-8').'" data-fa="'.htmlspecialchars($title, ENT_QUOTES, 'UTF-8').'">'.htmlspecialchars($title, ENT_QUOTES, 'UTF-8').'</h1>'
    .'<p class="article-excerpt hero-subtitle" data-en="'.htmlspecialchars($description, ENT_QUOTES, 'UTF-8').'" data-fa="'.htmlspecialchars($description, ENT_QUOTES, 'UTF-8').'">'.htmlspecialchars($description, ENT_QUOTES, 'UTF-8').'</p></div>'
    .'<figure class="article-hero-media"><img class="article-hero-thumbnail" src="'.$banner.'" alt="راهکار Enterprise Storage با TrueNAS" loading="eager" fetchpriority="high"></figure>'
    .'</div></div></section><article class="article-body" lang="fa">'.$body.'</article></body></html>';

$destination = dirname(__DIR__, 3).'/legacy/articles/truenas-zfs-enterprise.html';
file_put_contents($destination, $html);
echo $destination."\n";
