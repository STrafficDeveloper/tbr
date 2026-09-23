<?php

declare(strict_types=1);

/**
 * Local development only, standing in for public/.htaccess:
 *   php -S 127.0.0.1:8000 -t public bin/dev-router.php
 * Real files (CSS, images) are served as-is; everything else, including
 * /sitemap.xml and /robots.txt, goes through the app.
 */

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$file = dirname(__DIR__) . '/public' . (is_string($path) ? $path : '/');

if (is_string($path) && $path !== '/' && is_file($file)) {
    return false;
}

require dirname(__DIR__) . '/public/index.php';
