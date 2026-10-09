<?php
/**
 * GymFlow - Application Configuration & Helpers
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

if (!defined('APP_INIT')) {
    define('APP_INIT', true);
}

// Start Session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Application Information
define('APP_NAME', 'Gym Flow');
define('APP_TAGLINE', 'Elevate Your Strength & Peak Performance');
define('APP_VERSION', '1.1.0');

// Base URL Auto-detection
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? 80) == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$baseUrl = rtrim($protocol . $host . ($scriptDir === '/' ? '' : $scriptDir), '/');
define('BASE_URL', $baseUrl);

// Currency Configuration
define('CURRENCY_SYMBOL', 'PKR ');
define('CURRENCY_CODE', 'PKR');

/**
 * Helper to format price in PKR
 */
function formatPrice($amount, $decimals = 0): string {
    return CURRENCY_SYMBOL . number_format((float)$amount, $decimals);
}

/**
 * Accurately calculate relative path back to project root
 */
function getRootPrefix(): string {
    $projectRoot = str_replace('\\', '/', realpath(dirname(__DIR__)) ?: dirname(__DIR__));
    $scriptFilename = $_SERVER['SCRIPT_FILENAME'] ?? '';
    
    if ($scriptFilename) {
        $currentScriptDir = str_replace('\\', '/', realpath(dirname($scriptFilename)) ?: dirname($scriptFilename));
        $pRootLower = strtolower(rtrim($projectRoot, '/'));
        $cDirLower = strtolower(rtrim($currentScriptDir, '/'));
        
        if (strpos($cDirLower, $pRootLower) === 0) {
            $sub = trim(substr($currentScriptDir, strlen($projectRoot)), '/\\');
            if ($sub !== '') {
                $parts = array_filter(explode('/', str_replace('\\', '/', $sub)));
                return str_repeat('../', count($parts));
            }
            return '';
        }
    }

    // Fallback based on SCRIPT_NAME
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $dir = trim(dirname($script), '/\\');
    if ($dir === '' || $dir === '.') {
        return '';
    }
    $parts = array_filter(explode('/', $dir));
    return str_repeat('../', count($parts));
}

/**
 * Helper to get asset URL
 */
function asset($path = ''): string {
    $path = ltrim($path, '/');
    $prefix = getRootPrefix();
    return $prefix . 'assets/' . $path;
}

/**
 * Helper to generate clean, extensionless page URLs
 */
function url(string $path = ''): string {
    $path = ltrim($path, '/');
    if ($path === '' || $path === 'index.php') {
        $cleanPath = '';
    } else {
        // Remove .php extension from routes (preserving query strings and hashes)
        $cleanPath = preg_replace('/\.php(\?|#|$)/', '$1', $path);
    }
    $prefix = getRootPrefix();
    return $prefix . ($cleanPath === '' ? ($prefix ? '' : './') : $cleanPath);
}

/**
 * Authentication Helpers
 */
function isLoggedIn(): bool {
    return !empty($_SESSION['user_id']) || !empty($_SESSION['logged_in']);
}

function getCurrentUser(): array {
    if (!isLoggedIn()) {
        return [];
    }
    return [
        'id'          => $_SESSION['user_id'] ?? null,
        'name'        => $_SESSION['user_name'] ?? 'Member',
        'email'       => $_SESSION['user_email'] ?? '',
        'role'        => $_SESSION['user_role'] ?? 'member',
        'plan'        => $_SESSION['user_plan'] ?? 'Standard Access',
        'member_id'   => $_SESSION['user_code'] ?? (date('Y') . '-' . ($_SESSION['user_id'] ?? '1')),
        'member_code' => $_SESSION['user_code'] ?? (date('Y') . '-' . ($_SESSION['user_id'] ?? '1'))
    ];
}

/**
 * Check if the given URI is active
 */
function isActiveRoute($pageName): string {
    $currentPage = basename($_SERVER['SCRIPT_NAME'] ?? '', '.php');
    return ($currentPage === $pageName) ? 'text-red-500 font-semibold' : 'text-zinc-300 hover:text-white';
}

/**
 * Helper to redirect to a path
 */
function redirect(string $path = '') {
    $target = url($path);
    header("Location: {$target}");
    exit;
}
