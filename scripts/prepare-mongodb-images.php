<?php

// Normalize generated assets to the user-requested web dimensions.
// Designs and text are supplied by ImageGen; this only resamples PNG output.
$root = dirname(__DIR__);
$source = $argv[1] ?? throw new RuntimeException('Pass the generated-image directory');
$files = [
    'exec-1fcefd6d-42d0-41e4-bce3-348c6648486d.png' => 'mongodb-installation-production-banner.png',
    'exec-9b7e7111-10ef-4844-ab35-8deb1838f1f2.png' => 'mongodb-architecture.png',
    'exec-c9987cc7-97a0-4957-8002-8ddbbecc4a00.png' => 'mongodb-security-architecture.png',
    'exec-c7197b36-5c25-4733-a02e-7aa4f94e78d5.png' => 'mongodb-replica-set.png',
];
if (isset($argv[2])) $files[$argv[2]] = 'mongodb-backup-monitoring.png';
foreach ($files as $input => $name) {
    $destination = $root.'/resources/assets/img/articles/'.(str_contains($name, 'banner') ? 'banners' : 'content');
    if (! is_dir($destination)) mkdir($destination, 0755, true);
    $path = $destination.'/'.$name;
    if (is_file($path)) continue;
    $original = imagecreatefrompng($source.'/'.$input);
    [$width, $height] = str_contains($name, 'banner') ? [1000, 1000] : [1920, 1080];
    $resized = imagecreatetruecolor($width, $height);
    imagecopyresampled($resized, $original, 0, 0, 0, 0, $width, $height, imagesx($original), imagesy($original));
    imagepng($resized, $path, 9);
    imagedestroy($original);
    imagedestroy($resized);
    echo $name.' '.$width.'x'.$height.PHP_EOL;
}
