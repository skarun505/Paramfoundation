<?php

/**
 * Laravel - A PHP Framework For Web Artisans
 * router for PHP built-in server
 */

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''
);

// Serve static files directly from public/
$publicPath = __DIR__ . '/public';
if ($uri !== '/' && file_exists($publicPath . $uri)) {
    return false;
}

// All other requests → Laravel's index.php
$_SERVER['SCRIPT_FILENAME'] = $publicPath . '/index.php';
require_once $publicPath . '/index.php';
