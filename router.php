<?php
/**
 * GymFlow - Local PHP Built-in Server Router (.htaccess Emulator)
 * Enables extensionless URLs (/about, /pricing, /admin/newsletter, /login, etc.)
 * 
 * Usage:
 * php -S localhost:8080 router.php
 */

$rawUri = $_SERVER['REQUEST_URI'] ?? '/';
$uriPath = parse_url($rawUri, PHP_URL_PATH);
$uriPath = urldecode($uriPath);

// Normalize path for local filesystem
$normalizedPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $uriPath);
$targetFile = __DIR__ . $normalizedPath;

// 1. Root Request -> index.php
if ($uriPath === '/' || $uriPath === '') {
    $_SERVER['SCRIPT_FILENAME'] = __DIR__ . DIRECTORY_SEPARATOR . 'index.php';
    $_SERVER['SCRIPT_NAME'] = '/index.php';
    $_SERVER['PHP_SELF'] = '/index.php';
    require __DIR__ . DIRECTORY_SEPARATOR . 'index.php';
    exit;
}

// 2. Direct static assets (CSS, JS, images, fonts, icons, manifest, etc.) -> Let PHP serve directly
if (file_exists($targetFile) && !is_dir($targetFile)) {
    return false;
}

// 3. Extensionless PHP file in root or subdirectories (e.g., /about, /pricing, /admin/newsletter, /admin/members)
if (file_exists($targetFile . '.php')) {
    $_SERVER['SCRIPT_FILENAME'] = $targetFile . '.php';
    $_SERVER['SCRIPT_NAME'] = $uriPath . '.php';
    $_SERVER['PHP_SELF'] = $uriPath . '.php';
    require $targetFile . '.php';
    exit;
}

// 4. Directory with index.php (e.g., /admin, /admin/, /user, /user/)
if (is_dir($targetFile)) {
    $dirIndex = rtrim($targetFile, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'index.php';
    if (file_exists($dirIndex)) {
        $_SERVER['SCRIPT_FILENAME'] = $dirIndex;
        $_SERVER['SCRIPT_NAME'] = rtrim($uriPath, '/') . '/index.php';
        $_SERVER['PHP_SELF'] = rtrim($uriPath, '/') . '/index.php';
        require $dirIndex;
        exit;
    }
}

// 5. Explicit PHP file request (e.g., /contact.php, /login.php)
if (file_exists($targetFile) && substr($targetFile, -4) === '.php') {
    $_SERVER['SCRIPT_FILENAME'] = $targetFile;
    $_SERVER['SCRIPT_NAME'] = $uriPath;
    $_SERVER['PHP_SELF'] = $uriPath;
    require $targetFile;
    exit;
}

// 6. Not Found -> 404 response
http_response_code(404);
if (file_exists(__DIR__ . '/404.php')) {
    require __DIR__ . '/404.php';
} else {
    echo "<!DOCTYPE html><html lang='en'><head><title>404 Not Found</title><style>body{background:#09090b;color:#f4f4f5;font-family:sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;}h1{color:#ef4444;margin:0 0 10px 0;}a{color:#ef4444;text-decoration:none;font-weight:bold;}</style></head><body><div style='text-align:center;'><h1>404 Not Found</h1><p>The requested page <code>" . htmlspecialchars($uriPath) . "</code> was not found on GymFlow local server.</p><p><a href='/'>&larr; Return to Home</a></p></div></body></html>";
}
exit;
