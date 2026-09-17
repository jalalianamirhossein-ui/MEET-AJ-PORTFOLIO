<?php

/**
 * Legacy path compatibility. Public traffic is routed by Laravel.
 * If a host still executes this file directly, hand the request to the app
 * so validation, CSRF, and storage stay in one place.
 */
require dirname(__DIR__).'/public/index.php';
