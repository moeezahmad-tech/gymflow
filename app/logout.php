<?php
/**
 * GymFlow Mobile App - Logout Handler
 */
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/auth.php';

logoutUser();
header('Location: ' . url('app/index.php'));
exit;
