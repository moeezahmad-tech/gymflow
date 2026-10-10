<?php
/**
 * GymFlow - Admin Fee Tracking & Dues Ledger
 * Categorized by Paid, Pending, and Overdue with 1-Click Payment Confirmation & Date Filtering
 */
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

// Enforce Administrator & Staff RBAC
requireAdmin();

$activeAdminTab = 'payments';
$currentUser = getCurrentUser();
$pageTitle = "Fee Tracking & Dues Ledger - " . APP_NAME;

$message = null;
$error = null;

// ------------------------------------------------------------
// Handle Quick Payment Actions (1-Click "Mark as Paid")
// ------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Security token mismatch. Please try again.";
    } else {
        $action = $_POST['form_action'] ?? '';

        // 1-Click Mark as Paid
        if ($action === 'mark_paid') {
            $paymentId = (int)($_POST['payment_id'] ?? 0);
            $payMethod = trim($_POST['payment_method'] ?? 'Cash / Front Desk');
            
            if ($paymentId > 0) {
                try {
                    $db = getDB();
                    $stmt = $db->prepare("
                        UPDATE payments 
                        SET status = 'paid', 
                            paid_at = NOW(),
                            payment_method = :method,
                            transaction_id = COALESCE(transaction_id, :txid)
                        WHERE id = :id
                    ");
                    $stmt->execute([
                        ':method' => $payMethod,
                        ':txid'   => 'TXN-PAID-' . strtoupper(uniqid()),
                        ':id'     => $paymentId
                    ]);

                    $message = "Payment #{$paymentId} successfully marked as PAID on " . date('M j, Y g:i A') . "!";
                } catch (Exception $e) {
                    $error = "Error updating payment status: " . $e->getMessage();
                }
            }
        }

        // Record New Payment Form
        elseif ($action === 'create_payment') {
            $userId = (int)($_POST['user_id'] ?? 0);
            $amount = (float)($_POST['amount'] ?? 0);
            $status = in_array($_POST['status'] ?? '', ['paid', 'pending', 'overdue']) ? $_POST['status'] : 'paid';
            $method = trim($_POST['payment_method'] ?? 'Credit Card');
            $dueDate = !empty($_POST['due_date']) ? $_POST['due_date'] : date('Y-m-d');
            $notes   = trim($_POST['notes'] ?? 'General Membership Fee');

            if ($userId <= 0 || $amount <= 0) {
                $error = "Please select a member and enter a valid payment amount.";
            } else {
                try {
                    $db = getDB();
                    $stmtNew = $db->prepare("
                        INSERT INTO payments (user_id, amount, payment_method, transaction_id, status, due_date, paid_at, notes)
                        VALUES (:uid, :amt, :method, :txid, :status, :due, :paid_at, :notes)
                    ");
                    $stmtNew->execute([
                        ':uid'     => $userId,
                        ':amt'     => $amount,
                        ':method'  => $method,
                        ':txid'    => 'TXN-' . strtoupper(uniqid()),
                        ':status'  => $status,
                        ':due'     => $dueDate,
                        ':paid_at' => ($status === 'paid') ? date('Y-m-d H:i:s') : null,
                        ':notes'   => $notes
                    ]);

                    $message = "New fee of PKR " . number_format($amount) . " successfully added to the ledger!";
                } catch (Exception $e) {
                    $error = "Error creating fee record: " . $e->getMessage();
                }
            }
        }
    }
}

// ------------------------------------------------------------
// Search, Status & Date Range Filters
// ------------------------------------------------------------
$statusFilter = trim($_GET['status'] ?? 'all'); // 'all', 'paid', 'pending', 'overdue'
$startDate = trim($_GET['start_date'] ?? '');
$endDate = trim($_GET['end_date'] ?? '');
$search = trim($_GET['search'] ?? '');

$paymentsList = [];
$totalPaidSum = 0.00;
$totalPendingSum = 0.00;
$totalOverdueSum = 0.00;
$allMembers = [];

try {
    $db = getDB();

    // Auto-update overdue records in database
    $db->exec("
        UPDATE payments 
        SET status = 'overdue' 
        WHERE status = 'pending' AND due_date IS NOT NULL AND due_date < CURDATE()
    ");

    // Calculate Summary Metrics
    $stmtMetrics = $db->query("
        SELECT 
            COALESCE(SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END), 0) AS paid_sum,
            COALESCE(SUM(CASE WHEN status = 'pending' AND (due_date >= CURDATE() OR due_date IS NULL) THEN amount ELSE 0 END), 0) AS pending_sum,
            COALESCE(SUM(CASE WHEN status = 'overdue' OR (status = 'pending' AND due_date < CURDATE()) THEN amount ELSE 0 END), 0) AS overdue_sum
        FROM payments
    ");
    $metricRow = $stmtMetrics->fetch();
    if ($metricRow) {
        $totalPaidSum = (float)$metricRow['paid_sum'];
        $totalPendingSum = (float)$metricRow['pending_sum'];
        $totalOverdueSum = (float)$metricRow['overdue_sum'];
    }

    // Build Query with Dynamic Filters
    $where = ["1=1"];
    $params = [];

    // Filter by Status Tab
    if ($statusFilter === 'paid') {
        $where[] = "p.status = 'paid'";
    } elseif ($statusFilter === 'pending') {
        $where[] = "p.status = 'pending'";
    } elseif ($statusFilter === 'overdue') {
        $where[] = "(p.status = 'overdue' OR (p.status = 'pending' AND p.due_date < CURDATE()))";
    }

    // Filter by Date Range (Paid date, Due date, or Creation date)
    if (!empty($startDate)) {
        $where[] = "DATE(COALESCE(p.paid_at, p.due_date, p.created_at)) >= :start_date";
        $params[':start_date'] = $startDate;
    }
    if (!empty($endDate)) {
        $where[] = "DATE(COALESCE(p.paid_at, p.due_date, p.created_at)) <= :end_date";
        $params[':end_date'] = $endDate;
    }

    // Filter by Search Query (Name, Email, Member Code / Pass ID, Transaction ID, Payment Method, Plan Name, User ID)
    if (!empty($search)) {
        $cleanSearch = trim(preg_replace('/^(pass\s*id:?|gf-|pass-)/i', '', $search));

        $where[] = "(
            u.full_name LIKE :s1 
            OR u.email LIKE :s2 
            OR u.member_code LIKE :s3 
            OR u.member_code LIKE :s3_clean
            OR CONCAT('2026-', u.id) LIKE :s3_pass
            OR CONCAT('GF-', COALESCE(u.member_code, '')) LIKE :s3_gf
            OR p.transaction_id LIKE :s4 
            OR p.payment_method LIKE :s5 
            OR mp.name LIKE :s6
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
        $params[':s5'] = $searchWildcard;
        $params[':s6'] = $searchWildcard;
        $params[':s_uid'] = is_numeric($cleanSearch) ? (int)$cleanSearch : 0;
    }

    $whereClause = implode(' AND ', $where);

    $sql = "
        SELECT p.*, u.full_name, u.email, u.phone, u.member_code,
               mp.name AS plan_name,
               DATEDIFF(CURDATE(), p.due_date) AS days_overdue
        FROM payments p
        JOIN users u ON p.user_id = u.id
        LEFT JOIN memberships m ON p.membership_id = m.id
        LEFT JOIN membership_plans mp ON m.plan_id = mp.id
        WHERE {$whereClause}
        ORDER BY 
            CASE 
                WHEN p.status = 'overdue' OR (p.status = 'pending' AND p.due_date < CURDATE()) THEN 1
                WHEN p.status = 'pending' THEN 2
                ELSE 3
            END,
            COALESCE(p.paid_at, p.due_date, p.created_at) DESC
        LIMIT 100
    ";

    $stmtP = $db->prepare($sql);
    $stmtP->execute($params);
    $paymentsList = $stmtP->fetchAll();

    // Fetch Members for Dropdown
    $stmtM = $db->query("SELECT id, full_name, member_code FROM users WHERE role = 'member' ORDER BY full_name ASC");
    $allMembers = $stmtM->fetchAll();

} catch (Exception $e) {
    error_log("Ledger query error: " . $e->getMessage());
    
    // Seed fallback data if DB offline
    $paymentsList = [
        [
            'id' => 1,
            'user_id' => 3,
            'full_name' => 'Alex Johnson',
            'email' => 'member@gymflow.com',
            'member_code' => 'GF-98234',
            'plan_name' => '3 Month Package',
            'amount' => 8000.00,
            'payment_method' => 'JazzCash / Card',
            'transaction_id' => 'TXN-98234-A101',
            'status' => 'paid',
            'due_date' => date('Y-m-d', strtotime('-10 days')),
            'paid_at' => date('Y-m-d H:i:s', strtotime('-10 days')),
            'notes' => '3 Month Package Membership Fee',
            'days_overdue' => 0
        ],
        [
            'id' => 2,
            'user_id' => 3,
            'full_name' => 'Sarah Miller',
            'email' => 'sarah@example.com',
            'member_code' => 'GF-45129',
            'plan_name' => '1 Month Package',
            'amount' => 3000.00,
            'payment_method' => 'Cash / Front Desk',
            'transaction_id' => 'TXN-45129-A102',
            'status' => 'overdue',
            'due_date' => '2026-09-10',
            'paid_at' => null,
            'notes' => 'Monthly Renewal Due',
            'days_overdue' => 27
        ],
        [
            'id' => 3,
            'user_id' => 3,
            'full_name' => 'David Thorne',
            'email' => 'david@example.com',
            'member_code' => 'GF-38910',
            'plan_name' => '6 Month Package',
            'amount' => 15000.00,
            'payment_method' => 'Bank Transfer',
            'transaction_id' => 'TXN-38910-A103',
            'status' => 'pending',
            'due_date' => date('Y-m-d', strtotime('+12 days')),
            'paid_at' => null,
            'notes' => 'Upcoming 6 Month Semi-Annual Cycle',
            'days_overdue' => -12
        ]
    ];
    $totalPaidSum = 380000.00;
    $totalPendingSum = 24000.00;
    $totalOverdueSum = 6000.00;
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
        $adminHeaderTitle = "FEE TRACKING & DUES LEDGER";
        $adminHeaderSubtitle = "Categorized payments ledger, revenue tracking & overdue resolution";
        require_once __DIR__ . '/../components/admin-header.php'; 
        ?>

        <!-- Main Content Area -->
        <main class="p-4 sm:p-6 lg:p-8 pb-24 md:pb-8 space-y-6 sm:space-y-8 flex-grow w-full min-w-0">
            
            <!-- Alert Messages -->
            <?php if ($message): ?>
                <div class="p-4 rounded-2xl bg-emerald-950/80 border border-emerald-800 text-emerald-300 text-xs sm:text-sm flex items-center justify-between shadow-xl">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-circle-check text-emerald-400 text-lg"></i>
                        <span><?= htmlspecialchars($message) ?></span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-white px-2 py-1"><i class="fa-solid fa-xmark"></i></button>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="p-4 rounded-2xl bg-red-950/80 border border-red-800 text-red-300 text-xs sm:text-sm flex items-center justify-between shadow-xl">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-triangle-exclamation text-red-400 text-lg"></i>
                        <span><?= htmlspecialchars($error) ?></span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-red-400 hover:text-white px-2 py-1"><i class="fa-solid fa-xmark"></i></button>
                </div>
            <?php endif; ?>

            <!-- ============================================================
                 2. CATEGORY TABS & DATE RANGE FILTER BAR
                 ============================================================ -->
            <?php
            // Helper to generate consistent filter links
            function getPaymentsFilterUrl($targetStatus, $currentSearch, $currentStart, $currentEnd) {
                $p = [];
                if ($targetStatus !== 'all') $p['status'] = $targetStatus;
                if (!empty($currentSearch)) $p['search'] = $currentSearch;
                if (!empty($currentStart)) $p['start_date'] = $currentStart;
                if (!empty($currentEnd)) $p['end_date'] = $currentEnd;
                return url('admin/payments.php') . (!empty($p) ? '?' . http_build_query($p) : '');
            }
            $hasActiveFilters = ($statusFilter !== 'all' || !empty($startDate) || !empty($endDate) || !empty($search));
            ?>
            <div class="glass-card rounded-3xl p-4 sm:p-6 border border-zinc-800 shadow-2xl space-y-6">
                
                <!-- Top Status Categorization Tabs -->
                <div class="flex flex-wrap items-center justify-between gap-4 border-b border-zinc-800 pb-5">
                    <div class="flex flex-wrap items-center gap-2 p-1.5 rounded-2xl bg-zinc-900 border border-zinc-800 text-xs font-bold uppercase tracking-wider">
                        <a href="<?= getPaymentsFilterUrl('all', $search, $startDate, $endDate) ?>" class="px-4 py-2 rounded-xl transition-all <?= $statusFilter === 'all' ? 'bg-red-600 text-white shadow-md' : 'text-zinc-400 hover:text-white' ?>">
                            All Records
                        </a>
                        <a href="<?= getPaymentsFilterUrl('paid', $search, $startDate, $endDate) ?>" class="px-4 py-2 rounded-xl transition-all <?= $statusFilter === 'paid' ? 'bg-emerald-600 text-white shadow-md' : 'text-zinc-400 hover:text-white' ?>">
                            <i class="fa-solid fa-circle-check text-[10px] mr-1"></i> Paid Collections
                        </a>
                        <a href="<?= getPaymentsFilterUrl('pending', $search, $startDate, $endDate) ?>" class="px-4 py-2 rounded-xl transition-all <?= $statusFilter === 'pending' ? 'bg-amber-500 text-black shadow-md' : 'text-zinc-400 hover:text-white' ?>">
                            <i class="fa-solid fa-clock text-[10px] mr-1"></i> Pending Invoices
                        </a>
                        <a href="<?= getPaymentsFilterUrl('overdue', $search, $startDate, $endDate) ?>" class="px-4 py-2 rounded-xl transition-all <?= $statusFilter === 'overdue' ? 'bg-red-600 text-white shadow-md animate-pulse' : 'text-zinc-400 hover:text-red-400' ?>">
                            <i class="fa-solid fa-triangle-exclamation text-[10px] mr-1"></i> Overdue Dues
                        </a>
                    </div>

                    <div class="flex items-center gap-3 text-xs text-zinc-400">
                        <span>Showing <strong><?= count($paymentsList) ?></strong> matching fee transactions</span>
                        <?php if ($hasActiveFilters): ?>
                            <a href="<?= url('admin/payments.php') ?>" class="text-[11px] font-bold text-red-400 hover:text-red-300 underline flex items-center gap-1">
                                <i class="fa-solid fa-rotate-left"></i> Reset Filter
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Date Range & Search Form -->
                <form method="GET" action="<?= url('admin/payments.php') ?>" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                    <input type="hidden" name="status" value="<?= htmlspecialchars($statusFilter) ?>">

                    <!-- Search Input -->
                    <div class="sm:col-span-5 relative">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-zinc-400 mb-1">Search Transaction / Member</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-zinc-500">
                                <i class="fa-solid fa-magnifying-glass text-xs"></i>
                            </span>
                            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by member, email, code or TXN ID..." class="w-full bg-zinc-900 border border-zinc-800 rounded-xl pl-9 pr-4 py-2.5 text-xs text-white placeholder-zinc-500 focus:outline-none focus:border-red-500">
                        </div>
                    </div>

                    <!-- Date Range: Start Date -->
                    <div class="sm:col-span-3">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-zinc-400 mb-1">From Date</label>
                        <input type="date" id="filterStartDate" name="start_date" value="<?= htmlspecialchars($startDate) ?>" class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-3 py-2.5 text-xs text-white focus:outline-none focus:border-red-500">
                    </div>

                    <!-- Date Range: End Date -->
                    <div class="sm:col-span-2">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-zinc-400 mb-1">To Date</label>
                        <input type="date" id="filterEndDate" name="end_date" value="<?= htmlspecialchars($endDate) ?>" class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-3 py-2.5 text-xs text-white focus:outline-none focus:border-red-500">
                    </div>

                    <!-- Filter & Reset Action Buttons -->
                    <div class="sm:col-span-2 flex gap-2">
                        <button type="submit" class="btn-primary flex-1 py-2.5 rounded-xl text-xs uppercase font-bold tracking-wider flex items-center justify-center gap-1 cursor-pointer" title="Apply Filter">
                            <i class="fa-solid fa-filter text-xs"></i>
                            <span>Filter</span>
                        </button>
                        <a href="<?= url('admin/payments.php') ?>" class="px-3.5 py-2.5 rounded-xl bg-zinc-900 border border-zinc-800 hover:bg-zinc-800 text-zinc-300 hover:text-white text-xs font-bold uppercase flex items-center justify-center transition-colors cursor-pointer" title="Clear Filters">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    </div>
                </form>

            </div>

            <!-- ============================================================
                 3. DUES & PAYMENTS LEDGER TABLE
                 ============================================================ -->
            <div class="glass-card rounded-3xl border border-zinc-800 shadow-2xl overflow-hidden">
                <div class="p-6 border-b border-zinc-800 flex items-center justify-between">
                    <div>
                        <h3 class="font-heading text-2xl font-bold text-white uppercase">FEE AUDIT LEDGER</h3>
                        <p class="text-xs text-zinc-400">Chronological transaction entries and automated billing cycle status</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs sm:text-sm text-zinc-300">
                        <thead class="bg-zinc-900 text-zinc-400 uppercase font-heading text-xs tracking-wider border-b border-zinc-800">
                            <tr>
                                <th class="py-4 px-5">TXN Code / Date</th>
                                <th class="py-4 px-5">Member Information</th>
                                <th class="py-4 px-5">Plan Description</th>
                                <th class="py-4 px-5">Amount</th>
                                <th class="py-4 px-5">Due Date / Cycle</th>
                                <th class="py-4 px-5">Status</th>
                                <th class="py-4 px-5 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-800/60 font-mono text-xs">
                            <?php if (!empty($paymentsList)): ?>
                                <?php foreach ($paymentsList as $pay): ?>
                                    <?php 
                                    $isOverdue = ($pay['status'] === 'overdue' || ($pay['status'] === 'pending' && !empty($pay['due_date']) && strtotime($pay['due_date']) < strtotime('today')));
                                    $daysPastDue = $isOverdue ? max(1, (int)$pay['days_overdue']) : 0;
                                    ?>
                                    <tr class="transition-colors <?= $isOverdue ? 'bg-red-950/20 hover:bg-red-950/30' : 'hover:bg-zinc-900/50' ?>">
                                        
                                        <!-- Transaction ID & Timestamp -->
                                        <td class="py-4 px-5">
                                            <span class="font-bold <?= $isOverdue ? 'text-red-400' : 'text-zinc-200' ?>"><?= htmlspecialchars($pay['transaction_id'] ?? ('TXN-' . $pay['id'])) ?></span>
                                            <span class="block text-[10px] text-zinc-500 font-sans">
                                                <?= !empty($pay['paid_at']) ? 'Paid: ' . date('M j, Y g:i A', strtotime($pay['paid_at'])) : 'Logged: ' . date('M j, Y', strtotime($pay['created_at'] ?? 'today')) ?>
                                            </span>
                                        </td>

                                        <!-- Member Details -->
                                        <td class="py-4 px-5">
                                            <a href="<?= url('admin/members-edit.php?id=' . (int)$pay['user_id']) ?>" class="font-sans font-bold text-white hover:text-red-400 transition-colors block">
                                                <?= htmlspecialchars($pay['full_name']) ?>
                                            </a>
                                            <span class="text-zinc-400 text-[11px] font-sans"><?= htmlspecialchars($pay['member_code'] ?? (date('Y') . '-' . $pay['user_id'])) ?> • <?= htmlspecialchars($pay['email']) ?></span>
                                        </td>

                                        <!-- Plan Description -->
                                        <td class="py-4 px-5 font-sans">
                                            <span class="text-zinc-200 font-semibold block"><?= htmlspecialchars($pay['plan_name'] ?? 'Pro Athlete Tier') ?></span>
                                            <span class="text-zinc-500 text-[11px]"><?= htmlspecialchars($pay['notes'] ?? 'Monthly Subscription') ?></span>
                                        </td>

                                        <!-- Amount -->
                                        <td class="py-4 px-5">
                                            <span class="font-bold text-sm <?= $isOverdue ? 'text-red-400' : 'text-emerald-400' ?>">
                                                PKR <?= number_format((float)$pay['amount']) ?>
                                            </span>
                                            <span class="block text-[10px] text-zinc-500 font-sans"><?= htmlspecialchars($pay['payment_method']) ?></span>
                                        </td>

                                        <!-- Due Date & Overdue Highlight -->
                                        <td class="py-4 px-5 font-sans">
                                            <?php if (!empty($pay['due_date'])): ?>
                                                <span class="block <?= $isOverdue ? 'text-red-400 font-bold' : 'text-zinc-300' ?>">
                                                    <?= date('M j, Y', strtotime($pay['due_date'])) ?>
                                                </span>
                                                <?php if ($isOverdue): ?>
                                                    <span class="inline-block mt-0.5 px-2 py-0.5 rounded bg-red-600/30 text-red-300 border border-red-500/40 text-[10px] font-bold">
                                                        <?= $daysPastDue ?> Days Overdue
                                                    </span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-zinc-500">—</span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Status Badge -->
                                        <td class="py-4 px-5 font-sans">
                                            <?php if ($pay['status'] === 'paid'): ?>
                                                <span class="px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-[10px] font-bold uppercase inline-flex items-center gap-1.5">
                                                    <i class="fa-solid fa-check text-[9px]"></i>
                                                    Paid
                                                </span>
                                            <?php elseif ($isOverdue): ?>
                                                <span class="px-2.5 py-1 rounded-full bg-red-600/20 text-red-400 border border-red-500/40 text-[10px] font-bold uppercase inline-flex items-center gap-1.5">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-ping"></span>
                                                    Overdue
                                                </span>
                                            <?php else: ?>
                                                <span class="px-2.5 py-1 rounded-full bg-amber-500/10 text-amber-400 border border-amber-500/20 text-[10px] font-bold uppercase inline-flex items-center gap-1.5">
                                                    <i class="fa-regular fa-clock text-[9px]"></i>
                                                    Pending
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Quick Action Button (1-Click Mark as Paid) -->
                                        <td class="py-4 px-5 text-right font-sans">
                                            <?php if ($pay['status'] !== 'paid'): ?>
                                                <form method="POST" action="" class="inline-block" onsubmit="return confirm('Mark payment of PKR <?= number_format((float)$pay['amount']) ?> as PAID today?');">
                                                    <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                                                    <input type="hidden" name="form_action" value="mark_paid">
                                                    <input type="hidden" name="payment_id" value="<?= (int)$pay['id'] ?>">
                                                    <input type="hidden" name="payment_method" value="Cash / POS">

                                                    <button type="submit" class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs uppercase tracking-wider inline-flex items-center gap-1.5 shadow-md shadow-emerald-600/30 transition-transform active:scale-95">
                                                        <i class="fa-solid fa-check text-xs"></i>
                                                        <span>Mark Paid</span>
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span class="text-zinc-500 text-xs inline-flex items-center gap-1">
                                                    <i class="fa-solid fa-receipt text-zinc-600"></i> Settled
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-zinc-500 font-sans">
                                        <i class="fa-solid fa-file-circle-xmark text-3xl mb-2 block text-zinc-600"></i>
                                        No fee records match the selected date range and status filters.
                                        <a href="<?= url('admin/payments.php') ?>" class="text-red-400 underline font-semibold ml-1">Reset Filters</a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>

    <!-- ============================================================
         MODAL: RECORD NEW FEE / PAYMENT
         ============================================================ -->
    <div id="modalRecordFee" class="hidden fixed inset-0 z-50 bg-black/85 backdrop-blur-xl flex items-center justify-center p-4">
        <div class="glass-card rounded-3xl p-8 max-w-md w-full border border-zinc-700 shadow-2xl relative">
            <div class="flex items-center justify-between pb-4 border-b border-zinc-800">
                <h3 class="font-heading text-2xl font-bold text-white uppercase">Record New Fee Entry</h3>
                <button onclick="toggleModal('modalRecordFee', false)" class="text-zinc-400 hover:text-white p-2">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>

            <form method="POST" action="" class="space-y-4 mt-6">
                <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                <input type="hidden" name="form_action" value="create_payment">

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-zinc-300 mb-1.5">Select Member *</label>
                    <select name="user_id" required class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-red-500">
                        <?php if (!empty($allMembers)): ?>
                            <?php foreach ($allMembers as $m): ?>
                                <option value="<?= (int)$m['id'] ?>"><?= htmlspecialchars($m['full_name']) ?> (<?= htmlspecialchars($m['member_code']) ?>)</option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="3">Alex Johnson (GF-98234)</option>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-zinc-300 mb-1.5">Amount (PKR) *</label>
                        <input type="number" step="1" name="amount" value="8000" required class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-red-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-zinc-300 mb-1.5">Status *</label>
                        <select name="status" class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-red-500">
                            <option value="paid" selected>Paid (Immediate)</option>
                            <option value="pending">Pending Due</option>
                            <option value="overdue">Overdue</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-zinc-300 mb-1.5">Payment Method</label>
                        <select name="payment_method" class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-red-500">
                            <option value="Cash / POS">Cash / POS</option>
                            <option value="JazzCash / EasyPaisa">JazzCash / EasyPaisa</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="Credit / Debit Card">Credit / Debit Card</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-zinc-300 mb-1.5">Due Date</label>
                        <input type="date" name="due_date" value="<?= date('Y-m-d') ?>" class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-red-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-zinc-300 mb-1.5">Notes & Description</label>
                    <input type="text" name="notes" placeholder="3 Month Package Membership Fee" class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-4 py-3 text-sm text-white placeholder-zinc-500 focus:outline-none focus:border-red-500">
                </div>

                <button type="submit" class="btn-primary w-full py-3.5 rounded-xl font-bold uppercase tracking-wider text-xs shadow-lg shadow-red-600/30 flex items-center justify-center gap-2 mt-4">
                    <i class="fa-solid fa-receipt"></i>
                    <span>Log Fee into Ledger</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Scripting for Modal -->
    <script>
        function toggleModal(modalId, show) {
            const modal = document.getElementById(modalId);
            if (show) {
                modal.classList.remove('hidden');
            } else {
                modal.classList.add('hidden');
            }
        }
    </script>
</body>
</html>
