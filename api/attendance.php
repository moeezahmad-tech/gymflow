<?php
/**
 * GymFlow - Front Desk Attendance & Access Control API
 * NOTE: Attendance module is temporarily commented out / disabled.
 */
header('Content-Type: application/json; charset=utf-8');

echo json_encode([
    'success' => false,
    'message' => 'Attendance module is currently disabled by administrator.'
]);
exit;
