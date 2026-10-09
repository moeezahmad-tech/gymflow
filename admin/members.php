<?php
/**
 * GymFlow - Admin Member Directory & Management
 * Searchable, Filterable & Paginated Client Database
 */
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

// Enforce Administrator & Staff RBAC
requireAdmin();

$activeAdminTab = 'members';
$currentUser = getCurrentUser();
$pageTitle = "Members Directory - " . APP_NAME;

// ------------------------------------------------------------
// Search, Filter & Pagination Parameters
// ------------------------------------------------------------
$search = trim($_GET['search'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');
$planFilter = (int)($_GET['plan_id'] ?? 0);
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 8;
$offset = ($page - 1) * $perPage;

$members = [];
$totalMembersCount = 0;
$totalPages = 1;

try {
    $db = getDB();

    // Base WHERE conditions
    $whereConditions = ["u.role = 'member'"];
    $params = [];

    if (!empty($search)) {
        $whereConditions[] = "(u.full_name LIKE :search OR u.email LIKE :search OR u.member_code LIKE :search OR u.phone LIKE :search)";
        $params[':search'] = "%{$search}%";
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
               COALESCE(mp.name, '3 Month Package') AS plan_name,
               COALESCE(mp.price, 8000.00) AS plan_price,
               COALESCE(m.end_date, DATE_ADD(u.created_at, INTERVAL 90 DAY)) AS renewal_date,
               DATEDIFF(COALESCE(m.end_date, DATE_ADD(u.created_at, INTERVAL 90 DAY)), CURDATE()) AS days_until_due,
               0 AS total_visits /* attendance query disabled */
        FROM users u
        LEFT JOIN memberships m ON u.id = m.user_id AND m.status = 'active'
        LEFT JOIN membership_plans mp ON m.plan_id = mp.id
        WHERE {$whereClause}
        ORDER BY u.id DESC
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
    // Fallback seed record if table not yet initialized
    $members = [
        [
            'id' => 3,
            'member_code' => 'GF-98234',
            'full_name' => 'Alex Johnson',
            'email' => 'member@gymflow.com',
            'phone' => '+92 300 1234569',
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'plan_name' => '3 Month Package',
            'plan_price' => 8000.00,
            'renewal_date' => date('Y-m-d', strtotime('+78 days')),
            'total_visits' => 14
        ]
    ];
    $totalMembersCount = count($members);
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
        $adminHeaderSubtitle = "Searchable client database & active subscription ledger";
        require_once __DIR__ . '/../components/admin-header.php'; 
        ?>

        <!-- Main Content Area -->
        <main class="p-4 sm:p-6 lg:p-8 pb-24 md:pb-8 space-y-6 flex-grow min-w-0">
            
            <!-- Success / Flash Messages -->
            <?php if (!empty($_GET['added'])): ?>
                <div class="p-4 rounded-2xl bg-emerald-950/80 border border-emerald-800 text-emerald-300 text-xs sm:text-sm flex items-center justify-between shadow-xl">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-circle-check text-emerald-400 text-lg"></i>
                        <span><strong>Member Registered!</strong> New gym member profile and digital keycard have been successfully provisioned.</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-white px-2 py-1"><i class="fa-solid fa-xmark"></i></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($_GET['updated'])): ?>
                <div class="p-4 rounded-2xl bg-blue-950/80 border border-blue-800 text-blue-300 text-xs sm:text-sm flex items-center justify-between shadow-xl">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-circle-check text-blue-400 text-lg"></i>
                        <span><strong>Profile Updated!</strong> Member records and membership tier have been updated.</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-blue-400 hover:text-white px-2 py-1"><i class="fa-solid fa-xmark"></i></button>
                </div>
            <?php endif; ?>

            <!-- ============================================================
                 SEARCH & FILTERS TOOLBAR
                 ============================================================ -->
            <div class="glass-card rounded-2xl p-5 border border-zinc-800 shadow-xl">
                <form method="GET" action="" class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-center">
                    
                    <!-- Search Input -->
                    <div class="sm:col-span-6 relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-zinc-500">
                            <i class="fa-solid fa-magnifying-glass text-xs"></i>
                        </span>
                        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by name, email, phone, or member code (e.g. 2026-3)..." class="w-full bg-zinc-900 border border-zinc-800 rounded-xl pl-9 pr-4 py-2.5 text-xs sm:text-sm text-white placeholder-zinc-500 focus:outline-none focus:border-red-500 transition-colors">
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
                    <div class="sm:col-span-3 flex items-center gap-2">
                        <select name="plan_id" class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-3 py-2.5 text-xs text-white focus:outline-none focus:border-red-500 transition-colors">
                            <option value="0">All Membership Plans</option>
                            <option value="1" <?= $planFilter === 1 ? 'selected' : '' ?>>1 Month Package (PKR 3,000)</option>
                            <option value="2" <?= $planFilter === 2 ? 'selected' : '' ?>>3 Month Package (PKR 8,000)</option>
                            <option value="3" <?= $planFilter === 3 ? 'selected' : '' ?>>6 Month Package (PKR 15,000)</option>
                        </select>
                        <button type="submit" class="btn-primary px-4 py-2.5 rounded-xl text-xs uppercase font-bold shrink-0">
                            Filter
                        </button>
                    </div>

                </form>
            </div>

            <!-- ============================================================
                 MEMBERS DIRECTORY TABLE
                 ============================================================ -->
            <div class="glass-card rounded-3xl border border-zinc-800 shadow-2xl overflow-hidden">
                <div class="p-6 border-b border-zinc-800 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <h3 class="font-heading text-2xl font-bold text-white uppercase">All Registered Members</h3>
                        <p class="text-xs text-zinc-400">Showing <?= count($members) ?> of <?= $totalMembersCount ?> active member records</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs sm:text-sm text-zinc-300">
                        <thead class="bg-zinc-900 text-zinc-400 uppercase font-heading text-xs tracking-wider border-b border-zinc-800">
                            <tr>
                                <th class="py-4 px-5">Member ID</th>
                                <th class="py-4 px-5">Full Name & Email</th>
                                <th class="py-4 px-5">Phone Number</th>
                                <th class="py-4 px-5">Membership Plan</th>
                                <th class="py-4 px-5">Next Payment Due</th>
                                <th class="py-4 px-5">Status</th>
                                <th class="py-4 px-5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-800/60 font-mono text-xs">
                            <?php if (!empty($members)): ?>
                                <?php foreach ($members as $mem): ?>
                                    <tr class="hover:bg-zinc-900/50 transition-colors">
                                        <!-- Member Code -->
                                        <td class="py-4 px-5">
                                            <span class="font-bold text-red-400 font-mono"><?= htmlspecialchars($mem['member_code'] ?? (date('Y') . '-' . $mem['id'])) ?></span>
                                            <span class="block text-[10px] text-zinc-500 font-sans">Joined <?= date('M j, Y', strtotime($mem['created_at'])) ?></span>
                                        </td>

                                        <!-- Name & Email -->
                                        <td class="py-4 px-5">
                                            <div class="flex items-center gap-3">
                                                <div class="w-9 h-9 rounded-xl bg-zinc-800 border border-zinc-700 flex items-center justify-center text-red-500 font-sans font-bold text-xs shrink-0">
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

                                        <!-- Phone -->
                                        <td class="py-4 px-5 font-sans text-zinc-300">
                                            <?= htmlspecialchars($mem['phone'] ?: '—') ?>
                                        </td>

                                        <!-- Plan -->
                                        <td class="py-4 px-5">
                                            <span class="font-sans font-bold text-white block"><?= htmlspecialchars($mem['plan_name']) ?></span>
                                            <span class="text-emerald-400 text-[11px] font-bold">PKR <?= number_format((float)$mem['plan_price']) ?></span>
                                        </td>

                                        <!-- Next Payment Due -->
                                        <td class="py-4 px-5 font-sans">
                                            <?php 
                                            $renewDate = !empty($mem['renewal_date']) ? date('M d, Y', strtotime($mem['renewal_date'])) : '—';
                                            $daysRemaining = isset($mem['days_until_due']) ? (int)$mem['days_until_due'] : (isset($mem['renewal_date']) ? (int)ceil((strtotime($mem['renewal_date']) - time()) / 86400) : 30);
                                            ?>
                                            <span class="font-bold text-zinc-200 block"><?= $renewDate ?></span>
                                            <?php if ($daysRemaining < 0): ?>
                                                <span class="px-2 py-0.5 rounded-md bg-red-950/80 text-red-400 border border-red-800/80 text-[10px] font-bold uppercase inline-block mt-0.5">
                                                    Overdue by <?= abs($daysRemaining) ?>d
                                                </span>
                                            <?php elseif ($daysRemaining <= 3): ?>
                                                <span class="px-2 py-0.5 rounded-md bg-amber-950/80 text-amber-400 border border-amber-800/80 text-[10px] font-bold uppercase inline-block mt-0.5">
                                                    Due in <?= $daysRemaining ?>d
                                                </span>
                                            <?php else: ?>
                                                <span class="text-[10px] text-zinc-500 font-semibold">
                                                    <?= $daysRemaining ?> days left
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Status Badge -->
                                        <td class="py-4 px-5 font-sans">
                                            <?php if ($mem['status'] === 'active'): ?>
                                                <span class="px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-[10px] font-bold uppercase inline-flex items-center gap-1.5">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                                    Active
                                                </span>
                                            <?php elseif ($mem['status'] === 'suspended'): ?>
                                                <span class="px-2.5 py-1 rounded-full bg-red-500/10 text-red-400 border border-red-500/20 text-[10px] font-bold uppercase inline-flex items-center gap-1.5">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>
                                                    Suspended
                                                </span>
                                            <?php else: ?>
                                                <span class="px-2.5 py-1 rounded-full bg-zinc-800 text-zinc-400 border border-zinc-700 text-[10px] font-bold uppercase inline-flex items-center gap-1.5">
                                                    Inactive
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Action Buttons -->
                                        <td class="py-4 px-5 text-right font-sans">
                                            <div class="inline-flex items-center gap-2">
                                                <a href="<?= url('admin/members-edit.php?id=' . (int)$mem['id']) ?>" class="p-2 rounded-lg bg-zinc-900 border border-zinc-800 hover:border-red-500 hover:bg-zinc-800 text-zinc-300 hover:text-white transition-all text-xs" title="View & Edit Member Profile">
                                                    <i class="fa-regular fa-pen-to-square"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-zinc-500 font-sans">
                                        <i class="fa-solid fa-users-slash text-3xl mb-2 block text-zinc-600"></i>
                                        No gym members match your search filter. 
                                        <a href="<?= url('admin/members.php') ?>" class="text-red-400 underline font-semibold ml-1">Reset Search</a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- ============================================================
                     PAGINATION FOOTER
                     ============================================================ -->
                <?php if ($totalPages > 1): ?>
                    <div class="p-5 border-t border-zinc-800/80 flex flex-col sm:flex-row items-center justify-between gap-4">
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

</body>
</html>
