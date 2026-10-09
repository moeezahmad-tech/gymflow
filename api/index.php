<?php
/**
 * GymFlow - RESTful API Gateway
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';

$action = $_GET['action'] ?? 'status';

switch ($action) {
    case 'status':
        echo json_encode([
            'status' => 'success',
            'app' => APP_NAME,
            'version' => APP_VERSION,
            'database' => 'configured',
            'timestamp' => time()
        ]);
        break;

    case 'plans':
        try {
            $db = getDB();
            $stmt = $db->query("SELECT * FROM membership_plans WHERE status = 'active' ORDER BY price ASC");
            $plans = $stmt->fetchAll();
            echo json_encode(['status' => 'success', 'data' => $plans]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'contact':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'POST method required']);
            break;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $fullName = trim($input['full_name'] ?? '');
        $email    = trim($input['email'] ?? '');
        $phone    = trim($input['phone'] ?? '');
        $program  = trim($input['program'] ?? 'General Inquiry');
        $message  = trim($input['message'] ?? '');

        if (empty($fullName) || empty($email) || empty($message)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Please provide full name, email, and message.']);
            break;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Please provide a valid email address.']);
            break;
        }

        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("
                INSERT INTO contact_messages (full_name, email, phone, program, message, status, created_at)
                VALUES (:name, :email, :phone, :program, :msg, 'unread', NOW())
            ");
            $stmt->execute([
                ':name'    => $fullName,
                ':email'   => $email,
                ':phone'   => $phone,
                ':program' => $program,
                ':msg'     => $message
            ]);
            echo json_encode([
                'status'  => 'success',
                'message' => 'Inquiry received successfully! Our front desk team will contact you shortly.',
                'id'      => (int)$db->lastInsertId()
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
        break;

    default:
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Endpoint not found']);
        break;
}
