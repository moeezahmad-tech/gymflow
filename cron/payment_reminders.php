<?php
/**
 * GymFlow - Automated Cron Job: Payment Due & Overdue Notification Scanner
 *
 * Usage via CLI:
 *   php cron/payment_reminders.php
 *
 * Usage via Webhook / Cron Service:
 *   https://yourdomain.com/cron/payment_reminders.php?token=GYMFLOW_CRON_SECRET
 */

// Security token for web-triggered crons
define('CRON_SECRET', 'GYMFLOW_CRON_SECRET');

// Verify token if invoked via HTTP
if (php_sapi_name() !== 'cli') {
    $providedToken = $_GET['token'] ?? '';
    if (!hash_equals(CRON_SECRET, $providedToken)) {
        http_response_code(403);
        die(json_encode(['status' => 'error', 'message' => 'Unauthorized cron invocation']));
    }
    header('Content-Type: application/json; charset=utf-8');
}

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/notifications.php';

$startTime = microtime(true);
$dispatchedReminders = NotificationEngine::scanAndSendDailyReminders();
$duration = round(microtime(true) - $startTime, 4);

$result = [
    'status'       => 'success',
    'timestamp'    => date('Y-m-d H:i:s'),
    'scan_date'    => date('Y-m-d'),
    'total_sent'   => count($dispatchedReminders),
    'duration_sec' => $duration,
    'details'      => $dispatchedReminders
];

if (php_sapi_name() === 'cli') {
    echo "======================================================" . PHP_EOL;
    echo " GymFlow Automated Payment Reminder Scanner" . PHP_EOL;
    echo "======================================================" . PHP_EOL;
    echo "Timestamp: " . $result['timestamp'] . PHP_EOL;
    echo "Scan Date: " . $result['scan_date'] . PHP_EOL;
    echo "Total Reminders Dispatched: " . $result['total_sent'] . PHP_EOL;
    echo "Execution Time: " . $duration . "s" . PHP_EOL;
    echo "------------------------------------------------------" . PHP_EOL;
    foreach ($dispatchedReminders as $item) {
        $statusText = $item['is_overdue'] ? "[OVERDUE]" : "[DUE TODAY]";
        echo "{$statusText} Member: {$item['member']} | Phone: {$item['phone']} | Amount: PKR {$item['amount']}" . PHP_EOL;
    }
    echo "======================================================" . PHP_EOL;
} else {
    echo json_encode($result, JSON_PRETTY_PRINT);
}
