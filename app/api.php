<?php
/**
 * GymFlow Mobile App - Internal JSON API Endpoint
 */
header('Content-Type: application/json');

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized session. Please login.']);
    exit;
}

$currentUser = getCurrentUser();
$userId = (int)($currentUser['id'] ?? 0);
$action = $_REQUEST['action'] ?? '';
$db = Database::getConnection();

if ($action === 'check_in') {
    try {
        $todayCheck = $db->prepare("SELECT id, check_in_time FROM attendance WHERE user_id = :uid AND DATE(check_in_time) = CURDATE() LIMIT 1");
        $todayCheck->execute([':uid' => $userId]);
        $existing = $todayCheck->fetch();

        if (!$existing) {
            $ins = $db->prepare("INSERT INTO attendance (user_id, check_in_time, entry_type, status) VALUES (:uid, NOW(), 'mobile_app_qr', 'granted')");
            $ins->execute([':uid' => $userId]);
            echo json_encode([
                'success' => true,
                'message' => 'Turnstile Gate Access Granted! Check-in recorded.',
                'status' => 'granted',
                'time' => date('h:i A')
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'already_checked' => true,
                'message' => 'You are already checked in today at ' . date('h:i A', strtotime($existing['check_in_time'])),
                'time' => date('h:i A', strtotime($existing['check_in_time']))
            ]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

if ($action === 'send_coach_message') {
    $text = trim($_POST['message'] ?? '');
    if (empty($text)) {
        echo json_encode(['success' => false, 'message' => 'Message cannot be empty.']);
        exit;
    }
    try {
        $ins = $db->prepare("INSERT INTO contact_messages (full_name, email, phone, subject, message, status, created_at) VALUES (:fn, :em, :ph, 'App Coach Consultation', :msg, 'unread', NOW())");
        $ins->execute([
            ':fn' => $currentUser['name'] ?? 'Member',
            ':em' => $currentUser['email'] ?? 'member@gymflow.com',
            ':ph' => $currentUser['phone'] ?? '+923000000000',
            ':msg' => $text
        ]);
        echo json_encode(['success' => true, 'message' => 'Your message has been sent to the coaching staff!']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to send message: ' . $e->getMessage()]);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Unknown action.']);
