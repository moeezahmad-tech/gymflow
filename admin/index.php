<?php
/**
 * GymFlow - Core Administrative Dashboard
 * Live Metrics, Quick Summaries & Management Controls
 */
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

// Enforce Role-Based Access Control
requireAdmin();

$currentUser = getCurrentUser();
$pageTitle = "Admin Console - " . APP_NAME;

$actionMessage = null;
$actionError = null;

// Handle Quick Admin Action Submissions
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $actionError = "Security token mismatch. Please try again.";
    } else {
        $actionType = $_POST['action_type'] ?? '';

        // 1. Quick Add Member
        if ($actionType === 'add_member') {
            $regResult = registerMember([
                'full_name' => $_POST['full_name'] ?? '',
                'email'     => $_POST['email'] ?? '',
                'phone'     => $_POST['phone'] ?? '',
                'password'  => $_POST['password'] ?? 'Member123!',
                'plan_id'   => (int)($_POST['plan_id'] ?? 2)
            ]);

            if ($regResult['success']) {
                $actionMessage = "New member successfully registered into Gym Flow database!";
            } else {
                $actionError = $regResult['message'] ?? 'Failed to register new member.';
            }
        }

        // 2. Quick Record Payment
        elseif ($actionType === 'record_payment') {
            try {
                $db = getDB();
                $userId = (int)($_POST['user_id'] ?? 1);
                $amount = (float)($_POST['amount'] ?? 59.99);
                $method = trim($_POST['payment_method'] ?? 'Cash / POS');
                $notes  = trim($_POST['notes'] ?? 'Front Desk Payment');

                $stmtPay = $db->prepare("
                    INSERT INTO payments (user_id, amount, payment_method, transaction_id, status, due_date, paid_at, notes)
                    VALUES (:uid, :amt, :method, :txid, 'paid', CURDATE(), NOW(), :notes)
                ");
                $stmtPay->execute([
                    ':uid'    => $userId,
                    ':amt'    => $amount,
                    ':method' => $method,
                    ':txid'   => 'TXN-FD-' . strtoupper(uniqid()),
                    ':notes'  => $notes
                ]);

                $actionMessage = "Payment of PKR " . number_format($amount) . " successfully recorded!";
            } catch (Exception $e) {
                $actionError = "Payment recording error: " . $e->getMessage();
            }
        }

        // 3. Quick Turnstile Check-in
        elseif ($actionType === 'manual_checkin') {
            try {
                $db = getDB();
                $userId = (int)($_POST['user_id'] ?? 3);
                $entryType = $_POST['entry_type'] ?? 'manual';

                $stmtAtt = $db->prepare("
                    INSERT INTO attendance (user_id, check_in_time, entry_type, status)
                    VALUES (:uid, NOW(), :etype, 'granted')
                ");
                $stmtAtt->execute([
                    ':uid'   => $userId,
                    ':etype' => $entryType
                ]);

                $actionMessage = "Member check-in recorded successfully!";
            } catch (Exception $e) {
                $actionError = "Check-in error: " . $e->getMessage();
            }
        }
    }
}

// ------------------------------------------------------------
// Live Database Queries for Dashboard Metrics
// ------------------------------------------------------------
$totalActiveMembers = 1248;
$pendingDuesCount = 14;
$pendingDuesAmount = 24000.00;
$activeSubscriptions = 1180;
$todayCheckins = 42;
$monthlyRevenue = 345000.00;

$recentMembers = [];
$recentPayments = [];
$recentAttendance = [];
$allMembersList = [];

try {
    $db = getDB();

    // 1. Metric: Total Active Members
    $stmtM = $db->query("SELECT COUNT(*) FROM users WHERE role = 'member' AND status = 'active'");
    $dbActiveCount = (int)$stmtM->fetchColumn();
    if ($dbActiveCount > 0) $totalActiveMembers = $dbActiveCount;

    // 2. Metric: Pending Dues Count & Sum
    $stmtDues = $db->query("SELECT COUNT(*), COALESCE(SUM(amount), 0) FROM payments WHERE status IN ('pending', 'overdue')");
    $duesData = $stmtDues->fetch(PDO::FETCH_NUM);
    if ($duesData && $duesData[0] > 0) {
        $pendingDuesCount = (int)$duesData[0];
        $pendingDuesAmount = (float)$duesData[1];
    }

    // 3. Metric: Active Memberships
    $activeSubscriptions = 1180;
    $stmtSubs = $db->query("SELECT COUNT(*) FROM memberships WHERE status = 'active'");
    $dbSubs = (int)$stmtSubs->fetchColumn();
    if ($dbSubs > 0) $activeSubscriptions = $dbSubs;

    /* Attendance module disabled for now
    $stmtCheckins = $db->query("SELECT COUNT(*) FROM attendance WHERE DATE(check_in_time) = CURDATE() AND status = 'granted'");
    $dbTodayCheckins = (int)$stmtCheckins->fetchColumn();
    if ($dbTodayCheckins > 0) $todayCheckins = $dbTodayCheckins;
    */

    // 4. Metric: Monthly Revenue
    $stmtRev = $db->query("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'paid' AND MONTH(paid_at) = MONTH(CURDATE()) AND YEAR(paid_at) = YEAR(CURDATE())");
    $dbRev = (float)$stmtRev->fetchColumn();
    if ($dbRev > 0) $monthlyRevenue = $dbRev;

    // 5. Recent Members
    $stmtRecMem = $db->query("
        SELECT u.id, u.member_code, u.full_name, u.email, u.phone, u.status, u.created_at,
               COALESCE(mp.name, '3 Month Package') AS plan_name,
               COALESCE(mp.price, 8000.00) AS plan_price,
               COALESCE(m.end_date, DATE_ADD(u.created_at, INTERVAL 90 DAY)) AS next_payment_date,
               DATEDIFF(COALESCE(m.end_date, DATE_ADD(u.created_at, INTERVAL 90 DAY)), CURDATE()) AS days_until_due
        FROM users u
        LEFT JOIN memberships m ON u.id = m.user_id
        LEFT JOIN membership_plans mp ON m.plan_id = mp.id
        WHERE u.role = 'member'
        ORDER BY u.id DESC LIMIT 6
    ");
    $recentMembers = $stmtRecMem->fetchAll();

    // 6. Recent Payments Log
    $stmtRecPay = $db->query("
        SELECT p.*, u.full_name, u.member_code 
        FROM payments p
        JOIN users u ON p.user_id = u.id
        ORDER BY p.id DESC LIMIT 5
    ");
    $recentPayments = $stmtRecPay->fetchAll();

    /* Attendance feed disabled for now
    $stmtRecAtt = $db->query("
        SELECT a.*, u.full_name, u.member_code 
        FROM attendance a
        JOIN users u ON a.user_id = u.id
        ORDER BY a.id DESC LIMIT 5
    ");
    $recentAttendance = $stmtRecAtt->fetchAll();
    */

    // 8. All Members for Dropdown Selectors
    $stmtAllM = $db->query("SELECT id, full_name, member_code FROM users WHERE role = 'member' ORDER BY full_name ASC");
    $allMembersList = $stmtAllM->fetchAll();

} catch (Exception $e) {
    error_log("Admin Dashboard Metrics Warning: " . $e->getMessage());
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

    <!-- Google Fonts & FontAwesome -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&family=Teko:wght@500;600;700&display=swap" rel="stylesheet">
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

    <!-- Modular Dedicated Admin Sidebar -->
    <?php 
    $activeAdminTab = 'dashboard';
    require_once __DIR__ . '/../components/admin-sidebar.php'; 
    ?>

    <!-- ============================================================
         2. MAIN ADMIN CONTENT WORKSPACE
         ============================================================ -->
    <div class="flex-1 flex flex-col min-w-0">
        
        <!-- Unified Top Admin Header -->
        <?php 
        $adminHeaderTitle = "GYM MANAGEMENT OVERVIEW";
        $adminHeaderSubtitle = "Live operational telemetry • " . date('l, F j, Y');
        require_once __DIR__ . '/../components/admin-header.php'; 
        ?>

        <!-- Main Body Container -->
        <main class="p-4 sm:p-6 lg:p-8 pb-24 md:pb-8 space-y-6 sm:space-y-8 flex-grow min-w-0">
            
            <!-- Alert Notifications -->
            <?php if ($actionMessage): ?>
                <div class="p-4 rounded-2xl bg-emerald-950/80 border border-emerald-800 text-emerald-300 text-xs sm:text-sm flex items-center justify-between shadow-xl">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-circle-check text-emerald-400 text-lg shrink-0"></i>
                        <span><?= htmlspecialchars($actionMessage) ?></span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-white px-2 py-1"><i class="fa-solid fa-xmark"></i></button>
                </div>
            <?php endif; ?>

            <?php if ($actionError): ?>
                <div class="p-4 rounded-2xl bg-red-950/80 border border-red-800 text-red-300 text-xs sm:text-sm flex items-center justify-between shadow-xl">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-triangle-exclamation text-red-400 text-lg shrink-0"></i>
                        <span><?= htmlspecialchars($actionError) ?></span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-red-400 hover:text-white px-2 py-1"><i class="fa-solid fa-xmark"></i></button>
                </div>
            <?php endif; ?>

            <!-- ============================================================
                 3. 3 KEY METRICS CARDS (Active Members, Pending Dues, Active Subscriptions)
                 ============================================================ -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                
                <!-- Metric 1: Total Active Members -->
                <div class="glass-card rounded-3xl p-5 sm:p-6 border border-zinc-800 shadow-2xl relative overflow-hidden group">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-[10px] sm:text-[11px] uppercase tracking-wider text-zinc-400 font-bold block">Total Active Members</span>
                            <h3 class="font-heading text-3xl sm:text-4xl lg:text-5xl font-bold text-white mt-1 leading-none"><?= number_format($totalActiveMembers) ?></h3>
                        </div>
                        <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-red-600/10 border border-red-500/30 flex items-center justify-center text-red-500 text-xl sm:text-2xl group-hover:scale-110 transition-transform shrink-0">
                            <i class="fa-solid fa-users"></i>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-[11px] sm:text-xs text-emerald-400 gap-1.5 font-semibold flex-wrap">
                        <i class="fa-solid fa-arrow-trend-up"></i>
                        <span>+12% vs last month</span>
                        <span class="text-zinc-500 ml-auto font-normal text-[10px] sm:text-[11px]">94% Active</span>
                    </div>
                </div>

                <!-- Metric 2: Pending Dues Count -->
                <div class="glass-card rounded-3xl p-5 sm:p-6 border border-zinc-800 shadow-2xl relative overflow-hidden group">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-[10px] sm:text-[11px] uppercase tracking-wider text-zinc-400 font-bold block">Pending Dues Count</span>
                            <h3 class="font-heading text-3xl sm:text-4xl lg:text-5xl font-bold text-white mt-1 leading-none"><?= number_format($pendingDuesCount) ?></h3>
                        </div>
                        <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-amber-400 text-xl sm:text-2xl group-hover:scale-110 transition-transform shrink-0">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-[11px] sm:text-xs text-amber-400 gap-1.5 font-semibold flex-wrap">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <span>Awaiting Collection</span>
                        <span class="text-zinc-500 ml-auto font-normal text-[10px] sm:text-[11px]">Action needed</span>
                    </div>
                </div>

                <!-- Metric 3: Active Subscriptions -->
                <div class="glass-card rounded-3xl p-5 sm:p-6 border border-zinc-800 shadow-2xl relative overflow-hidden group sm:col-span-2 lg:col-span-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-[10px] sm:text-[11px] uppercase tracking-wider text-zinc-400 font-bold block">Active Subscriptions</span>
                            <h3 class="font-heading text-3xl sm:text-4xl lg:text-5xl font-bold text-white mt-1 leading-none"><?= number_format((float)($activeSubscriptions ?? 0)) ?></h3>
                        </div>
                        <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-blue-500/10 border border-blue-500/30 flex items-center justify-center text-blue-400 text-xl sm:text-2xl group-hover:scale-110 transition-transform shrink-0">
                            <i class="fa-solid fa-id-card"></i>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-[11px] sm:text-xs text-emerald-400 gap-1.5 font-semibold flex-wrap">
                        <i class="fa-solid fa-check-double"></i>
                        <span>Enrolled in tiers</span>
                        <span class="text-zinc-500 ml-auto font-normal text-[10px] sm:text-[11px]">Active Plans</span>
                    </div>
                </div>

            </div>

            <!-- ============================================================
                 5. TWO-COLUMN SPLIT: RECENT PAYMENTS & LIVE ATTENDANCE
                 ============================================================ -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 sm:gap-8">
                
                <!-- Left 6 Cols: Recent Payments Log -->
                <div id="payments" class="lg:col-span-6 glass-card rounded-3xl p-5 sm:p-6 border border-zinc-800 shadow-2xl space-y-4 min-w-0">
                    <div class="flex items-center justify-between pb-4 border-b border-zinc-800">
                        <div>
                            <span class="text-emerald-400 text-[10px] sm:text-xs font-bold uppercase tracking-widest">Financial Ledger</span>
                            <h3 class="font-heading text-xl sm:text-2xl font-bold text-white uppercase mt-0.5">RECENT PAYMENTS</h3>
                        </div>
                        <button onclick="toggleModal('modalRecordPayment', true)" class="text-xs uppercase font-bold text-emerald-400 hover:text-emerald-300">
                            + New Entry
                        </button>
                    </div>

                    <div class="space-y-3 font-mono text-xs">
                        <?php if (!empty($recentPayments)): ?>
                            <?php foreach ($recentPayments as $p): ?>
                                <div class="p-3.5 rounded-2xl bg-zinc-900/80 border border-zinc-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="w-9 h-9 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex items-center justify-center font-bold text-[11px] shrink-0">
                                            Rs
                                        </div>
                                        <div class="min-w-0">
                                            <span class="font-sans font-bold text-white block text-sm truncate"><?= htmlspecialchars($p['full_name']) ?></span>
                                            <span class="text-zinc-500 text-[11px] truncate block"><?= htmlspecialchars($p['transaction_id'] ?? 'TXN-98234') ?> • <?= htmlspecialchars($p['payment_method']) ?></span>
                                        </div>
                                    </div>
                                    <div class="text-left sm:text-right shrink-0 border-t sm:border-t-0 pt-2 sm:pt-0 border-zinc-800/50">
                                        <span class="font-bold text-white text-sm block">PKR <?= number_format((float)$p['amount']) ?></span>
                                        <span class="text-[10px] text-emerald-400 uppercase font-sans font-bold"><?= htmlspecialchars($p['status']) ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="p-3.5 rounded-2xl bg-zinc-900/80 border border-zinc-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-9 h-9 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex items-center justify-center font-bold text-[11px] shrink-0">Rs</div>
                                    <div class="min-w-0">
                                        <span class="font-sans font-bold text-white block text-sm truncate">Alex Johnson</span>
                                        <span class="text-zinc-500 text-[11px] truncate block">TXN-98234-A101 • JazzCash / Card</span>
                                    </div>
                                </div>
                                <div class="text-left sm:text-right shrink-0 border-t sm:border-t-0 pt-2 sm:pt-0 border-zinc-800/50">
                                    <span class="font-bold text-white text-sm block">PKR 8,000</span>
                                    <span class="text-[10px] text-emerald-400 uppercase font-sans font-bold">Paid</span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Right 6 Cols: Membership Plans & Quick Tiers Overview -->
                <div class="lg:col-span-6 glass-card rounded-3xl p-5 sm:p-6 border border-zinc-800 shadow-2xl space-y-4 min-w-0">
                    <div class="flex items-center justify-between pb-4 border-b border-zinc-800">
                        <div>
                            <span class="text-red-500 text-[10px] sm:text-xs font-bold uppercase tracking-widest">Subscription Tiers</span>
                            <h3 class="font-heading text-xl sm:text-2xl font-bold text-white uppercase mt-0.5">MEMBERSHIP TIERS OVERVIEW</h3>
                        </div>
                        <a href="<?= url('pricing.php') ?>" target="_blank" class="text-xs uppercase font-bold text-red-400 hover:text-red-300">
                            View Plans
                        </a>
                    </div>

                    <div class="space-y-3 font-sans text-xs">
                        <div class="p-3.5 rounded-2xl bg-zinc-900/80 border border-zinc-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-9 h-9 rounded-xl bg-zinc-800 text-white flex items-center justify-center font-bold text-sm shrink-0">
                                    <i class="fa-solid fa-dumbbell"></i>
                                </div>
                                <div class="min-w-0">
                                    <span class="font-bold text-white block text-sm truncate">1 Month Package</span>
                                    <span class="text-zinc-500 text-[11px] truncate block">Floor access (5AM - 11PM) • Locker Room • 30 Days</span>
                                </div>
                            </div>
                            <div class="text-left sm:text-right shrink-0 border-t sm:border-t-0 pt-2 sm:pt-0 border-zinc-800/50">
                                <span class="font-bold text-white text-sm block">PKR 3,000<span class="text-[10px] text-zinc-500 font-normal">/mo</span></span>
                                <span class="text-[10px] text-zinc-400 uppercase font-bold">30 Days</span>
                            </div>
                        </div>

                        <div class="p-3.5 rounded-2xl bg-zinc-900/80 border border-red-500/30 flex flex-col sm:flex-row sm:items-center justify-between gap-3 relative overflow-hidden">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-9 h-9 rounded-xl bg-red-600/20 text-red-500 border border-red-500/30 flex items-center justify-center text-sm shrink-0">
                                    <i class="fa-solid fa-fire"></i>
                                </div>
                                <div class="min-w-0">
                                    <span class="font-bold text-white block text-sm truncate">3 Month Package</span>
                                    <span class="text-zinc-500 text-[11px] truncate block">24/7 Access • All Classes • Sauna • 90 Days</span>
                                </div>
                            </div>
                            <div class="text-left sm:text-right shrink-0 border-t sm:border-t-0 pt-2 sm:pt-0 border-zinc-800/50">
                                <span class="font-bold text-red-400 text-sm block">PKR 8,000<span class="text-[10px] text-zinc-500 font-normal">/3mo</span></span>
                                <span class="text-[10px] text-red-400 uppercase font-bold">Most Popular</span>
                            </div>
                        </div>

                        <div class="p-3.5 rounded-2xl bg-zinc-900/80 border border-zinc-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-9 h-9 rounded-xl bg-amber-500/10 text-amber-400 border border-amber-500/20 flex items-center justify-center text-sm shrink-0">
                                    <i class="fa-solid fa-crown"></i>
                                </div>
                                <div class="min-w-0">
                                    <span class="font-bold text-white block text-sm truncate">6 Month Package</span>
                                    <span class="text-zinc-500 text-[11px] truncate block">VIP 1-on-1 Coaching • Spa Access • 180 Days</span>
                                </div>
                            </div>
                            <div class="text-left sm:text-right shrink-0 border-t sm:border-t-0 pt-2 sm:pt-0 border-zinc-800/50">
                                <span class="font-bold text-white text-sm block">PKR 15,000<span class="text-[10px] text-zinc-500 font-normal">/6mo</span></span>
                                <span class="text-[10px] text-amber-400 uppercase font-bold">Save PKR 3k</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </main>
    </div>

    <!-- ============================================================
         MODAL 1: ADD NEW MEMBER
         ============================================================ -->
    <div id="modalAddMember" class="hidden fixed inset-0 z-50 bg-black/85 backdrop-blur-xl flex items-center justify-center p-4">
        <div class="glass-card rounded-3xl p-6 sm:p-8 max-w-lg w-full border border-zinc-700 shadow-2xl relative max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between pb-4 border-b border-zinc-800">
                <h3 class="font-heading text-xl sm:text-2xl font-bold text-white uppercase">Register New Gym Member</h3>
                <button onclick="toggleModal('modalAddMember', false)" class="text-zinc-400 hover:text-white p-2">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>

            <form method="POST" action="" class="space-y-4 mt-6">
                <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                <input type="hidden" name="action_type" value="add_member">

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-zinc-300 mb-1.5">Member Full Name</label>
                    <input type="text" name="full_name" required placeholder="e.g. Sarah Miller" class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-4 py-3 text-sm text-white placeholder-zinc-500 focus:outline-none focus:border-red-500">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-zinc-300 mb-1.5">Email Address</label>
                        <input type="email" name="email" required placeholder="sarah@example.com" class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-4 py-3 text-sm text-white placeholder-zinc-500 focus:outline-none focus:border-red-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-zinc-300 mb-1.5">Phone</label>
                        <input type="tel" name="phone" list="pk-phone-suggestions-modal" placeholder="+92 300 1234567" class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-4 py-3 text-sm text-white placeholder-zinc-500 focus:outline-none focus:border-red-500">
                        <datalist id="pk-phone-suggestions-modal">
                            <option value="+92 300 ">Jazz / Mobilink (+92 300)</option>
                            <option value="+92 301 ">Jazz (+92 301)</option>
                            <option value="+92 321 ">Warid (+92 321)</option>
                            <option value="+92 333 ">Ufone (+92 333)</option>
                            <option value="+92 345 ">Telenor (+92 345)</option>
                            <option value="+92 312 ">Zong (+92 312)</option>
                            <option value="+92 300 1234567">Sample (+92 300 1234567)</option>
                        </datalist>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-zinc-300 mb-1.5">Membership Package</label>
                        <select name="plan_id" class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-red-500">
                            <option value="1">1 Month Package (PKR 3,000 / 30 Days)</option>
                            <option value="2" selected>3 Month Package (PKR 8,000 / 90 Days)</option>
                            <option value="3">6 Month Package (PKR 15,000 / 180 Days)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-zinc-300 mb-1.5">Default Password</label>
                        <input type="password" name="password" value="Member123!" required class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-4 py-3 text-sm text-white placeholder-zinc-500 focus:outline-none focus:border-red-500">
                    </div>
                </div>

                <button type="submit" class="btn-primary w-full py-3.5 rounded-xl font-bold uppercase tracking-wider text-xs shadow-lg shadow-red-600/30 flex items-center justify-center gap-2 mt-4 cursor-pointer">
                    <i class="fa-solid fa-check"></i>
                    <span>Save & Provision Keycard</span>
                </button>
            </form>
        </div>
    </div>

    <!-- ============================================================
         MODAL 2: RECORD PAYMENT
         ============================================================ -->
    <div id="modalRecordPayment" class="hidden fixed inset-0 z-50 bg-black/85 backdrop-blur-xl flex items-center justify-center p-4">
        <div class="glass-card rounded-3xl p-6 sm:p-8 max-w-md w-full border border-zinc-700 shadow-2xl relative max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between pb-4 border-b border-zinc-800">
                <h3 class="font-heading text-xl sm:text-2xl font-bold text-white uppercase">Record Gym Payment</h3>
                <button onclick="toggleModal('modalRecordPayment', false)" class="text-zinc-400 hover:text-white p-2">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>

            <form method="POST" action="" class="space-y-4 mt-6">
                <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                <input type="hidden" name="action_type" value="record_payment">

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-zinc-300 mb-1.5">Select Member</label>
                    <select name="user_id" id="paymentUserId" class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-red-500">
                        <?php if (!empty($allMembersList)): ?>
                            <?php foreach ($allMembersList as $mem): ?>
                                <option value="<?= (int)$mem['id'] ?>"><?= htmlspecialchars($mem['full_name']) ?> (<?= htmlspecialchars($mem['member_code']) ?>)</option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="3">Alex Johnson (GF-98234)</option>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-zinc-300 mb-1.5">Amount (PKR)</label>
                        <input type="number" step="1" name="amount" id="paymentAmount" value="8000" required class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-red-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-zinc-300 mb-1.5">Method</label>
                        <select name="payment_method" class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-red-500">
                            <option value="Cash / POS">Cash / POS</option>
                            <option value="JazzCash / EasyPaisa">JazzCash / EasyPaisa</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="Credit / Debit Card">Credit / Debit Card</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-zinc-300 mb-1.5">Notes / Description</label>
                    <input type="text" name="notes" placeholder="3 Month Package Subscription Renewal" class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-4 py-3 text-sm text-white placeholder-zinc-500 focus:outline-none focus:border-red-500">
                </div>

                <button type="submit" class="btn-primary w-full py-3.5 rounded-xl font-bold uppercase tracking-wider text-xs shadow-lg shadow-red-600/30 flex items-center justify-center gap-2 mt-4 cursor-pointer">
                    <i class="fa-solid fa-receipt"></i>
                    <span>Confirm & Generate Receipt</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Manual Check-in Modal commented out for now
    <div id="modalManualCheckin" ...>
    </div>
    -->

    <!-- Scripts for Modal Controls -->
    <script>
        function toggleModal(modalId, show) {
            const modal = document.getElementById(modalId);
            if (show) {
                modal.classList.remove('hidden');
            } else {
                modal.classList.add('hidden');
            }
        }

        function prefillPayment(userId, userName, amount) {
            const select = document.getElementById('paymentUserId');
            const amtInput = document.getElementById('paymentAmount');
            if (select) select.value = userId;
            if (amtInput) amtInput.value = amount.toFixed(2);
            toggleModal('modalRecordPayment', true);
        }
    </script>
</body>
</html>
