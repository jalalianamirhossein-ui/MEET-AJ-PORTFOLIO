<?php

/**
 * Documentation link repair + verification.
 * Rewrites only links that do not resolve, using a map of moved documents.
 */
$root = __DIR__;
$docs = $root.'/docs';

$map = [
    'PROJECT-STATUS.md' => 'current/PROJECT-STATUS.md',
    'ARCHITECTURE.md' => 'current/ARCHITECTURE.md',
    'DATABASE.md' => 'current/DATABASE.md',
    'ADMIN.md' => 'current/ADMIN.md',
    'features.md' => 'current/FEATURES.md',
    'FEATURES.md' => 'current/FEATURES.md',
    'MULTILINGUAL.md' => 'current/MULTILINGUAL.md',
    'SERVICES.md' => 'current/SERVICES.md',
    'SEO.md' => 'current/SEO.md',
    'PWA.md' => 'current/PWA.md',
    'SECURITY.md' => 'current/SECURITY.md',
    'design-system.md' => 'current/DESIGN-SYSTEM.md',
    'DESIGN-SYSTEM.md' => 'current/DESIGN-SYSTEM.md',
    'article-visual-dna.md' => 'current/ARTICLE-VISUAL-DNA.md',
    'DEPLOYMENT.md' => 'current/DEPLOYMENT.md',
    'PROJECT-STRUCTURE.md' => 'current/PROJECT-STRUCTURE.md',
    'TESTING.md' => 'current/TESTING.md',
    'PERFORMANCE.md' => 'current/PERFORMANCE.md',
    'ARTICLES.md' => 'current/ARTICLES.md',
    'REQUESTS.md' => 'current/REQUESTS.md',
    'final-project-qa-report.md' => 'qa/FINAL-QA-REPORT.md',
    'FINAL-QA-REPORT.md' => 'qa/FINAL-QA-REPORT.md',
    'full-site-visual-qa.md' => 'qa/VISUAL-QA.md',
    'VISUAL-QA.md' => 'qa/VISUAL-QA.md',
    'admin-ui-qa.md' => 'qa/ADMIN-QA.md',
    'ADMIN-QA.md' => 'qa/ADMIN-QA.md',
    'QA-MATRIX.md' => 'qa/QA-MATRIX.md',
    'full-site-design-audit.md' => 'qa/DESIGN-AUDIT.md',
    'RESPONSIVE-QA.md' => 'qa/RESPONSIVE-QA.md',
    'ACCESSIBILITY-QA.md' => 'qa/ACCESSIBILITY-QA.md',
    'CONTENT-INTEGRITY.md' => 'qa/CONTENT-INTEGRITY.md',
    'architecture-decision-record.md' => 'decisions/ADR/ADR-001-laravel-13-filament-5-stack.md',
    'framework-version-decision.md' => 'decisions/ADR/ADR-002-framework-version-selection.md',
    'DOCUMENTATION-INDEX.md' => 'historical/DOCUMENTATION-INDEX.md',
    'DOCUMENTATION-CLEANUP-REPORT.md' => 'historical/DOCUMENTATION-CLEANUP-REPORT.md',
    'PROJECT-STRUCTURE-CLEANUP.md' => 'historical/PROJECT-STRUCTURE-CLEANUP.md',
    'SERVICE-CMS-IMPLEMENTATION.md' => 'historical/SERVICE-CMS-IMPLEMENTATION.md',
    'final-ui-qa.md' => 'historical/final-ui-qa.md',
    'service-detail-ui-qa.md' => 'historical/service-detail-ui-qa.md',
    'get-to-know-me-ui.md' => 'historical/get-to-know-me-ui.md',
    'current-site-inventory.json' => 'historical/current-site-inventory.json',
    'baseline-main-css.patch' => 'historical/baseline-main-css.patch',
    'phase-1-environment.md' => 'phases/phase-01-environment.md',
    'phase-2-database.md' => 'phases/phase-02-database.md',
    'phase-3-filament.md' => 'phases/phase-03-filament.md',
    'phase-4-importer.md' => 'phases/phase-04-article-importer.md',
    'phase-5-frontend.md' => 'phases/phase-05-frontend-seo-pwa.md',
    'phase-6-deployment.md' => 'phases/phase-06-deployment.md',
];

function relativePath(string $fromDir, string $toAbs): string
{
    $from = explode('/', str_replace('\\', '/', $fromDir));
    $to = explode('/', str_replace('\\', '/', $toAbs));
    while ($from && $to && $from[0] === $to[0]) {
        array_shift($from);
        array_shift($to);
    }

    return implode('/', array_merge(array_fill(0, count($from), '..'), $to));
}

$files = [];
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($docs, FilesystemIterator::SKIP_DOTS));
foreach ($it as $f) {
    if ($f->isFile() && strtolower($f->getExtension()) === 'md') {
        $files[] = str_replace('\\', '/', $f->getPathname());
    }
}
$files[] = str_replace('\\', '/', $root.'/README.md');

$fixed = 0;
$broken = [];
$mode = $argv[1] ?? 'fix';

foreach ($files as $file) {
    if (! is_file($file)) {
        continue;
    }
    $dir = dirname($file);
    $text = file_get_contents($file);
    $original = $text;

    $text = preg_replace_callback('/\]\(([^)\s]+)\)/', function (array $m) use ($dir, $map, $root, &$fixed, &$broken, $file) {
        $target = $m[1];
        if (preg_match('#^(https?:|mailto:|#)#i', $target)) {
            return $m[0];
        }
        [$path, $frag] = array_pad(explode('#', $target, 2), 2, null);
        if ($path === '') {
            return $m[0];
        }
        $abs = realpath($dir.'/'.$path);
        if ($abs !== false) {
            return $m[0];
        }

        $base = basename($path);
        if (isset($map[$base])) {
            $newAbs = str_replace('\\', '/', $root.'/docs/'.$map[$base]);
            if (is_file($newAbs)) {
                $rel = relativePath($dir, $newAbs);
                $fixed++;

                return '](' . $rel . ($frag !== null ? '#'.$frag : '') . ')';
            }
        }

        $broken[] = str_replace($root.'/', '', $file).' -> '.$target;

        return $m[0];
    }, $text);

    if ($text !== $original && $mode === 'fix') {
        file_put_contents($file, $text);
    }
}

echo 'Files scanned: '.count($files)."\n";
echo 'Links rewritten: '.$fixed."\n";
echo 'Unresolved links: '.count($broken)."\n";
foreach (array_unique($broken) as $b) {
    echo '  BROKEN  '.$b."\n";
}
