<?php

require __DIR__.'/vendor/autoload.php';

foreach ([
    'Filament\\Actions\\ViewAction',
    'Filament\\Actions\\Action',
    'Illuminate\\Session\\TokenMismatchException',
    'Illuminate\\Foundation\\Http\\Middleware\\PreventRequestForgery',
    'Illuminate\\Foundation\\Http\\Middleware\\ValidateCsrfToken',
] as $c) {
    echo $c.' '.(class_exists($c) ? 'yes' : 'no').PHP_EOL;
}
