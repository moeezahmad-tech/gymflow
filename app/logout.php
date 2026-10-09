<?php
/**
 * GymFlow Mobile App - Logout Handler
 */
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/auth.php';

logoutUser();

// Clean redirect to app login
$isAppSubdir = (strpos($_SERVER['REQUEST_URI'] ?? '', '/app') !== false);
$redirectUrl = $isAppSubdir ? url('app/index.php') : './index.php';
header('Location: ' . $redirectUrl);
exit;
