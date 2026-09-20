<?php
$root = dirname(__DIR__);
$baseline = json_decode(file_get_contents($root.'/docs/qa/baseline-files.json'), true, flags: JSON_THROW_ON_ERROR);
// Baseline keys describe the original static layout; keep their historical hashes.
$relocations = [
    'assets/' => 'resources/assets/',
    'articles/' => 'resources/legacy/articles/',
    'services/' => 'resources/legacy/services/',
    'forms/' => 'resources/legacy/forms/',
    'partials/' => 'resources/static/partials/',
    'design-system/' => 'docs/design-system/',
    'index.html' => 'resources/legacy/index.html',
    'robots.txt' => 'resources/legacy/robots.txt',
    'sitemap.xml' => 'resources/legacy/sitemap.xml',
    'sw.js' => 'resources/legacy/sw.js',
    'manifest.json' => 'resources/static/manifest.json',
    'preloader.css' => 'resources/static/preloader.css',
    'preloader.html' => 'resources/static/preloader.html',
    'docs/netbox_installation_guide_v2.pdf' => 'resources/downloads/netbox_installation_guide_v2.pdf',
];
$changed = [];
foreach ($baseline as $entry) {
    $relative = $entry['file'];
    foreach ($relocations as $from => $to) {
        if ($relative === $from || (str_ends_with($from, '/') && str_starts_with($relative, $from))) {
            $relative = $to.substr($relative, strlen($from));
            break;
        }
    }
    $file = $root.'/'.$relative;
    if (!is_file($file) || hash_file('sha256', $file) !== $entry['sha256']) {
        $changed[] = $entry['file'];
    }
}
echo json_encode(['checked' => count($baseline), 'changed' => $changed], JSON_PRETTY_PRINT).PHP_EOL;
exit(count($changed) ? 1 : 0);
