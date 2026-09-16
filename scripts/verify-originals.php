<?php
$root = dirname(__DIR__);
$baseline = json_decode(file_get_contents($root.'/docs/baseline-files.json'), true, flags: JSON_THROW_ON_ERROR);
$changed = [];
foreach ($baseline as $entry) {
    $file = $root.'/'.$entry['file'];
    if (!is_file($file) || hash_file('sha256', $file) !== $entry['sha256']) {
        $changed[] = $entry['file'];
    }
}
echo json_encode(['checked' => count($baseline), 'changed' => $changed], JSON_PRETTY_PRINT).PHP_EOL;
exit(count($changed) ? 1 : 0);
