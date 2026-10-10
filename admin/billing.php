<?php
/**
 * GymFlow - Membership Billing & Transaction History
 * Dedicated Billing Hub, Fee Collections (10th Cutoff Schedule) & Member Audit Ledgers
 */
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

// Enforce Administrator & Staff RBAC
requireAdmin();

$activeAdminTab = 'billing';
$currentUser = getCurrentUser();
$pageTitle = "Membership Billing & History - " . APP_NAME;

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
                            $dt = new DateTime($currentEndDate);
                            $dt->modify("+{$months} month");
                            $dt->setDate((int)$dt->format('Y'), (int)$dt->format('m'), 10);
                            $nextTargetDate = $dt->format('Y-m-d');
                        } else {
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
                        $message = "Payment confirmed for <strong>" . htmlspecialchars($memberName) . "</strong>! Membership renewed on 10th monthly cycle.";
                    }
                } catch (Exception $e) {
                    if (isset($db) && $db->inTransaction()) {
                        $db->rollBack();
                    }
                    $error = "Error updating payment: " . $e->getMessage();
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

// ------------------------------------------------------------
// Search, Filter & Pagination Parameters
// ------------------------------------------------------------
$search = trim($_GET['search'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');
$planFilter = (int)($_GET['plan_id'] ?? 0);
$billingStatusFilter = trim($_GET['billing_status'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

$members = [];
$totalMembersCount = 0;
$totalPages = 1;
$allPlans = [];

// Overall Financial KPIs
$kpiTotalCollected = 0;
$kpiOverdueCount = 0;
$kpiPaidCount = 0;

try {
    $db = getDB();

    // Fetch all active plans for dropdown
    $allPlans = $db->query("SELECT id, name, price, duration_days FROM membership_plans WHERE status = 'active' ORDER BY price ASC")->fetchAll();

    // Overall KPIs
    $kpiTotalCollected = (float)$db->query("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'paid'")->fetchColumn();
    $kpiOverdueCount = (int)$db->query("SELECT COUNT(*) FROM payments WHERE status = 'overdue'")->fetchColumn();
    $kpiPaidCount = (int)$db->query("SELECT COUNT(*) FROM payments WHERE status = 'paid'")->fetchColumn();

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
    error_log("Billing directory query error: " . $e->getMessage());
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
        $adminHeaderTitle = "MEMBERSHIP BILLING & HISTORY";
        $adminHeaderSubtitle = "Monthly fee collection (10th cutoff schedule), dues tracking & complete transaction records";
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

            <!-- ============================================================
                 TOP KPI METRIC CARDS
                 ============================================================ -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-zinc-950/80 border border-zinc-900 rounded-3xl p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-xs uppercase font-bold text-zinc-400 tracking-wider">Total Revenue Paid</span>
                        <div class="w-9 h-9 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex items-center justify-center text-sm">
                            <i class="fa-solid fa-receipt"></i>
                        </div>
                    </div>
                    <h3 class="font-heading text-3xl font-bold text-emerald-400 mt-2">PKR <?= number_format($kpiTotalCollected) ?></h3>
                    <p class="text-xs text-zinc-500 mt-1"><?= $kpiPaidCount ?> confirmed fee payments</p>
                </div>

                <div class="bg-zinc-950/80 border border-zinc-900 rounded-3xl p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-xs uppercase font-bold text-zinc-400 tracking-wider">Active Billing Accounts</span>
                        <div class="w-9 h-9 rounded-xl bg-blue-500/10 text-blue-400 border border-blue-500/20 flex items-center justify-center text-sm">
                            <i class="fa-solid fa-id-card"></i>
                        </div>
                    </div>
                    <h3 class="font-heading text-3xl font-bold text-white mt-2"><?= $totalMembersCount ?> Members</h3>
                    <p class="text-xs text-zinc-500 mt-1">Monthly cycle (10th of every month)</p>
                </div>

                <div class="bg-zinc-950/80 border border-zinc-900 rounded-3xl p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-xs uppercase font-bold text-zinc-400 tracking-wider">Overdue / Pending</span>
                        <div class="w-9 h-9 rounded-xl bg-amber-500/10 text-amber-400 border border-amber-500/20 flex items-center justify-center text-sm">
                            <i class="fa-solid fa-circle-exclamation"></i>
                        </div>
                    </div>
                    <h3 class="font-heading text-3xl font-bold <?= $kpiOverdueCount > 0 ? 'text-red-400' : 'text-zinc-200' ?> mt-2"><?= $kpiOverdueCount ?> Overdue</h3>
                    <p class="text-xs text-zinc-500 mt-1">Requiring front-desk fee settlement</p>
                </div>
            </div>

            <!-- ============================================================
                 SEARCH & FILTERS TOOLBAR
                 ============================================================ -->
            <div class="bg-zinc-950/80 border border-zinc-900 rounded-3xl p-5 shadow-sm space-y-4">
                <form method="GET" action="<?= url('admin/billing.php') ?>" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
                    
                    <!-- Search Input -->
                    <div class="sm:col-span-5 relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-zinc-500">
                            <i class="fa-solid fa-magnifying-glass text-xs"></i>
                        </span>
                        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by member name, email, phone, or Pass ID..." class="w-full bg-zinc-900 border border-zinc-800 rounded-xl pl-9 pr-4 py-2.5 text-xs text-white placeholder-zinc-500 focus:outline-none focus:border-red-500 transition-colors">
                    </div>

                    <!-- Status Filter -->
                    <div class="sm:col-span-3">
                        <select name="status" class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-3 py-2.5 text-xs text-white focus:outline-none focus:border-red-500 transition-colors">
                            <option value="">All Member Statuses</option>
                            <option value="active" <?= $statusFilter === 'active' ? 'selected' : '' ?>>Active Members</option>
                            <option value="inactive" <?= $statusFilter === 'inactive' ? 'selected' : '' ?>>Inactive Members</option>
                            <option value="suspended" <?= $statusFilter === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                        </select>
                    </div>

                    <!-- Plan Filter -->
                    <div class="sm:col-span-3">
                        <select name="plan_id" class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-3 py-2.5 text-xs text-white focus:outline-none focus:border-red-500 transition-colors">
                            <option value="0">All Membership Plans</option>
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
                            <a href="<?= url('admin/billing.php') ?>" class="px-3 py-2.5 rounded-xl bg-zinc-900 border border-zinc-800 hover:bg-zinc-800 text-zinc-400 hover:text-white text-xs flex items-center justify-center cursor-pointer" title="Reset Filters">
                                <i class="fa-solid fa-xmark"></i>
                            </a>
                        <?php endif; ?>
                    </div>

                </form>
            </div>

            <!-- ============================================================
                 MEMBERSHIP BILLING & TRANSACTION RECORDS TABLE
                 ============================================================ -->
            <div class="bg-zinc-950/80 border border-zinc-900 rounded-3xl shadow-sm overflow-hidden">
                <div class="p-6 border-b border-zinc-900 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-file-invoice-dollar text-red-500"></i>
                            <h3 class="font-heading text-2xl font-bold text-white uppercase">Membership Billing & History</h3>
                        </div>
                        <p class="text-xs text-zinc-400">Monthly billing cycle (10th cutoff date), fee collection status & complete member audit records</p>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="px-3 py-1.5 rounded-xl bg-zinc-900 border border-zinc-800 text-[11px] font-bold text-zinc-300 inline-flex items-center gap-1.5">
                            <i class="fa-solid fa-calendar-days text-red-400"></i>
                            <span>Schedule: 10th of Every Month</span>
                        </span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs sm:text-sm text-zinc-300">
                        <thead class="bg-zinc-900/60 text-zinc-400 uppercase font-heading text-xs tracking-wider border-b border-zinc-900">
                            <tr>
                                <th class="py-4 px-5">Pass ID / Joined</th>
                                <th class="py-4 px-5">Member Details</th>
                                <th class="py-4 px-5">Package & Fee</th>
                                <th class="py-4 px-5">Payment / Expiry Date</th>
                                <th class="py-4 px-5">Billing Status</th>
                                <th class="py-4 px-5 text-right">Transaction History</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-900 font-mono text-xs">
                            <?php if (!empty($members)): ?>
                                <?php foreach ($members as $mem): ?>
                                    <?php
                                    $renewDate = !empty($mem['renewal_date']) ? date('M d, Y', strtotime($mem['renewal_date'])) : '—';
                                    $daysRemaining = isset($mem['days_until_due']) && $mem['renewal_date'] ? (int)$mem['days_until_due'] : 30;
                                    $hasUnpaidDues = (int)($mem['has_unpaid_dues'] ?? 0);
                                    $isOverdue = ($daysRemaining < 0 || $hasUnpaidDues > 0);
                                    $needsPayment = ($hasUnpaidDues > 0 || $daysRemaining <= 0 || $mem['status'] !== 'active');
                                    ?>
                                    <tr class="hover:bg-zinc-900/40 transition-colors <?= $isOverdue ? 'bg-red-950/10' : '' ?>">
                                        
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

                                        <!-- 3. Package & Fee -->
                                        <td class="py-4 px-5 font-sans">
                                            <span class="font-bold text-white block"><?= htmlspecialchars($mem['plan_name']) ?></span>
                                            <span class="text-emerald-400 text-[11px] font-bold">PKR <?= number_format((float)$mem['plan_price']) ?> / mo</span>
                                        </td>

                                        <!-- 4. Expiry / Next Payment Due (10th of Every Month Schedule) -->
                                        <td class="py-4 px-5 font-sans">
                                            <span class="font-bold text-zinc-200 block"><?= $renewDate ?></span>
                                            <?php if ($daysRemaining < 0): ?>
                                                <span class="px-2 py-0.5 rounded-md bg-red-950/80 text-red-400 border border-red-800/80 text-[10px] font-bold uppercase inline-block mt-0.5">
                                                    Overdue by <?= abs($daysRemaining) ?>d (10th Cutoff)
                                                </span>
                                            <?php elseif ($daysRemaining === 0): ?>
                                                <span class="px-2 py-0.5 rounded-md bg-red-500/20 text-red-400 border border-red-500/40 text-[10px] font-bold uppercase inline-block mt-0.5">
                                                    Due Today (10th Final Date)
                                                </span>
                                            <?php elseif ($daysRemaining <= 3): ?>
                                                <span class="px-2 py-0.5 rounded-md bg-amber-950/80 text-amber-400 border border-amber-800/80 text-[10px] font-bold uppercase inline-block mt-0.5">
                                                    Due in <?= $daysRemaining ?>d (10th)
                                                </span>
                                            <?php else: ?>
                                                <span class="text-[10px] text-zinc-400 font-semibold">
                                                    <?= $daysRemaining ?> days left (Due 10th)
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- 5. Billing Status (Paid Badge or Mark as Paid Button) -->
                                        <td class="py-4 px-5 font-sans">
                                            <?php if ($needsPayment): ?>
                                                <!-- PAYMENT DUE / OVERDUE: PROMINENT ACTION (w-32 py-2) -->
                                                <button type="button" 
                                                        onclick="openMarkPaidModal(<?= (int)$mem['id'] ?>, <?= htmlspecialchars(json_encode($mem['full_name']), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($mem['member_code'] ?? ('2026-' . $mem['id'])), ENT_QUOTES) ?>, <?= (float)$mem['plan_price'] ?>, <?= htmlspecialchars(json_encode($mem['plan_name']), ENT_QUOTES) ?>)" 
                                                        class="w-32 py-2 rounded-xl bg-amber-500/20 hover:bg-emerald-600 border border-amber-500/40 hover:border-emerald-600 text-amber-400 hover:text-white text-xs font-bold transition-all inline-flex items-center justify-center gap-1.5 cursor-pointer shadow-sm shrink-0 active:scale-95" 
                                                        title="Monthly Fee Due (Cutoff 10th) - Confirm Payment">
                                                    <i class="fa-solid fa-circle-exclamation text-xs"></i>
                                                    <span>Mark as Paid</span>
                                                </button>
                                            <?php else: ?>
                                                <!-- SETTLED & ACTIVE: PAID BADGE (EXACT SAME w-32 py-2) -->
                                                <span class="w-32 py-2 rounded-xl bg-emerald-500/10 border border-emerald-500/25 text-emerald-400 text-xs font-bold inline-flex items-center justify-center gap-1.5 shrink-0" title="Monthly fee is settled (Due next cycle on 10th)">
                                                    <i class="fa-solid fa-check text-xs"></i>
                                                    <span>Paid</span>
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- 6. Transaction History & Audit Records -->
                                        <td class="py-4 px-5 text-right font-sans">
                                            <div class="inline-flex items-center justify-end gap-1.5">
                                                <!-- View Full Member History & Transactions -->
                                                <button type="button" 
                                                        onclick="openMemberHistoryModal(<?= (int)$mem['id'] ?>)" 
                                                        class="px-3.5 py-2 rounded-xl bg-zinc-900 hover:bg-zinc-800 border border-zinc-800 hover:border-red-500/50 text-zinc-300 hover:text-white transition-all text-xs font-bold inline-flex items-center gap-2 cursor-pointer shadow-sm active:scale-95" 
                                                        title="View All Past Transactions, Fee History & Attendance Logs">
                                                    <i class="fa-solid fa-clock-rotate-left text-red-500 text-xs"></i>
                                                    <span>View History</span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-zinc-500 font-sans">
                                        <i class="fa-solid fa-receipt text-3xl mb-2 block text-zinc-600"></i>
                                        No billing records found matching your search.
                                        <a href="<?= url('admin/billing.php') ?>" class="text-red-400 underline font-semibold ml-1">Reset Filters</a>
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

    <!-- ============================================================
         MODAL 1: MARK AS PAID CONFIRMATION
         ============================================================ -->
    <div id="markPaidModal" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm hidden flex items-center justify-center p-4">
        <div class="bg-zinc-950 border border-zinc-800 rounded-3xl max-w-md w-full p-6 space-y-5 shadow-2xl animate-fade-in">
            <div class="flex items-center justify-between pb-3 border-b border-zinc-900">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-circle-check text-emerald-400 text-lg"></i>
                    <h3 class="font-heading text-lg font-bold text-white uppercase tracking-wider">Confirm Payment & Renewal</h3>
                </div>
                <button onclick="closeMarkPaidModal()" class="text-zinc-400 hover:text-white cursor-pointer"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>

            <form method="POST" action="<?= url('admin/billing.php') ?>" class="space-y-4">
                <?= getCSRFTokenInput() ?>
                <input type="hidden" name="form_action" value="mark_paid">
                <input type="hidden" id="modalUserId" name="user_id" value="">

                <!-- Member Summary Card -->
                <div class="bg-zinc-900/80 border border-zinc-800/80 rounded-2xl p-4 space-y-1">
                    <div class="flex items-center justify-between">
                        <span id="modalMemberName" class="font-bold text-white text-sm"></span>
                        <span id="modalMemberPassId" class="text-xs font-mono text-red-400 font-bold px-2 py-0.5 rounded bg-zinc-800 border border-zinc-700"></span>
                    </div>
                    <div class="text-xs text-zinc-400 flex items-center gap-1.5 pt-1">
                        <i class="fa-solid fa-cubes text-zinc-500"></i>
                        <span id="modalPlanName"></span>
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-zinc-300">Payment Method</label>
                    <select name="payment_method" class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-4 py-3 text-xs text-white focus:outline-none focus:border-emerald-500">
                        <option value="Cash / Front Desk" selected>💵 Cash / Front Desk Collection</option>
                        <option value="JazzCash">📱 JazzCash Direct Transfer</option>
                        <option value="EasyPaisa">📱 EasyPaisa Direct Transfer</option>
                        <option value="Credit / Debit Card">💳 POS / Credit or Debit Card</option>
                        <option value="Bank Transfer">🏦 Online Bank Wire Transfer</option>
                    </select>
                </div>

                <!-- Collected Amount -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-zinc-300">Fee Amount (PKR)</label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-bold text-emerald-400 font-mono">PKR</span>
                        <input type="number" id="modalAmount" name="amount" required step="100" class="w-full bg-zinc-900 border border-zinc-800 rounded-xl pl-12 pr-4 py-3 text-sm font-bold text-white focus:outline-none focus:border-emerald-500 font-mono">
                    </div>
                </div>

                <!-- Confirm Buttons -->
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="closeMarkPaidModal()" class="px-4 py-2.5 rounded-xl bg-zinc-900 border border-zinc-800 text-zinc-300 text-xs font-bold uppercase hover:bg-zinc-800 cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold uppercase tracking-wider flex items-center gap-2 shadow-lg shadow-emerald-600/20 cursor-pointer">
                        <i class="fa-solid fa-circle-check"></i>
                        <span>Confirm Payment</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================
         MODAL 2: COMPLETE MEMBER HISTORY & TRANSACTION LEDGER
         ============================================================ -->
    <div id="memberHistoryModal" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-md hidden flex items-center justify-center p-3 sm:p-6 overflow-y-auto">
        <div class="bg-[#0b0c10] border border-zinc-800 rounded-3xl max-w-4xl w-full p-5 sm:p-7 space-y-5 shadow-2xl animate-fade-in my-auto max-h-[92vh] flex flex-col">
            
            <!-- Modal Header -->
            <div class="flex items-start justify-between pb-4 border-b border-zinc-800/80 shrink-0">
                <div class="flex items-center gap-3.5">
                    <div id="histUserAvatar" class="w-12 h-12 rounded-2xl bg-gradient-to-br from-red-600 to-red-800 text-white font-bold flex items-center justify-center text-sm shadow-md border border-red-500/30 shrink-0">
                        --
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 id="histUserName" class="font-heading text-xl sm:text-2xl font-bold uppercase text-white leading-none">Loading Member...</h3>
                            <span id="histUserPassId" class="px-2 py-0.5 rounded-md bg-zinc-900 border border-zinc-800 text-red-400 font-mono text-[11px] font-bold"></span>
                            <span id="histUserStatusBadge" class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase"></span>
                        </div>
                        <p id="histUserSubInfo" class="text-xs text-zinc-400 mt-1 font-sans">Fetching verified records...</p>
                    </div>
                </div>
                <button type="button" onclick="closeMemberHistoryModal()" class="w-9 h-9 rounded-xl bg-zinc-900 border border-zinc-800 text-zinc-400 hover:text-white flex items-center justify-center transition-all cursor-pointer">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <!-- Loading Spinner State -->
            <div id="histModalLoading" class="py-16 text-center space-y-3">
                <i class="fa-solid fa-circle-notch fa-spin text-3xl text-red-500"></i>
                <p class="text-xs text-zinc-400 font-sans">Loading transactions & historical records...</p>
            </div>

            <!-- Content Area (Rendered dynamically) -->
            <div id="histModalBody" class="space-y-5 flex-1 overflow-y-auto pr-1 hidden">
                
                <!-- 3 Top KPI Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="bg-zinc-900/60 border border-zinc-800 rounded-2xl p-4 text-center">
                        <span class="text-[10px] uppercase font-bold text-zinc-400 block tracking-wider">Total Paid Revenue</span>
                        <div class="text-xl font-heading font-bold text-emerald-400 mt-1" id="histKpiTotalPaid">PKR 0</div>
                        <span class="text-[10px] text-zinc-500 font-sans" id="histKpiPaidCount">0 settled fee payments</span>
                    </div>

                    <div class="bg-zinc-900/60 border border-zinc-800 rounded-2xl p-4 text-center">
                        <span class="text-[10px] uppercase font-bold text-zinc-400 block tracking-wider">Gym Check-Ins</span>
                        <div class="text-xl font-heading font-bold text-white mt-1" id="histKpiCheckIns">0 Sessions</div>
                        <span class="text-[10px] text-zinc-500 font-sans">Lifetime gate entries</span>
                    </div>

                    <div class="bg-zinc-900/60 border border-zinc-800 rounded-2xl p-4 text-center">
                        <span class="text-[10px] uppercase font-bold text-zinc-400 block tracking-wider">Active Plan Schedule</span>
                        <div class="text-sm font-bold text-white mt-1.5 truncate" id="histKpiPlanName">—</div>
                        <span class="text-[10px] text-zinc-400 font-sans block mt-0.5" id="histKpiDueDate">Monthly 10th Cutoff</span>
                    </div>
                </div>

                <!-- Navigation Tabs -->
                <div class="flex items-center gap-2 border-b border-zinc-800 pb-2">
                    <button type="button" onclick="switchHistTab('transactions')" id="histTabBtn-transactions" class="hist-tab-btn active px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition-all bg-red-600 text-white shadow-md">
                        <i class="fa-solid fa-receipt mr-1.5"></i> All Transactions
                    </button>
                    <button type="button" onclick="switchHistTab('attendance')" id="histTabBtn-attendance" class="hist-tab-btn px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition-all text-zinc-400 hover:text-white bg-zinc-900/80 border border-zinc-800">
                        <i class="fa-solid fa-calendar-check mr-1.5"></i> Attendance Log
                    </button>
                    <button type="button" onclick="switchHistTab('subscriptions')" id="histTabBtn-subscriptions" class="hist-tab-btn px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition-all text-zinc-400 hover:text-white bg-zinc-900/80 border border-zinc-800">
                        <i class="fa-solid fa-id-card mr-1.5"></i> Subscription History
                    </button>
                </div>

                <!-- TAB 1: ALL TRANSACTIONS & BILLING LEDGER -->
                <div id="histTabPane-transactions" class="hist-tab-pane space-y-3">
                    <div class="overflow-x-auto rounded-2xl border border-zinc-800/80">
                        <table class="w-full text-left text-xs font-mono">
                            <thead class="bg-zinc-900/80 text-zinc-400 uppercase text-[10px] font-heading tracking-wider border-b border-zinc-800">
                                <tr>
                                    <th class="py-3 px-4">Date / Time</th>
                                    <th class="py-3 px-4">Txn / Invoice ID</th>
                                    <th class="py-3 px-4">Amount</th>
                                    <th class="py-3 px-4">Payment Method</th>
                                    <th class="py-3 px-4">Status</th>
                                    <th class="py-3 px-4">Notes</th>
                                </tr>
                            </thead>
                            <tbody id="histTransactionsTableBody" class="divide-y divide-zinc-900 font-sans text-xs">
                                <!-- Populated dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB 2: ATTENDANCE CHECK-IN LOGS -->
                <div id="histTabPane-attendance" class="hist-tab-pane hidden space-y-3">
                    <div class="overflow-x-auto rounded-2xl border border-zinc-800/80">
                        <table class="w-full text-left text-xs font-mono">
                            <thead class="bg-zinc-900/80 text-zinc-400 uppercase text-[10px] font-heading tracking-wider border-b border-zinc-800">
                                <tr>
                                    <th class="py-3 px-4">Date & Time</th>
                                    <th class="py-3 px-4">Gate Status</th>
                                    <th class="py-3 px-4">Access Type</th>
                                    <th class="py-3 px-4">Details / Notes</th>
                                </tr>
                            </thead>
                            <tbody id="histAttendanceTableBody" class="divide-y divide-zinc-900 font-sans text-xs">
                                <!-- Populated dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB 3: SUBSCRIPTIONS & PLANS -->
                <div id="histTabPane-subscriptions" class="hist-tab-pane hidden space-y-3">
                    <div class="overflow-x-auto rounded-2xl border border-zinc-800/80">
                        <table class="w-full text-left text-xs font-mono">
                            <thead class="bg-zinc-900/80 text-zinc-400 uppercase text-[10px] font-heading tracking-wider border-b border-zinc-800">
                                <tr>
                                    <th class="py-3 px-4">Plan Name</th>
                                    <th class="py-3 px-4">Monthly Fee</th>
                                    <th class="py-3 px-4">Start Date</th>
                                    <th class="py-3 px-4">End Date (Due 10th)</th>
                                    <th class="py-3 px-4">Plan Status</th>
                                </tr>
                            </thead>
                            <tbody id="histSubscriptionsTableBody" class="divide-y divide-zinc-900 font-sans text-xs">
                                <!-- Populated dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <!-- Modal Footer -->
            <div class="pt-3 border-t border-zinc-800/80 flex items-center justify-between text-xs text-zinc-400 shrink-0">
                <span class="text-[11px] font-sans text-zinc-500">GymFlow Verified Client Audit Ledger</span>
                <button type="button" onclick="closeMemberHistoryModal()" class="px-4 py-2 rounded-xl bg-zinc-900 border border-zinc-800 text-zinc-300 hover:text-white font-bold text-xs uppercase cursor-pointer">
                    Close History
                </button>
            </div>

        </div>
    </div>

    <script>
    function openMarkPaidModal(userId, fullName, memberCode, planPrice, planName) {
        document.getElementById('modalUserId').value = userId;
        document.getElementById('modalMemberName').textContent = fullName;
        document.getElementById('modalMemberPassId').textContent = memberCode;
        document.getElementById('modalPlanName').textContent = planName;
        document.getElementById('modalAmount').value = planPrice;
        document.getElementById('markPaidModal').classList.remove('hidden');
    }

    function closeMarkPaidModal() {
        document.getElementById('markPaidModal').classList.add('hidden');
    }

    // Member History & Transaction Ledger Engine
    function openMemberHistoryModal(userId) {
        const modal = document.getElementById('memberHistoryModal');
        const loading = document.getElementById('histModalLoading');
        const body = document.getElementById('histModalBody');
        
        modal.classList.remove('hidden');
        loading.classList.remove('hidden');
        body.classList.add('hidden');

        // Reset to first tab
        switchHistTab('transactions');

        fetch(`<?= url("admin/billing.php") ?>?action=get_member_history&user_id=${userId}`)
            .then(res => res.json())
            .then(data => {
                loading.classList.add('hidden');
                body.classList.remove('hidden');

                if (!data.success) {
                    showAdminToast(data.message || 'Could not load member history', 'error');
                    closeMemberHistoryModal();
                    return;
                }

                const u = data.user;
                const stats = data.stats;

                // Header info
                document.getElementById('histUserName').textContent = u.full_name || 'Member';
                document.getElementById('histUserAvatar').textContent = (u.full_name || 'M').substring(0, 2).toUpperCase();
                document.getElementById('histUserPassId').textContent = u.member_code || `2026-${u.id}`;
                document.getElementById('histUserSubInfo').textContent = `${u.email || ''} • ${u.phone || 'No phone'} • Member since ${new Date(u.created_at).toLocaleDateString(undefined, {month: 'short', day: 'numeric', year: 'numeric'})}`;

                // Status Badge
                const statusBadge = document.getElementById('histUserStatusBadge');
                if (u.status === 'active') {
                    statusBadge.className = 'px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-emerald-500/10 text-emerald-400 border border-emerald-500/20';
                    statusBadge.textContent = 'Active';
                } else {
                    statusBadge.className = 'px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-red-500/10 text-red-400 border border-red-500/20';
                    statusBadge.textContent = u.status || 'Inactive';
                }

                // Top KPIs
                document.getElementById('histKpiTotalPaid').textContent = `PKR ${stats.total_paid_formatted}`;
                document.getElementById('histKpiPaidCount').textContent = `${stats.paid_count} settled • ${stats.pending_count} pending`;
                document.getElementById('histKpiCheckIns').textContent = `${stats.total_checkins} Sessions`;
                document.getElementById('histKpiPlanName').textContent = u.plan_name || 'Standard Plan';
                document.getElementById('histKpiDueDate').textContent = u.end_date ? `Renewal: ${u.end_date} (10th Cutoff)` : 'Monthly 10th Cutoff';

                // 1. Render Transactions Table
                const txBody = document.getElementById('histTransactionsTableBody');
                if (!data.payments || data.payments.length === 0) {
                    txBody.innerHTML = `<tr><td colspan="6" class="py-8 text-center text-zinc-500 font-sans"><i class="fa-solid fa-receipt text-2xl mb-1 block"></i>No transaction records logged yet for this member.</td></tr>`;
                } else {
                    txBody.innerHTML = data.payments.map(p => {
                        const dateStr = p.paid_at ? new Date(p.paid_at).toLocaleString() : (p.due_date || p.created_at);
                        const isPaid = p.status === 'paid';
                        const statusPill = isPaid 
                            ? `<span class="px-2 py-0.5 rounded-md bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-[10px] font-bold uppercase">PAID</span>`
                            : (p.status === 'overdue' 
                                ? `<span class="px-2 py-0.5 rounded-md bg-red-500/20 text-red-400 border border-red-500/30 text-[10px] font-bold uppercase">OVERDUE</span>`
                                : `<span class="px-2 py-0.5 rounded-md bg-amber-500/20 text-amber-400 border border-amber-500/30 text-[10px] font-bold uppercase">PENDING</span>`);
                        
                        return `
                            <tr class="hover:bg-zinc-900/40">
                                <td class="py-3 px-4 font-mono text-zinc-300">${dateStr}</td>
                                <td class="py-3 px-4 font-mono text-red-400 font-bold">${p.transaction_id || `TXN-${p.id}`}</td>
                                <td class="py-3 px-4 font-mono font-bold text-white">PKR ${Number(p.amount).toLocaleString()}</td>
                                <td class="py-3 px-4 font-sans text-zinc-300">${p.payment_method || 'Cash / Desk'}</td>
                                <td class="py-3 px-4">${statusPill}</td>
                                <td class="py-3 px-4 font-sans text-zinc-400 text-[11px] max-w-xs truncate">${p.notes || '—'}</td>
                            </tr>
                        `;
                    }).join('');
                }

                // 2. Render Attendance Table
                const attBody = document.getElementById('histAttendanceTableBody');
                if (!data.attendance || data.attendance.length === 0) {
                    attBody.innerHTML = `<tr><td colspan="4" class="py-8 text-center text-zinc-500 font-sans"><i class="fa-solid fa-calendar-xmark text-2xl mb-1 block"></i>No gym check-in records found.</td></tr>`;
                } else {
                    attBody.innerHTML = data.attendance.map(a => `
                        <tr class="hover:bg-zinc-900/40">
                            <td class="py-3 px-4 font-mono text-zinc-300">${new Date(a.check_in_time).toLocaleString()}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-md bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-[10px] font-bold uppercase inline-flex items-center gap-1">
                                    <i class="fa-solid fa-circle-check text-[9px]"></i> Granted
                                </span>
                            </td>
                            <td class="py-3 px-4 font-sans uppercase text-[10px] font-bold text-zinc-400">${a.entry_type || 'Turnstile QR'}</td>
                            <td class="py-3 px-4 font-sans text-zinc-400 text-[11px]">${a.notes || 'Normal entry scan'}</td>
                        </tr>
                    `).join('');
                }

                // 3. Render Subscriptions Table
                const subBody = document.getElementById('histSubscriptionsTableBody');
                if (!data.memberships || data.memberships.length === 0) {
                    subBody.innerHTML = `<tr><td colspan="5" class="py-8 text-center text-zinc-500 font-sans">No membership subscriptions recorded.</td></tr>`;
                } else {
                    subBody.innerHTML = data.memberships.map(m => `
                        <tr class="hover:bg-zinc-900/40">
                            <td class="py-3 px-4 font-sans font-bold text-white">${m.plan_name || 'Standard Athletic Pass'}</td>
                            <td class="py-3 px-4 font-mono text-emerald-400 font-bold">PKR ${Number(m.plan_price || 3500).toLocaleString()}</td>
                            <td class="py-3 px-4 font-mono text-zinc-300">${m.start_date || '—'}</td>
                            <td class="py-3 px-4 font-mono text-zinc-300">${m.end_date || '—'} (10th)</td>
                            <td class="py-3 px-4 font-sans uppercase text-[10px] font-bold ${m.status === 'active' ? 'text-emerald-400' : 'text-zinc-500'}">${m.status || 'Active'}</td>
                        </tr>
                    `).join('');
                }

            })
            .catch(err => {
                loading.classList.add('hidden');
                showAdminToast('Failed to fetch history: ' + err, 'error');
                closeMemberHistoryModal();
            });
    }

    function closeMemberHistoryModal() {
        document.getElementById('memberHistoryModal').classList.add('hidden');
    }

    function switchHistTab(tabName) {
        document.querySelectorAll('.hist-tab-btn').forEach(btn => {
            btn.classList.remove('bg-red-600', 'text-white', 'shadow-md');
            btn.classList.add('text-zinc-400', 'hover:text-white', 'bg-zinc-900/80', 'border', 'border-zinc-800');
        });
        document.querySelectorAll('.hist-tab-pane').forEach(p => p.classList.add('hidden'));

        const targetBtn = document.getElementById(`histTabBtn-${tabName}`);
        const targetPane = document.getElementById(`histTabPane-${tabName}`);
        if (targetBtn) {
            targetBtn.classList.remove('text-zinc-400', 'hover:text-white', 'bg-zinc-900/80', 'border', 'border-zinc-800');
            targetBtn.classList.add('bg-red-600', 'text-white', 'shadow-md');
        }
        if (targetPane) {
            targetPane.classList.remove('hidden');
        }
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

    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeMarkPaidModal();
            closeMemberHistoryModal();
        }
    });
    </script>
</body>
</html>
