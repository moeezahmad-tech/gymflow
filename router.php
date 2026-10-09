<?php
/**
 * GymFlow - Local PHP Built-in Server Router
 * Usage: php -S localhost:8080 router.php
 */

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . $uri;

// 1. If physical file exists (CSS, JS, images, etc.), serve directly
if ($uri !== '/' && file_exists($file) && !is_dir($file)) {
    return false;
}

// 2. If extensionless URL matches a .php file, execute it
if ($uri !== '/' && file_exists($file . '.php')) {
    require $file . '.php';
    exit;
}

// 3. If directory contains index.php (e.g. /admin/, /user/)
if (is_dir($file) && file_exists($file . '/index.php')) {
    require $file . '/index.php';
    exit;
}

// 4. Default to index.php
require __DIR__ . '/index.php';
