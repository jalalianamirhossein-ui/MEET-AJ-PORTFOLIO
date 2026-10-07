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

$title = 'راه‌اندازی TrueNAS از صفر؛ ساخت NAS سازمانی با ZFS';
$description = 'راهنمای Production-Ready راه‌اندازی TrueNAS با ZFS، ساخت Pool و Dataset، SMB، NFS، iSCSI، Snapshot، Replication، امنیت و Backup سه-دو-یک.';
$keywords = 'TrueNAS, ZFS, NAS سازمانی, RAIDZ, Dataset, Pool, VDEV, SMB, NFS, iSCSI, Snapshot, Replication, Backup';
$canonical = 'https://meetaj.ir/articles/truenas-zfs-enterprise';
$banner = '/assets/img/articles/banners/TrueNAS%20Enterprise%20Storage%20Solution.png';

$html = '<!doctype html><html lang="fa" dir="rtl" data-article-language="fa"><head>'
    .'<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">'
    .'<title>'.htmlspecialchars($title, ENT_QUOTES, 'UTF-8').'</title>'
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
    .'</head><body class="article-page theme-other"><main class="article-body" lang="fa">'.$body.'</main></body></html>';

$destination = dirname(__DIR__, 3).'/legacy/articles/truenas-zfs-enterprise.html';
file_put_contents($destination, $html);
echo $destination."\n";
