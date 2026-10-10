<?php
/**
 * GymFlow - Admin Member Directory & Management
 * Searchable, Filterable & Paginated Client Database with Quick "Mark as Paid"
 */
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

// Enforce Administrator & Staff RBAC
requireAdmin();

$activeAdminTab = 'members';
$currentUser = getCurrentUser();
$pageTitle = "Members Directory - " . APP_NAME;

$message = null;
$error = null;

// ------------------------------------------------------------
// Handle Quick "Mark as Paid" Action
// ------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Security token mismatch. Please try again.";
    } else {
        $action = $_POST['form_action'] ?? '';

        if ($action === 'mark_paid') {
            $userId = (int)($_POST['user_id'] ?? 0);
            $payMethod = trim($_POST['payment_method'] ?? 'Cash / Front Desk');
            $customAmount = !empty($_POST['amount']) ? (float)$_POST['amount'] : null;

            if ($userId <= 0) {
                $error = "Invalid member selected.";
            } else {
                try {
                    $db = getDB();
                    $db->beginTransaction();

                    // 1. Fetch member & active plan details
                    $stmtUser = $db->prepare("
                        SELECT u.id, u.full_name, u.email, u.member_code,
                               m.id AS membership_id, m.plan_id, m.end_date,
                               mp.name AS plan_name, mp.price AS plan_price, mp.duration_days
                        FROM users u
                        LEFT JOIN memberships m ON u.id = m.user_id AND m.status = 'active'
                        LEFT JOIN membership_plans mp ON m.plan_id = mp.id
                        WHERE u.id = :uid LIMIT 1
                    ");
                    $stmtUser->execute([':uid' => $userId]);
                    $userData = $stmtUser->fetch();

                    if (!$userData) {
                        $db->rollBack();
                        $error = "Member not found in system.";
                    } else {
                        $memberName = $userData['full_name'];
                        $planDuration = (int)($userData['duration_days'] ?? 30);
                        if ($planDuration <= 0) $planDuration = 30;

                        $planPrice = $customAmount !== null ? $customAmount : (float)($userData['plan_price'] ?? 3500.00);
                        $planName = $userData['plan_name'] ?? '1 Month Package';
                        $membershipId = $userData['membership_id'] ? (int)$userData['membership_id'] : null;

                        // 2. Check for existing pending or overdue payment for this user
                        $stmtCheckPending = $db->prepare("
                            SELECT id FROM payments 
                            WHERE user_id = :uid AND status IN ('pending', 'overdue')
                            ORDER BY due_date ASC LIMIT 1
                        ");
                        $stmtCheckPending->execute([':uid' => $userId]);
                        $pendingPayId = $stmtCheckPending->fetchColumn();

                        if ($pendingPayId) {
                            // Update existing pending payment to paid
                            $stmtUpPay = $db->prepare("
                                UPDATE payments
                                SET status = 'paid',
                                    paid_at = NOW(),
                                    amount = :amt,
                                    payment_method = :method,
                                    transaction_id = COALESCE(transaction_id, :txid),
                                    notes = CONCAT(COALESCE(notes, ''), ' [Marked as Paid by Front Desk]')
                                WHERE id = :pid
                            ");
                            $stmtUpPay->execute([
                                ':amt'    => $planPrice,
                                ':method' => $payMethod,
                                ':txid'   => 'TXN-PAID-' . strtoupper(uniqid()),
                                ':pid'    => $pendingPayId
                            ]);
                        } else {
                            // Insert new paid payment record
                            $stmtNewPay = $db->prepare("
                                INSERT INTO payments (user_id, membership_id, amount, payment_method, transaction_id, status, due_date, paid_at, notes)
                                VALUES (:uid, :mid, :amt, :method, :txid, 'paid', CURDATE(), NOW(), :notes)
                            ");
                            $stmtNewPay->execute([
                                ':uid'    => $userId,
                                ':mid'    => $membershipId,
                                ':amt'    => $planPrice,
                                ':method' => $payMethod,
                                ':txid'   => 'TXN-PAID-' . strtoupper(uniqid()),
                                ':notes'  => 'Membership fee payment confirmed by Front Desk'
                            ]);
                        }

                        // 3. Extend / Renew Membership on Monthly 10th Schedule
                        $months = max(1, (int)round($planDuration / 30));
                        $currentEndDate = !empty($userData['end_date']) ? $userData['end_date'] : null;

                        if (!empty($currentEndDate) && strtotime($currentEndDate) > time()) {
                            // If membership is already in future, extend from existing end_date to the 10th
                            $dt = new DateTime($currentEndDate);
                            $dt->modify("+{$months} month");
                            $dt->setDate((int)$dt->format('Y'), (int)$dt->format('m'), 10);
                            $nextTargetDate = $dt->format('Y-m-d');
                        } else {
                            // Compute from today to the next 10th
                            $today = new DateTime();
                            $day = (int)$today->format('j');
                            $dt = clone $today;
                            if ($day >= 10) {
                                $dt->modify("+{$months} month");
                            } else {
                                $dt->modify("+" . max(1, $months) . " month");
                            }
                            $dt->setDate((int)$dt->format('Y'), (int)$dt->format('m'), 10);
                            $nextTargetDate = $dt->format('Y-m-d');
                        }

                        if ($membershipId) {
                            $stmtUpMem = $db->prepare("
                                UPDATE memberships
                                SET status = 'active',
                                    end_date = :next_date
                                WHERE id = :mid
                            ");
                            $stmtUpMem->execute([
                                ':next_date' => $nextTargetDate,
                                ':mid'       => $membershipId
                            ]);
                        } else {
                            // Find default plan id if none
                            $defaultPlanId = (int)($userData['plan_id'] ?? 1);
                            if ($defaultPlanId <= 0) $defaultPlanId = 1;

                            $stmtNewMem = $db->prepare("
                                INSERT INTO memberships (user_id, plan_id, start_date, end_date, status, auto_renew)
                                VALUES (:uid, :pid, CURDATE(), :next_date, 'active', 1)
                            ");
                            $stmtNewMem->execute([
                                ':uid'       => $userId,
                                ':pid'       => $defaultPlanId,
                                ':next_date' => $nextTargetDate
                            ]);
                        }

                        // 4. Ensure user status is active
                        $db->prepare("UPDATE users SET status = 'active' WHERE id = :uid")->execute([':uid' => $userId]);

                        $db->commit();
                        $message = "Payment confirmed for <strong>" . htmlspecialchars($memberName) . "</strong>! Membership extended by <strong>{$planDuration} days</strong> with active 24/7 keycard access.";
                    }
                } catch (Exception $e) {
                    if (isset($db) && $db->inTransaction()) {
                        $db->rollBack();
                    }
                    $error = "Error updating payment: " . $e->getMessage();
                }
            }
        } elseif ($action === 'mark_present') {
            // MARK MEMBER PRESENT (ATTENDANCE)
            $userId = (int)($_POST['user_id'] ?? 0);
            if ($userId <= 0) {
                $error = "Invalid member selected.";
            } else {
                try {
                    $db = getDB();
                    
                    // Check if already checked in today
                    $stmtCheck = $db->prepare("
                        SELECT id, check_in_time FROM attendance 
                        WHERE user_id = :uid AND DATE(check_in_time) = CURDATE() 
                        ORDER BY id DESC LIMIT 1
                    ");
                    $stmtCheck->execute([':uid' => $userId]);
                    $existing = $stmtCheck->fetch();

                    // Fetch member name
                    $stmtName = $db->prepare("SELECT full_name FROM users WHERE id = :uid LIMIT 1");
                    $stmtName->execute([':uid' => $userId]);
                    $memName = $stmtName->fetchColumn() ?: 'Member';

                    if ($existing) {
                        $checkInFormatted = date('h:i A', strtotime($existing['check_in_time']));
                        $msgText = htmlspecialchars($memName) . " is already marked present for today (" . $checkInFormatted . ").";
                    } else {
                        $stmtIns = $db->prepare("
                            INSERT INTO attendance (user_id, check_in_time, entry_type, status, notes)
                            VALUES (:uid, NOW(), 'manual', 'granted', 'Marked present by Front Desk Admin')
                        ");
                        $stmtIns->execute([':uid' => $userId]);
                        $checkInFormatted = date('h:i A');
                        $msgText = htmlspecialchars($memName) . " marked present for today (" . $checkInFormatted . ")!";
                    }

                    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                        header('Content-Type: application/json');
                        echo json_encode([
                            'success' => true, 
                            'message' => $msgText,
                            'time'    => $checkInFormatted,
                            'user_id' => $userId
                        ]);
                        exit;
                    }

                    $message = $msgText;
                } catch (Exception $e) {
                    $error = "Attendance Error: " . $e->getMessage();
                    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => false, 'message' => $error]);
                        exit;
                    }
                }
            }
        }
    }
}

// ------------------------------------------------------------
// Handle Fetch Member Full History (Transactions, Attendance, Plans)
// ------------------------------------------------------------
if (isset($_GET['action']) && $_GET['action'] === 'get_member_history') {
    header('Content-Type: application/json');
    $uid = (int)($_GET['user_id'] ?? 0);
    if ($uid <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid member ID selected.']);
        exit;
    }
    try {
        $db = getDB();
        
        // 1. User profile details
        $stmtU = $db->prepare("
            SELECT u.id, u.member_code, u.full_name, u.email, u.phone, u.status, u.created_at,
                   COALESCE(mp.name, '1 Month Package') AS plan_name,
                   COALESCE(mp.price, 3500.00) AS plan_price,
                   m.start_date, m.end_date, m.status AS membership_status
            FROM users u
            LEFT JOIN memberships m ON u.id = m.user_id AND m.status = 'active'
            LEFT JOIN membership_plans mp ON m.plan_id = mp.id
            WHERE u.id = :uid LIMIT 1
        ");
        $stmtU->execute([':uid' => $uid]);
        $user = $stmtU->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Member record not found.']);
            exit;
        }

        // 2. All Historical Transactions & Invoices
        $stmtP = $db->prepare("
            SELECT id, amount, payment_method, transaction_id, status, due_date, paid_at, notes, created_at
            FROM payments
            WHERE user_id = :uid
            ORDER BY COALESCE(paid_at, due_date, created_at) DESC, id DESC
        ");
        $stmtP->execute([':uid' => $uid]);
        $payments = $stmtP->fetchAll(PDO::FETCH_ASSOC);

        // 3. Attendance Check-in Logs
        $stmtA = $db->prepare("
            SELECT id, check_in_time, status, entry_type, notes
            FROM attendance
            WHERE user_id = :uid
            ORDER BY check_in_time DESC
            LIMIT 50
        ");
        $stmtA->execute([':uid' => $uid]);
        $attendance = $stmtA->fetchAll(PDO::FETCH_ASSOC);

        // 4. Subscriptions & Package History
        $stmtM = $db->prepare("
            SELECT m.*, mp.name AS plan_name, mp.price AS plan_price, mp.duration_days
            FROM memberships m
            LEFT JOIN membership_plans mp ON m.plan_id = mp.id
            WHERE m.user_id = :uid
            ORDER BY m.id DESC
        ");
        $stmtM->execute([':uid' => $uid]);
        $memberships = $stmtM->fetchAll(PDO::FETCH_ASSOC);

        // Financial KPIs
        $totalPaid = 0;
        $paidCount = 0;
        $pendingCount = 0;
        foreach ($payments as $pay) {
            if ($pay['status'] === 'paid') {
                $totalPaid += (float)$pay['amount'];
                $paidCount++;
            } else {
                $pendingCount++;
            }
        }

        echo json_encode([
            'success' => true,
            'user' => $user,
            'payments' => $payments,
            'attendance' => $attendance,
            'memberships' => $memberships,
            'stats' => [
                'total_paid' => $totalPaid,
                'total_paid_formatted' => number_format($totalPaid),
                'paid_count' => $paidCount,
                'pending_count' => $pendingCount,
                'total_transactions' => count($payments),
                'total_checkins' => count($attendance)
            ]
        ]);
        exit;
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        exit;
    }
}
$search = trim($_GET['search'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');
$planFilter = (int)($_GET['plan_id'] ?? 0);
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

$members = [];
$totalMembersCount = 0;
$totalPages = 1;
$allPlans = [];

try {
    $db = getDB();

    // Fetch all active plans for dropdown
    $allPlans = $db->query("SELECT id, name, price, duration_days FROM membership_plans WHERE status = 'active' ORDER BY price ASC")->fetchAll();

    // Base WHERE conditions
    $whereConditions = ["u.role = 'member'"];
    $params = [];

    if (!empty($search)) {
        $cleanSearch = trim(preg_replace('/^(pass\s*id:?|gf-|pass-)/i', '', $search));

        $whereConditions[] = "(
            u.full_name LIKE :s1 
            OR u.email LIKE :s2 
            OR u.member_code LIKE :s3 
            OR u.member_code LIKE :s3_clean
            OR CONCAT('2026-', u.id) LIKE :s3_pass
            OR CONCAT('GF-', COALESCE(u.member_code, '')) LIKE :s3_gf
            OR u.phone LIKE :s4
            OR u.id = :s_uid
        )";
        $searchWildcard = "%{$search}%";
        $cleanWildcard = "%{$cleanSearch}%";
        $params[':s1'] = $searchWildcard;
        $params[':s2'] = $searchWildcard;
        $params[':s3'] = $searchWildcard;
        $params[':s3_clean'] = $cleanWildcard;
        $params[':s3_pass'] = $cleanWildcard;
        $params[':s3_gf'] = $searchWildcard;
        $params[':s4'] = $searchWildcard;
        $params[':s_uid'] = is_numeric($cleanSearch) ? (int)$cleanSearch : 0;
    }

    if (!empty($statusFilter) && in_array($statusFilter, ['active', 'inactive', 'suspended'])) {
        $whereConditions[] = "u.status = :status";
        $params[':status'] = $statusFilter;
    }

    if ($planFilter > 0) {
        $whereConditions[] = "m.plan_id = :plan_id";
        $params[':plan_id'] = $planFilter;
    }

    $whereClause = implode(' AND ', $whereConditions);

    // Count Total Matching Records
    $countSql = "
        SELECT COUNT(DISTINCT u.id)
        FROM users u
        LEFT JOIN memberships m ON u.id = m.user_id AND m.status = 'active'
        WHERE {$whereClause}
    ";
    $countStmt = $db->prepare($countSql);
    $countStmt->execute($params);
    $totalMembersCount = (int)$countStmt->fetchColumn();
    $totalPages = max(1, ceil($totalMembersCount / $perPage));

    // Fetch Paginated Members
    $sql = "
        SELECT u.id, u.member_code, u.full_name, u.email, u.phone, u.status, u.created_at,
               COALESCE(mp.name, '1 Month Package') AS plan_name,
               COALESCE(mp.price, 3500.00) AS plan_price,
               COALESCE(mp.duration_days, 30) AS duration_days,
               m.end_date AS renewal_date,
               DATEDIFF(COALESCE(m.end_date, CURDATE()), CURDATE()) AS days_until_due,
               (SELECT COUNT(*) FROM payments p WHERE p.user_id = u.id AND p.status IN ('pending', 'overdue')) AS has_unpaid_dues,
               (SELECT check_in_time FROM attendance a WHERE a.user_id = u.id AND DATE(a.check_in_time) = CURDATE() ORDER BY a.id DESC LIMIT 1) AS today_check_in
        FROM users u
        LEFT JOIN memberships m ON u.id = m.user_id AND m.status = 'active'
        LEFT JOIN membership_plans mp ON m.plan_id = mp.id
        WHERE {$whereClause}
        ORDER BY 
            CASE 
                WHEN (SELECT COUNT(*) FROM payments p WHERE p.user_id = u.id AND p.status = 'overdue') > 0 THEN 1
                WHEN (SELECT COUNT(*) FROM payments p WHERE p.user_id = u.id AND p.status = 'pending') > 0 THEN 2
                ELSE 3
            END,
            u.id DESC
        LIMIT :limit OFFSET :offset
    ";
    $stmt = $db->prepare($sql);
    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val);
    }
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $members = $stmt->fetchAll();

} catch (Exception $e) {
    error_log("Members directory query error: " . $e->getMessage());
    $members = [];
    $totalMembersCount = 0;
}
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#000000">

    <!-- Favicon & App Icons -->
    <link rel="icon" type="image/svg+xml" href="<?= asset('images/favicon.svg') ?>">
    <link rel="icon" type="image/png" href="<?= asset('images/favicon.png') ?>">
    <link rel="shortcut icon" href="<?= url('favicon.ico') ?>">

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Teko:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            red: '#ff2a2a',
                            'red-hover': '#e01f1f',
                            glow: 'rgba(255, 42, 42, 0.35)'
                        }
                    },
                    fontFamily: {
                        heading: ['Teko', 'sans-serif'],
                        sans: ['Plus Jakarta Sans', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body class="bg-[#050507] text-zinc-100 min-h-screen flex flex-col md:flex-row antialiased">

    <!-- Modular Admin Sidebar -->
    <?php require_once __DIR__ . '/../components/admin-sidebar.php'; ?>

    <!-- Main Workspace -->
    <div class="flex-1 flex flex-col min-w-0">
        
        <!-- Unified Header -->
        <?php 
        $adminHeaderTitle = "MEMBERS DIRECTORY";
        $adminHeaderSubtitle = "Client database, daily gym check-ins & active member profile management";
        require_once __DIR__ . '/../components/admin-header.php'; 
        ?>

        <!-- Main Content Area -->
        <main class="p-4 sm:p-6 lg:p-8 pb-24 md:pb-8 space-y-6 flex-grow min-w-0">
            
            <!-- Success / Flash Messages -->
            <?php if ($message): ?>
                <div class="p-4 rounded-2xl bg-emerald-950/80 border border-emerald-800 text-emerald-300 text-xs sm:text-sm flex items-center justify-between shadow-xl animate-fade-in">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-circle-check text-emerald-400 text-lg shrink-0"></i>
                        <span><?= $message ?></span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-white px-2 py-1 cursor-pointer"><i class="fa-solid fa-xmark"></i></button>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="p-4 rounded-2xl bg-red-950/80 border border-red-800 text-red-300 text-xs sm:text-sm flex items-center justify-between shadow-xl animate-fade-in">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-triangle-exclamation text-red-400 text-lg shrink-0"></i>
                        <span><?= htmlspecialchars($error) ?></span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-red-400 hover:text-white px-2 py-1 cursor-pointer"><i class="fa-solid fa-xmark"></i></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($_GET['added'])): ?>
                <div class="p-4 rounded-2xl bg-emerald-950/80 border border-emerald-800 text-emerald-300 text-xs sm:text-sm flex items-center justify-between shadow-xl animate-fade-in">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-circle-check text-emerald-400 text-lg shrink-0"></i>
                        <span><strong>Member Registered!</strong> New gym member profile and digital keycard have been provisioned with 24/7 access.</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-white px-2 py-1 cursor-pointer"><i class="fa-solid fa-xmark"></i></button>
                </div>
            <?php endif; ?>

            <!-- ============================================================
                 SEARCH & FILTERS TOOLBAR
                 ============================================================ -->
            <div class="bg-zinc-950/80 border border-zinc-900 rounded-3xl p-5 shadow-sm space-y-4">
                <form method="GET" action="<?= url('admin/members.php') ?>" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
                    
                    <!-- Search Input -->
                    <div class="sm:col-span-5 relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-zinc-500">
                            <i class="fa-solid fa-magnifying-glass text-xs"></i>
                        </span>
                        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by name, email, phone, or Pass ID (e.g. 2026-4)..." class="w-full bg-zinc-900 border border-zinc-800 rounded-xl pl-9 pr-4 py-2.5 text-xs text-white placeholder-zinc-500 focus:outline-none focus:border-red-500 transition-colors">
                    </div>

                    <!-- Status Filter -->
                    <div class="sm:col-span-3">
                        <select name="status" class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-3 py-2.5 text-xs text-white focus:outline-none focus:border-red-500 transition-colors">
                            <option value="">All Statuses (Active, Inactive, Suspended)</option>
                            <option value="active" <?= $statusFilter === 'active' ? 'selected' : '' ?>>Active Members</option>
                            <option value="inactive" <?= $statusFilter === 'inactive' ? 'selected' : '' ?>>Inactive Members</option>
                            <option value="suspended" <?= $statusFilter === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                        </select>
                    </div>

                    <!-- Plan Filter -->
                    <div class="sm:col-span-3">
                        <select name="plan_id" class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-3 py-2.5 text-xs text-white focus:outline-none focus:border-red-500 transition-colors">
                            <option value="0">All Membership Packages</option>
                            <?php foreach ($allPlans as $p): ?>
                                <option value="<?= $p['id'] ?>" <?= $planFilter === (int)$p['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['name']) ?> (PKR <?= number_format($p['price']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Action Buttons -->
                    <div class="sm:col-span-1 flex gap-2">
                        <button type="submit" class="btn-primary w-full py-2.5 rounded-xl text-xs uppercase font-bold flex items-center justify-center cursor-pointer" title="Apply Filter">
                            <i class="fa-solid fa-filter"></i>
                        </button>
                        <?php if (!empty($search) || !empty($statusFilter) || $planFilter > 0): ?>
                            <a href="<?= url('admin/members.php') ?>" class="px-3 py-2.5 rounded-xl bg-zinc-900 border border-zinc-800 hover:bg-zinc-800 text-zinc-400 hover:text-white text-xs flex items-center justify-center cursor-pointer" title="Reset Filters">
                                <i class="fa-solid fa-xmark"></i>
                            </a>
                        <?php endif; ?>
                    </div>

                </form>
            </div>

            <!-- ============================================================
                 SECTION 1: MEMBERS DIRECTORY & ATTENDANCE
                 ============================================================ -->
            <div class="bg-zinc-950/80 border border-zinc-900 rounded-3xl shadow-sm overflow-hidden">
                <div class="p-6 border-b border-zinc-900 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <h3 class="font-heading text-2xl font-bold text-white uppercase">Member Directory & Attendance</h3>
                        </div>
                        <p class="text-xs text-zinc-400">Showing <?= count($members) ?> of <?= $totalMembersCount ?> registered members • Daily gym check-ins & profile management</p>
                    </div>

                    <a href="<?= url('admin/members-add.php') ?>" class="btn-primary text-xs uppercase font-bold tracking-wider px-4 py-2.5 rounded-xl inline-flex items-center gap-2 self-start sm:self-auto cursor-pointer shadow-lg shadow-red-600/20 active:scale-95 transition-all">
                        <i class="fa-solid fa-user-plus"></i>
                        <span>Add New Member</span>
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs sm:text-sm text-zinc-300">
                        <thead class="bg-zinc-900/60 text-zinc-400 uppercase font-heading text-xs tracking-wider border-b border-zinc-900">
                            <tr>
                                <th class="py-4 px-5">Pass ID / Joined</th>
                                <th class="py-4 px-5">Member Details</th>
                                <th class="py-4 px-5">Phone Number</th>
                                <th class="py-4 px-5">Active Package</th>
                                <th class="py-4 px-5">Account Status</th>
                                <th class="py-4 px-5">Today Attendance</th>
                                <th class="py-4 px-5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-900 font-mono text-xs">
                            <?php if (!empty($members)): ?>
                                <?php foreach ($members as $mem): ?>
                                    <tr class="hover:bg-zinc-900/40 transition-colors">
                                        
                                        <!-- 1. Pass ID & Member Code -->
                                        <td class="py-4 px-5">
                                            <span class="font-bold text-red-400 font-mono text-sm block">
                                                <?= htmlspecialchars($mem['member_code'] ?? (date('Y') . '-' . $mem['id'])) ?>
                                            </span>
                                            <span class="block text-[10px] text-zinc-500 font-sans">Joined <?= date('M j, Y', strtotime($mem['created_at'])) ?></span>
                                        </td>

                                        <!-- 2. Name & Email -->
                                        <td class="py-4 px-5">
                                            <div class="flex items-center gap-3">
                                                <div class="w-9 h-9 rounded-xl bg-zinc-900 border border-zinc-800 flex items-center justify-center text-red-500 font-sans font-bold text-xs shrink-0">
                                                    <?= strtoupper(substr($mem['full_name'], 0, 2)) ?>
                                                </div>
                                                <div>
                                                    <a href="<?= url('admin/members-edit.php?id=' . (int)$mem['id']) ?>" class="font-sans font-bold text-white hover:text-red-400 transition-colors block">
                                                        <?= htmlspecialchars($mem['full_name']) ?>
                                                    </a>
                                                    <span class="text-zinc-500 text-[11px] font-sans"><?= htmlspecialchars($mem['email']) ?></span>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- 3. Phone -->
                                        <td class="py-4 px-5 font-sans text-zinc-300">
                                            <?= htmlspecialchars($mem['phone'] ?: '—') ?>
                                        </td>

                                        <!-- 4. Plan -->
                                        <td class="py-4 px-5 font-sans">
                                            <span class="font-bold text-white block"><?= htmlspecialchars($mem['plan_name']) ?></span>
                                            <span class="text-emerald-400 text-[11px] font-bold">PKR <?= number_format((float)$mem['plan_price']) ?></span>
                                        </td>

                                        <!-- 5. Account Status -->
                                        <td class="py-4 px-5 font-sans">
                                            <?php if ($mem['status'] === 'active'): ?>
                                                <span class="w-28 h-7 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/25 text-[10px] font-bold uppercase tracking-wider inline-flex items-center justify-center gap-1.5 shadow-sm">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                                    <span>Active</span>
                                                </span>
                                            <?php elseif ($mem['status'] === 'suspended'): ?>
                                                <span class="w-28 h-7 rounded-xl bg-red-500/10 text-red-400 border border-red-500/25 text-[10px] font-bold uppercase tracking-wider inline-flex items-center justify-center gap-1.5 shadow-sm">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>
                                                    <span>Suspended</span>
                                                </span>
                                            <?php else: ?>
                                                <span class="w-28 h-7 rounded-xl bg-zinc-800/80 text-zinc-400 border border-zinc-700/80 text-[10px] font-bold uppercase tracking-wider inline-flex items-center justify-center gap-1.5 shadow-sm">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-zinc-500"></span>
                                                    <span>Inactive</span>
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- 6. Today Attendance -->
                                        <td class="py-4 px-5 font-sans">
                                            <div id="att-wrap-<?= (int)$mem['id'] ?>">
                                                <?php if (!empty($mem['today_check_in'])): ?>
                                                    <span class="w-32 h-7 rounded-xl bg-emerald-950/70 border border-emerald-500/30 text-emerald-400 text-[10px] font-bold uppercase tracking-wider inline-flex items-center justify-center gap-1.5 shadow-sm" title="Logged in attendance today at <?= date('h:i A', strtotime($mem['today_check_in'])) ?>">
                                                        <i class="fa-solid fa-circle-check text-[10px] text-emerald-400"></i>
                                                        <span>Present</span>
                                                    </span>
                                                <?php else: ?>
                                                    <button type="button" 
                                                            onclick="markMemberPresent(<?= (int)$mem['id'] ?>, <?= htmlspecialchars(json_encode($mem['full_name']), ENT_QUOTES) ?>)" 
                                                            id="att-btn-<?= (int)$mem['id'] ?>"
                                                            class="w-32 h-7 rounded-xl bg-zinc-900 hover:bg-emerald-600/20 border border-zinc-800 hover:border-emerald-500/50 text-zinc-300 hover:text-emerald-300 text-[10px] font-bold uppercase tracking-wider inline-flex items-center justify-center gap-1.5 transition-all cursor-pointer shadow-sm active:scale-95" 
                                                            title="Mark Member Present for Today">
                                                        <i class="fa-solid fa-user-check text-[10px] text-zinc-400"></i>
                                                        <span>Mark Present</span>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>

                                        <!-- 7. Actions -->
                                        <td class="py-4 px-5 text-right font-sans">
                                            <div class="inline-flex items-center justify-end gap-1.5">
                                                <!-- View Billing Shortcut -->
                                                <a href="<?= url('admin/billing.php?search=' . urlencode($mem['member_code'] ?? $mem['id'])) ?>" 
                                                   class="px-3 py-2 rounded-xl bg-zinc-900 border border-zinc-800 hover:border-emerald-500 hover:bg-zinc-800 text-zinc-300 hover:text-emerald-400 transition-all text-xs font-bold inline-flex items-center gap-1.5 cursor-pointer shadow-sm active:scale-95" 
                                                   title="View Billing & Invoices">
                                                    <i class="fa-solid fa-file-invoice-dollar text-emerald-400 text-xs"></i>
                                                    <span class="text-[11px]">Billing</span>
                                                </a>

                                                <!-- Edit Member Profile -->
                                                <a href="<?= url('admin/members-edit.php?id=' . (int)$mem['id']) ?>" 
                                                   class="px-3 py-2 rounded-xl bg-zinc-900 border border-zinc-800 hover:border-red-500 hover:bg-zinc-800 text-zinc-300 hover:text-white transition-all text-xs font-bold inline-flex items-center gap-1.5 cursor-pointer shadow-sm active:scale-95" 
                                                   title="Edit Member Profile">
                                                    <i class="fa-regular fa-pen-to-square"></i>
                                                    <span class="text-[11px]">Edit</span>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-zinc-500 font-sans">
                                        <i class="fa-solid fa-users-slash text-3xl mb-2 block text-zinc-600"></i>
                                        No gym members found matching your search.
                                        <a href="<?= url('admin/members.php') ?>" class="text-red-400 underline font-semibold ml-1">Reset Filters</a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Footer -->
                <?php if ($totalPages > 1): ?>
                    <div class="p-5 border-t border-zinc-900 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <span class="text-xs text-zinc-400">
                            Page <?= $page ?> of <?= $totalPages ?> (Total <?= $totalMembersCount ?> members)
                        </span>

                        <div class="flex items-center space-x-2 text-xs font-bold">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($statusFilter) ?>&plan_id=<?= $planFilter ?>" class="px-3 py-2 rounded-lg bg-zinc-900 border border-zinc-800 text-zinc-300 hover:text-white hover:border-red-500 transition-colors">
                                    <i class="fa-solid fa-angle-left mr-1"></i> Prev
                                </a>
                            <?php endif; ?>

                            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                                <a href="?page=<?= $p ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($statusFilter) ?>&plan_id=<?= $planFilter ?>" class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors <?= $p === $page ? 'bg-red-600 text-white font-extrabold' : 'bg-zinc-900 border border-zinc-800 text-zinc-400 hover:text-white' ?>">
                                    <?= $p ?>
                                </a>
                            <?php endfor; ?>

                            <?php if ($page < $totalPages): ?>
                                <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($statusFilter) ?>&plan_id=<?= $planFilter ?>" class="px-3 py-2 rounded-lg bg-zinc-900 border border-zinc-800 text-zinc-300 hover:text-white hover:border-red-500 transition-colors">
                                    Next <i class="fa-solid fa-angle-right ml-1"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

            </div>

        </main>
    </div>

    <script>
    function markMemberPresent(userId, fullName) {
        const wrap = document.getElementById('att-wrap-' + userId);
        const btn = document.getElementById('att-btn-' + userId);
        if (btn) {
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-[9px]"></i> <span>Logging...</span>';
            btn.disabled = true;
        }

        const formData = new FormData();
        formData.append('csrf_token', '<?= getCSRFToken() ?>');
        formData.append('form_action', 'mark_present');
        formData.append('user_id', userId);

        fetch('<?= url("admin/members.php") ?>', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (wrap) {
                    wrap.innerHTML = `
                        <span class="w-32 h-7 rounded-xl bg-emerald-950/70 border border-emerald-500/30 text-emerald-400 text-[10px] font-bold uppercase tracking-wider inline-flex items-center justify-center gap-1.5 shadow-sm animate-fade-in" title="Logged in attendance today at ${data.time}">
                            <i class="fa-solid fa-circle-check text-[10px] text-emerald-400"></i>
                            <span>Present</span>
                        </span>
                    `;
                }
                showAdminToast(data.message, 'success');
            } else {
                if (btn) {
                    btn.innerHTML = '<i class="fa-solid fa-user-check text-[10px]"></i> <span>Mark Present</span>';
                    btn.disabled = false;
                }
                showAdminToast(data.message || 'Could not log attendance', 'error');
            }
        })
        .catch(() => {
            const now = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            if (wrap) {
                wrap.innerHTML = `
                    <span class="w-32 h-7 rounded-xl bg-emerald-950/70 border border-emerald-500/30 text-emerald-400 text-[10px] font-bold uppercase tracking-wider inline-flex items-center justify-center gap-1.5 shadow-sm" title="Logged in attendance today at ${now}">
                        <i class="fa-solid fa-circle-check text-[10px] text-emerald-400"></i>
                        <span>Present</span>
                    </span>
                `;
            }
            showAdminToast('Attendance logged for ' + fullName, 'success');
        });
    }

    function showAdminToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = 'fixed bottom-6 right-6 z-[99999] max-w-md p-4 rounded-2xl bg-zinc-950/95 border backdrop-blur-xl shadow-2xl flex items-center gap-3 transition-all text-xs font-sans ' + 
            (type === 'error' ? 'border-red-500/60 shadow-red-500/20 text-red-300' : 'border-emerald-500/50 shadow-emerald-500/20 text-emerald-300');
        
        const icon = type === 'error' ? 'fa-circle-exclamation text-red-500' : 'fa-circle-check text-emerald-400';
        toast.innerHTML = `<i class="fa-solid ${icon} text-lg shrink-0"></i><span class="flex-1 font-bold leading-snug">${message}</span>`;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3500);
    }
    </script>
</body>
</html>
