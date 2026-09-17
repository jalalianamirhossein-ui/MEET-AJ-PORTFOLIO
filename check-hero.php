<?php

require __DIR__.'/vendor/autoload.php';

$r = new ReflectionClass(Filament\Support\Icons\Heroicon::class);
foreach ($r->getConstants() as $k => $v) {
    if (stripos($k, 'Wrench') !== false || stripos($k, 'Briefcase') !== false || stripos($k, 'Tool') !== false) {
        echo $k, PHP_EOL;
    }
}
