<?php
/**
 * GymFlow - Security, Authentication & Role-Based Access Control (RBAC)
 */

if (!defined('APP_INIT')) {
    require_once __DIR__ . '/app.php';
}
require_once __DIR__ . '/database.php';

// Session Security Configuration
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_samesite', 'Lax');
    session_start();
}

/**
 * Generate CSRF Token
 */
function getCSRFToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF Token
 */
function verifyCSRFToken($token = null): bool {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], (string)$token);
}

/**
 * Password Hashing & Verification Standards (Bcrypt with cost factor 10)
 */
function hashPassword(string $password): string {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
}

function verifyPassword(string $password, string $hash): bool {
    return password_verify($password, $hash);
}

/**
 * Sanitize User Input
 */
function sanitizeInput(string $data): string {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Authenticate User with Password Verification, Session Regeneration & Auto-Rehashing
 */
function authenticateUser(string $email, string $password, $roleFilter = null): array {
    $email = trim($email);

    if (empty($email) || empty($password)) {
        return ['success' => false, 'message' => 'Please provide both email address and password.'];
    }

    try {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT u.*, 
                   m.status AS membership_status, 
                   mp.name AS plan_name
            FROM users u
            LEFT JOIN memberships m ON u.id = m.user_id AND m.status = 'active'
            LEFT JOIN membership_plans mp ON m.plan_id = mp.id
            WHERE LOWER(u.email) = LOWER(:email)
            LIMIT 1
        ");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if (!$user) {
            return ['success' => false, 'message' => 'No account found with this email address.'];
        }

        // Verify Account Status
        if ($user['status'] !== 'active') {
            return ['success' => false, 'message' => 'Your account is currently ' . $user['status'] . '. Please contact support.'];
        }

        // Verify Role if specified
        if ($roleFilter && $roleFilter === 'admin' && !in_array($user['role'], ['admin', 'staff'])) {
            return ['success' => false, 'message' => 'Access denied. You do not have administrator privileges.'];
        }

        // Verify Password Hash
        if (!verifyPassword($password, $user['password'])) {
            return ['success' => false, 'message' => 'Invalid email address or password combination.'];
        }

        // Automatically upgrade/rehash password if algorithm or cost parameters changed
        if (password_needs_rehash($user['password'], PASSWORD_BCRYPT, ['cost' => 10])) {
            $newHash = hashPassword($password);
            $rehashStmt = $db->prepare("UPDATE users SET password = :p WHERE id = :uid");
            $rehashStmt->execute([':p' => $newHash, ':uid' => (int)$user['id']]);
        }

        // Regenerate Session ID to mitigate Session Fixation attacks
        if (!headers_sent()) {
            session_regenerate_id(true);
        }

        // Populate Secure Session
        $_SESSION['logged_in']   = true;
        $_SESSION['user_id']     = (int)$user['id'];
        $_SESSION['user_name']   = $user['full_name'];
        $_SESSION['user_email']  = $user['email'];
        $_SESSION['user_role']   = $user['role'];
        $_SESSION['user_code']   = $user['member_code'] ?? (date('Y') . '-' . $user['id']);
        $_SESSION['user_plan']   = $user['plan_name'] ?? 'Pro Athlete';
        $_SESSION['last_active'] = time();

        return [
            'success' => true,
            'role'    => $user['role'],
            'user'    => $user
        ];

    } catch (Exception $e) {
        error_log("Authentication DB Error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Database error during authentication: ' . $e->getMessage()];
    }
}

/**
 * Register a new member securely
 */
function registerMember(array $data): array {
    $fullName = trim($data['full_name'] ?? '');
    $email    = trim($data['email'] ?? '');
    $phone    = trim($data['phone'] ?? '');
    $password = $data['password'] ?? '';
    $planId   = (int)($data['plan_id'] ?? 2); // Default to Pro Athlete plan

    // Validations
    if (empty($fullName) || empty($email) || empty($password)) {
        return ['success' => false, 'message' => 'All required fields must be filled out.'];
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Please enter a valid email address.'];
    }

    if (strlen($password) < 6) {
        return ['success' => false, 'message' => 'Password must be at least 6 characters long.'];
    }

    try {
        $db = getDB();

        // Check if email already registered
        $stmt = $db->prepare("SELECT id FROM users WHERE LOWER(email) = LOWER(:email) LIMIT 1");
        $stmt->execute([':email' => $email]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'An account with this email address already exists.'];
        }

        // Generate smooth member code based on current year & user ID
        $tempCode = date('Y') . '-TEMP-' . rand(100, 999);

        // Secure password hashing using unified helper
        $hashedPassword = hashPassword($password);

        // Plan details lookup
        $planPrice = 8000.00;
        $planDays = 90;
        $planName = '3 Month Package';

        try {
            $stmtP = $db->prepare("SELECT name, price, duration_days FROM membership_plans WHERE id = :pid LIMIT 1");
            $stmtP->execute([':pid' => $planId]);
            $planRow = $stmtP->fetch();
            if ($planRow) {
                $planPrice = (float)$planRow['price'];
                $planDays = (int)$planRow['duration_days'];
                $planName = $planRow['name'];
            }
        } catch (Exception $pe) {
            if ($planId === 1) { $planPrice = 3000.00; $planDays = 30; $planName = '1 Month Package'; }
            elseif ($planId === 3) { $planPrice = 15000.00; $planDays = 180; $planName = '6 Month Package'; }
        }

        $db->beginTransaction();

        // 1. Insert User
        $insertUser = $db->prepare("
            INSERT INTO users (member_code, full_name, email, phone, password, role, status)
            VALUES (:code, :name, :email, :phone, :pass, 'member', 'active')
        ");
        $insertUser->execute([
            ':code'  => $tempCode,
            ':name'  => $fullName,
            ':email' => $email,
            ':phone' => $phone,
            ':pass'  => $hashedPassword
        ]);
        $newUserId = (int)$db->lastInsertId();

        // Update to smooth member code: Year-ID (e.g. 2026-12)
        $memberCode = date('Y') . '-' . $newUserId;
        $db->prepare("UPDATE users SET member_code = :code WHERE id = :id")->execute([':code' => $memberCode, ':id' => $newUserId]);

        // 2. Assign Membership
        $insertMembership = $db->prepare("
            INSERT INTO memberships (user_id, plan_id, start_date, end_date, status, auto_renew)
            VALUES (:uid, :pid, CURDATE(), DATE_ADD(CURDATE(), INTERVAL :days DAY), 'active', 1)
        ");
        $insertMembership->execute([
            ':uid'  => $newUserId,
            ':pid'  => $planId,
            ':days' => $planDays
        ]);
        $membershipId = (int)$db->lastInsertId();

        // 3. Log Initial Payment
        $insertPayment = $db->prepare("
            INSERT INTO payments (user_id, membership_id, amount, payment_method, transaction_id, status, due_date, paid_at, notes)
            VALUES (:uid, :mid, :amount, 'Online Registration', :txid, 'paid', CURDATE(), NOW(), :notes)
        ");
        $insertPayment->execute([
            ':uid'    => $newUserId,
            ':mid'    => $membershipId,
            ':amount' => $planPrice,
            ':txid'   => 'TXN-' . strtoupper(uniqid()),
            ':notes'  => $planName . ' Registration Payment'
        ]);

        $db->commit();

        // Establish Session
        if (!headers_sent()) {
            session_regenerate_id(true);
        }
        $_SESSION['logged_in']   = true;
        $_SESSION['user_id']     = $newUserId;
        $_SESSION['user_name']   = $fullName;
        $_SESSION['user_email']  = $email;
        $_SESSION['user_role']   = 'member';
        $_SESSION['user_code']   = $memberCode;
        $_SESSION['user_plan']   = $planName;
        $_SESSION['last_active'] = time();

        return ['success' => true, 'user_id' => $newUserId];

    } catch (Exception $e) {
        if (isset($db) && $db->inTransaction()) {
            $db->rollBack();
        }
        error_log("Registration DB Error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()];
    }
}

/**
 * Access Control Middleware: Require Login
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . url('login.php?error=unauthorized'));
        exit;
    }
}

/**
 * Access Control Middleware: Require Specific Role(s)
 */
function requireRole($roles) {
    requireLogin();
    
    $allowed = is_array($roles) ? $roles : [$roles];
    $currentUserRole = $_SESSION['user_role'] ?? 'member';

    if (!in_array($currentUserRole, $allowed)) {
        http_response_code(403);
        echo '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>403 Forbidden - Gym Flow</title>
            <script src="https://cdn.tailwindcss.com"></script>
        </head>
        <body class="bg-black text-white min-h-screen flex items-center justify-center p-4">
            <div class="max-w-md w-full bg-zinc-900 border border-zinc-800 rounded-3xl p-8 text-center">
                <div class="w-16 h-16 rounded-2xl bg-red-600/10 text-red-500 border border-red-500/30 flex items-center justify-center text-3xl mx-auto mb-4">
                    ⚠️
                </div>
                <h1 class="text-3xl font-bold uppercase tracking-wide">403 - Access Denied</h1>
                <p class="text-zinc-400 text-sm mt-3">You do not possess the required permissions (' . implode(', ', $allowed) . ') to access this administration section.</p>
                <div class="mt-6 flex justify-center gap-3">
                    <a href="' . url('user/index.php') . '" class="bg-red-600 hover:bg-red-700 text-white text-xs font-bold uppercase tracking-wider px-5 py-3 rounded-xl">Member Dashboard</a>
                    <a href="' . url('logout.php') . '" class="bg-zinc-800 hover:bg-zinc-700 text-zinc-300 text-xs font-bold uppercase tracking-wider px-5 py-3 rounded-xl">Sign Out</a>
                </div>
            </div>
        </body>
        </html>';
        exit;
    }
}

/**
 * Check if the currently logged-in user is an Admin
 */
function isAdmin(): bool {
    return isLoggedIn() && (($_SESSION['user_role'] ?? '') === 'admin');
}

/**
 * Check if the currently logged-in user is Staff
 */
function isStaff(): bool {
    return isLoggedIn() && (($_SESSION['user_role'] ?? '') === 'staff');
}

/**
 * Check if the currently logged-in user is a regular Member
 */
function isMember(): bool {
    return isLoggedIn() && (($_SESSION['user_role'] ?? '') === 'member');
}

/**
 * Access Control Middleware: Require Admin
 */
function requireAdmin() {
    requireRole(['admin', 'staff']);
}
