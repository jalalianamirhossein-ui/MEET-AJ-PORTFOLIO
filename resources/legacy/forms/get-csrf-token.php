<?php

/**
 * Legacy path compatibility. Public traffic is routed by Laravel.
 * If a host still executes this file directly, hand the request to the app
 * so the same session cookie and CSRF token are used.
 */
require dirname(__DIR__).'/public/index.php';
