<?php
/**
 * GymFlow - Newsletter Subscription API Endpoint
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed. POST required.']);
    exit;
}

$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true) ?: $_POST;

$email = trim($input['email'] ?? '');

if (empty($email)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Please enter a valid email address.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'The email address format is invalid.']);
    exit;
}

try {
    $db = getDB();

    // Ensure newsletter_subscribers table exists
    $db->exec("
        CREATE TABLE IF NOT EXISTS newsletter_subscribers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL UNIQUE,
            ip_address VARCHAR(45) NULL,
            status ENUM('subscribed', 'unsubscribed') DEFAULT 'subscribed',
            subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

    // Check if already subscribed
    $checkStmt = $db->prepare("SELECT id, status FROM newsletter_subscribers WHERE email = :email");
    $checkStmt->execute([':email' => $email]);
    $existing = $checkStmt->fetch();

    if ($existing) {
        if ($existing['status'] === 'unsubscribed') {
            $updateStmt = $db->prepare("UPDATE newsletter_subscribers SET status = 'subscribed', subscribed_at = NOW() WHERE id = :id");
            $updateStmt->execute([':id' => $existing['id']]);
        }
        echo json_encode([
            'status'  => 'success',
            'message' => 'You are already subscribed to Gym Flow VIP updates and workout tips!'
        ]);
        exit;
    }

    // Insert new subscriber
    $insertStmt = $db->prepare("
        INSERT INTO newsletter_subscribers (email, ip_address, status, subscribed_at)
        VALUES (:email, :ip, 'subscribed', NOW())
    ");
    $insertStmt->execute([
        ':email' => $email,
        ':ip'    => $ip
    ]);

    // Also log in contact_messages so admin gets an inquiry alert in the admin portal
    try {
        $msgStmt = $db->prepare("
            INSERT INTO contact_messages (full_name, email, phone, program, message, status, created_at)
            VALUES (:name, :email, '', 'Newsletter Subscription', 'Subscribed to Gym Flow Newsletter for weekly workout tips & VIP perks.', 'unread', NOW())
        ");
        $msgStmt->execute([
            ':name'  => 'Newsletter Subscriber',
            ':email' => $email
        ]);
    } catch (Exception $e) {
        // Silently continue if contact_messages insertion fails
    }

    echo json_encode([
        'status'  => 'success',
        'message' => 'Thank you for subscribing! You will now receive exclusive Gym Flow training tips and VIP offers.'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Unable to save subscription: ' . $e->getMessage()
    ]);
}
