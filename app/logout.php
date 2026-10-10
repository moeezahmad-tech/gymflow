<?php
/**
 * GymFlow Mobile App - Dedicated Logout Endpoint
 */
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/auth.php';

logoutUser();

// Clean redirect to app login interface
$isAppSubdir = (strpos($_SERVER['REQUEST_URI'] ?? '', '/app') !== false);
$redirectUrl = $isAppSubdir ? url('app/index.php') : 'index.php';
header('Location: ' . $redirectUrl);
exit;
