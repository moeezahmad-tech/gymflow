<?php
/**
 * GymFlow - View & Edit Member Profile
 * Update profile details, change membership tiers, renew plans, and view history
 */
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

// Enforce Administrator & Staff RBAC
requireAdmin();

$activeAdminTab = 'members';
$currentUser = getCurrentUser();

$memberId = (int)($_GET['id'] ?? 0);
if ($memberId <= 0) {
    header('Location: ' . url('admin/members.php'));
    exit;
}

$message = null;
$error = null;

// Handle Form Submissions
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Security validation error. Please try again.";
    } else {
        $action = $_POST['form_action'] ?? '';

        // Action 1: Update Profile Details
        if ($action === 'update_profile') {
            $fullName = trim($_POST['full_name'] ?? '');
            $email    = trim($_POST['email'] ?? '');
            $phone    = trim($_POST['phone'] ?? '');
            $status   = in_array($_POST['status'] ?? '', ['active', 'inactive', 'suspended']) ? $_POST['status'] : 'active';

            if (empty($fullName) || empty($email)) {
                $error = "Name and Email Address cannot be blank.";
            } else {
                try {
                    $db = getDB();
                    $stmt = $db->prepare("
                        UPDATE users 
                        SET full_name = :name, email = :email, phone = :phone, status = :status
                        WHERE id = :id AND role = 'member'
                    ");
                    $stmt->execute([
                        ':name'   => $fullName,
                        ':email'  => $email,
                        ':phone'  => $phone,
                        ':status' => $status,
                        ':id'     => $memberId
                    ]);

                    $message = "Member profile details successfully updated!";
                } catch (Exception $e) {
                    $error = "Update Error: " . $e->getMessage();
                }
            }
        }

        // Action 2: Update / Renew Membership Plan
        elseif ($action === 'update_membership') {
            $planId       = (int)($_POST['plan_id'] ?? 2);
            $endDate      = $_POST['end_date'] ?? date('Y-m-d', strtotime('+30 days'));
            $memStatus    = in_array($_POST['membership_status'] ?? '', ['active', 'expired', 'cancelled', 'pending']) ? $_POST['membership_status'] : 'active';
            $autoRenew    = !empty($_POST['auto_renew']) ? 1 : 0;
            $extensionDays = (int)($_POST['extension_days'] ?? 0);

            try {
                $db = getDB();

                // If fast extension was clicked
                if ($extensionDays > 0) {
                    // Check if membership exists
                    $stmtCheck = $db->prepare("SELECT id, end_date FROM memberships WHERE user_id = :uid LIMIT 1");
                    $stmtCheck->execute([':uid' => $memberId]);
                    $existingMem = $stmtCheck->fetch();

                    if ($existingMem) {
                        $stmtRenew = $db->prepare("
                            UPDATE memberships 
                            SET end_date = DATE_ADD(GREATEST(COALESCE(end_date, CURDATE()), CURDATE()), INTERVAL :days DAY),
                                status = 'active',
                                plan_id = :pid
                            WHERE user_id = :uid
                        ");
                        $stmtRenew->execute([
                            ':days' => $extensionDays,
                            ':pid'  => $planId,
                            ':uid'  => $memberId
                        ]);
                    } else {
                        $stmtIns = $db->prepare("
                            INSERT INTO memberships (user_id, plan_id, start_date, end_date, status, auto_renew)
                            VALUES (:uid, :pid, CURDATE(), DATE_ADD(CURDATE(), INTERVAL :days DAY), 'active', 1)
                        ");
                        $stmtIns->execute([
                            ':uid'  => $memberId,
                            ':pid'  => $planId,
                            ':days' => $extensionDays
                        ]);
                    }

                    // Determine renewal price
                    $renewPrice = 8000.00;
                    if ($extensionDays == 30) $renewPrice = 3000.00;
                    elseif ($extensionDays == 90) $renewPrice = 8000.00;
                    elseif ($extensionDays == 180) $renewPrice = 15000.00;

                    // Log payment record for extension
                    $stmtPay = $db->prepare("
                        INSERT INTO payments (user_id, amount, payment_method, transaction_id, status, due_date, paid_at, notes)
                        VALUES (:uid, :amt, 'Front Desk Renewal', :txid, 'paid', CURDATE(), NOW(), :notes)
                    ");
                    $stmtPay->execute([
                        ':uid'   => $memberId,
                        ':amt'   => $renewPrice,
                        ':txid'  => 'TXN-RNW-' . strtoupper(uniqid()),
                        ':notes' => "Membership renewed for {$extensionDays} days"
                    ]);

                    $message = "Membership successfully renewed for {$extensionDays} days (PKR " . number_format($renewPrice) . ")!";
                } else {
                    $stmtCheck = $db->prepare("SELECT id FROM memberships WHERE user_id = :uid LIMIT 1");
                    $stmtCheck->execute([':uid' => $memberId]);
                    $existingMemId = $stmtCheck->fetchColumn();

                    if ($existingMemId) {
                        $stmtUpd = $db->prepare("
                            UPDATE memberships 
                            SET plan_id = :pid, end_date = :edate, status = :mstatus, auto_renew = :autorenew
                            WHERE user_id = :uid
                        ");
                        $stmtUpd->execute([
                            ':pid'       => $planId,
                            ':edate'     => $endDate,
                            ':mstatus'   => $memStatus,
                            ':autorenew' => $autoRenew,
                            ':uid'       => $memberId
                        ]);
                    } else {
                        $stmtIns = $db->prepare("
                            INSERT INTO memberships (user_id, plan_id, start_date, end_date, status, auto_renew)
                            VALUES (:uid, :pid, CURDATE(), :edate, :mstatus, :autorenew)
                        ");
                        $stmtIns->execute([
                            ':uid'       => $memberId,
                            ':pid'       => $planId,
                            ':edate'     => $endDate,
                            ':mstatus'   => $memStatus,
                            ':autorenew' => $autoRenew
                        ]);
                    }

                    $message = "Membership plan tier and expiration updated!";
                }
            } catch (Exception $e) {
                $error = "Membership Update Error: " . $e->getMessage();
            }
        }

        // Action 3: Reset Password
        elseif ($action === 'reset_password') {
            $newPassword = $_POST['new_password'] ?? '';
            if (strlen($newPassword) < 6) {
                $error = "New password must be at least 6 characters.";
            } else {
                try {
                    $db = getDB();
                    $stmtPass = $db->prepare("UPDATE users SET password = :pass WHERE id = :id");
                    $stmtPass->execute([
                        ':pass' => password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 10]),
                        ':id'   => $memberId
                    ]);
                    $message = "Member portal password successfully reset!";
                } catch (Exception $e) {
                    $error = "Password Reset Error: " . $e->getMessage();
                }
            }
        }
    }
}

// ------------------------------------------------------------
// Load Member Data & Histories
// ------------------------------------------------------------
$member = [
    'id' => $memberId ?: 3,
    'member_code' => 'GF-98234',
    'full_name' => 'Alex Johnson',
    'email' => 'member@gymflow.com',
    'phone' => '+92 300 1234569',
    'status' => 'active',
    'created_at' => date('Y-m-d H:i:s')
];

$membership = [
    'plan_id' => 2,
    'plan_name' => '3 Month Package',
    'plan_price' => 8000.00,
    'start_date' => date('Y-m-d'),
    'end_date' => date('Y-m-d', strtotime('+90 days')),
    'status' => 'active',
    'auto_renew' => 1
];

$plansList = [
    ['id' => 1, 'name' => '1 Month Package', 'price' => 3000.00, 'duration_days' => 30],
    ['id' => 2, 'name' => '3 Month Package', 'price' => 8000.00, 'duration_days' => 90],
    ['id' => 3, 'name' => '6 Month Package', 'price' => 15000.00, 'duration_days' => 180]
];

$paymentHistory = [];
$attendanceHistory = [];

try {
    $db = getDB();

    // 1. Fetch Member
    $stmtMem = $db->prepare("SELECT * FROM users WHERE id = :id AND role = 'member' LIMIT 1");
    $stmtMem->execute([':id' => $memberId]);
    $dbMember = $stmtMem->fetch();
    if ($dbMember) {
        $member = $dbMember;
    }

    // 2. Fetch Active Membership
    $stmtSub = $db->prepare("
        SELECT m.*, mp.name AS plan_name, mp.price AS plan_price, mp.duration_days AS plan_duration_days
        FROM memberships m
        JOIN membership_plans mp ON m.plan_id = mp.id
        WHERE m.user_id = :uid
        ORDER BY m.id DESC LIMIT 1
    ");
    $stmtSub->execute([':uid' => $memberId]);
    $dbMembership = $stmtSub->fetch();
    if ($dbMembership) {
        $membership = $dbMembership;
    }

    // 3. Fetch Plans
    $stmtPlans = $db->query("SELECT * FROM membership_plans ORDER BY duration_days ASC");
    $dbPlans = $stmtPlans->fetchAll();
    if (!empty($dbPlans)) {
        $plansList = $dbPlans;
    }

    // Determine active plan duration
    $activePlanDuration = (int)($membership['plan_duration_days'] ?? 0);
    if ($activePlanDuration <= 0) {
        foreach ($plansList as $pl) {
            if ((int)$pl['id'] === (int)$membership['plan_id']) {
                $activePlanDuration = (int)$pl['duration_days'];
                break;
            }
        }
    }
    if ($activePlanDuration <= 0) $activePlanDuration = 90;

    // 4. Fetch Payments for this user
    $stmtPay = $db->prepare("SELECT * FROM payments WHERE user_id = :uid ORDER BY id DESC LIMIT 5");
    $stmtPay->execute([':uid' => $memberId]);
    $paymentHistory = $stmtPay->fetchAll();

} catch (Exception $e) {
    error_log("Edit member load error: " . $e->getMessage());
}

$pageTitle = "Edit Member: " . htmlspecialchars($member['full_name'] ?? 'Alex Johnson') . " | " . APP_NAME;
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
        $adminHeaderTitle = "MANAGE MEMBER: " . ($member['full_name'] ?? 'Member Profile');
        $adminHeaderSubtitle = "Member Code: " . ($member['member_code'] ?? (date('Y') . '-' . ($member['id'] ?? '1'))) . " • Plan: " . ($membership['plan_name'] ?? 'Active Membership');
        require_once __DIR__ . '/../components/admin-header.php'; 
        ?>

        <!-- Main Form Content -->
        <main class="p-6 space-y-8 flex-grow w-full">
            
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

            <!-- Member Overview Header Card -->
            <div class="glass-card rounded-3xl p-6 sm:p-8 border border-zinc-800 shadow-2xl relative overflow-hidden bg-gradient-to-r from-zinc-950 via-zinc-900 to-zinc-950">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-red-600 to-red-800 flex items-center justify-center text-white font-bold text-2xl shadow-xl shadow-red-600/30 shrink-0">
                            <?= strtoupper(substr($member['full_name'], 0, 2)) ?>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-mono text-red-400 font-bold text-xs"><?= htmlspecialchars($member['member_code']) ?></span>
                                <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-[10px] font-bold uppercase">
                                    <?= htmlspecialchars($member['status']) ?>
                                </span>
                            </div>
                            <h2 class="font-heading text-3xl font-bold text-white uppercase mt-0.5"><?= htmlspecialchars($member['full_name']) ?></h2>
                            <p class="text-xs text-zinc-400">Joined on <?= date('F j, Y', strtotime($member['created_at'])) ?> • Current Plan: <span class="text-white font-semibold"><?= htmlspecialchars($membership['plan_name']) ?></span></p>
                        </div>
                    </div>

                    <!-- Quick Expiry Badge -->
                    <div class="p-4 rounded-2xl bg-zinc-900 border border-zinc-800 text-center sm:text-right">
                        <span class="text-[10px] uppercase tracking-wider text-zinc-400 font-bold block">Membership Valid Until</span>
                        <span class="font-heading text-2xl font-bold text-emerald-400"><?= date('M j, Y', strtotime($membership['end_date'])) ?></span>
                    </div>
                </div>
            </div>

            <!-- Two Column Form Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                
                <!-- Left 6 Cols: Update Member Details -->
                <div class="lg:col-span-6 glass-card rounded-3xl p-6 sm:p-8 border border-zinc-800 shadow-2xl space-y-6">
                    <div class="border-b border-zinc-800 pb-4">
                        <span class="text-red-500 text-xs font-bold uppercase tracking-widest">Profile Configuration</span>
                        <h3 class="font-heading text-2xl font-bold text-white uppercase">MEMBER DETAILS</h3>
                    </div>

                    <form method="POST" action="" class="space-y-4">
                        <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                        <input type="hidden" name="form_action" value="update_profile">

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-zinc-300 mb-1.5">Full Name</label>
                            <input type="text" name="full_name" value="<?= htmlspecialchars($member['full_name']) ?>" required class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-red-500 transition-colors">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-zinc-300 mb-1.5">Email Address</label>
                            <input type="email" name="email" value="<?= htmlspecialchars($member['email']) ?>" required class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-red-500 transition-colors">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-zinc-300 mb-1.5">Phone Number</label>
                            <input type="tel" name="phone" list="pk-phone-suggestions" value="<?= htmlspecialchars($member['phone'] ?? '') ?>" placeholder="+92 300 1234567" class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-red-500 transition-colors">
                            <datalist id="pk-phone-suggestions">
                                <option value="+92 300 ">Jazz / Mobilink (+92 300)</option>
                                <option value="+92 301 ">Jazz (+92 301)</option>
                                <option value="+92 321 ">Warid (+92 321)</option>
                                <option value="+92 333 ">Ufone (+92 333)</option>
                                <option value="+92 345 ">Telenor (+92 345)</option>
                                <option value="+92 312 ">Zong (+92 312)</option>
                                <option value="+92 300 1234567">Sample (+92 300 1234567)</option>
                            </datalist>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-zinc-300 mb-1.5">Account Access Status</label>
                            <select name="status" class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-red-500 transition-colors">
                                <option value="active" <?= $member['status'] === 'active' ? 'selected' : '' ?>>Active (Full Turnstile Clearance)</option>
                                <option value="inactive" <?= $member['status'] === 'inactive' ? 'selected' : '' ?>>Inactive (No Keycard Access)</option>
                                <option value="suspended" <?= $member['status'] === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                            </select>
                        </div>

                        <button type="submit" class="btn-primary w-full py-3.5 rounded-xl font-bold uppercase tracking-wider text-xs shadow-lg shadow-red-600/30 flex items-center justify-center gap-2 mt-4">
                            <i class="fa-solid fa-floppy-disk"></i>
                            <span>Save Profile Changes</span>
                        </button>
                    </form>
                </div>

                <!-- Right 6 Cols: Membership Plan & Renewal -->
                <div class="lg:col-span-6 glass-card rounded-3xl p-6 sm:p-8 border border-zinc-800 shadow-2xl space-y-6">
                    <div class="border-b border-zinc-800 pb-4">
                        <span class="text-red-500 text-xs font-bold uppercase tracking-widest">Subscription Control</span>
                        <h3 class="font-heading text-2xl font-bold text-white uppercase">RENEW & MANAGE PLAN</h3>
                    </div>

                    <!-- Fast 1-Click Renewal Buttons -->
                    <div class="p-4 rounded-2xl bg-zinc-900 border border-zinc-800 space-y-2">
                        <span class="text-[11px] uppercase font-bold text-zinc-400 block">Fast 1-Click Renewal:</span>
                        <form method="POST" action="" id="fastRenewalForm" class="grid grid-cols-3 gap-2">
                            <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                            <input type="hidden" name="form_action" value="update_membership">
                            <input type="hidden" name="plan_id" id="fastRenewalPlanId" value="<?= (int)$membership['plan_id'] ?>">
                            
                            <button type="submit" name="extension_days" value="30" data-days="30" class="fast-renew-btn py-2.5 rounded-lg text-xs font-bold uppercase tracking-wider transition-all <?= $activePlanDuration == 30 ? 'btn-primary shadow-lg shadow-red-600/30 text-white' : 'bg-zinc-800 hover:bg-zinc-700 text-zinc-300 hover:text-white border border-zinc-700' ?>">
                                + 30 Days (1 Mo)
                            </button>
                            <button type="submit" name="extension_days" value="90" data-days="90" class="fast-renew-btn py-2.5 rounded-lg text-xs font-bold uppercase tracking-wider transition-all <?= $activePlanDuration == 90 ? 'btn-primary shadow-lg shadow-red-600/30 text-white' : 'bg-zinc-800 hover:bg-zinc-700 text-zinc-300 hover:text-white border border-zinc-700' ?>">
                                + 90 Days (3 Mos)
                            </button>
                            <button type="submit" name="extension_days" value="180" data-days="180" class="fast-renew-btn py-2.5 rounded-lg text-xs font-bold uppercase tracking-wider transition-all <?= $activePlanDuration == 180 ? 'btn-primary shadow-lg shadow-red-600/30 text-white' : 'bg-zinc-800 hover:bg-zinc-700 text-zinc-300 hover:text-white border border-zinc-700' ?>">
                                + 180 Days (6 Mos)
                            </button>
                        </form>
                    </div>

                    <!-- Detailed Plan Modification Form -->
                    <form method="POST" action="" class="space-y-4">
                        <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                        <input type="hidden" name="form_action" value="update_membership">

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-zinc-300 mb-1.5">Change Membership Package</label>
                            <select name="plan_id" id="planSelect" onchange="onPlanChange(this)" class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-red-500 transition-colors">
                                <?php if (!empty($plansList)): ?>
                                    <?php foreach ($plansList as $p): 
                                        $pDays = (int)($p['duration_days'] ?? 30);
                                        $isSelected = ((int)$p['id'] === (int)$membership['plan_id']);
                                    ?>
                                        <option value="<?= (int)$p['id'] ?>" data-duration="<?= $pDays ?>" <?= $isSelected ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($p['name']) ?> (PKR <?= number_format((float)$p['price']) ?> / <?= $pDays ?> Days)
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="1" data-duration="30" <?= (int)$membership['plan_id'] === 1 ? 'selected' : '' ?>>1 Month Package (PKR 3,000 / 30 Days)</option>
                                    <option value="2" data-duration="90" <?= (int)$membership['plan_id'] === 2 ? 'selected' : '' ?>>3 Month Package (PKR 8,000 / 90 Days)</option>
                                    <option value="3" data-duration="180" <?= (int)$membership['plan_id'] === 3 ? 'selected' : '' ?>>6 Month Package (PKR 15,000 / 180 Days)</option>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-zinc-300 mb-1.5">Expiration Date</label>
                                <input type="date" name="end_date" id="endDateInput" value="<?= htmlspecialchars($membership['end_date']) ?>" required class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-red-500 transition-colors">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-zinc-300 mb-1.5">Plan Status</label>
                                <select name="membership_status" class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-red-500 transition-colors">
                                    <option value="active" <?= $membership['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                    <option value="expired" <?= $membership['status'] === 'expired' ? 'selected' : '' ?>>Expired</option>
                                    <option value="cancelled" <?= $membership['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 pt-1 text-xs text-zinc-400">
                            <input type="checkbox" name="auto_renew" value="1" <?= !empty($membership['auto_renew']) ? 'checked' : '' ?> class="w-4 h-4 accent-red-600 rounded">
                            <span>Enable Automatic Recurring Monthly Billing</span>
                        </div>

                        <button type="submit" class="btn-outline w-full py-3.5 rounded-xl font-bold uppercase tracking-wider text-xs flex items-center justify-center gap-2 mt-4 hover:border-red-500 hover:text-white transition-colors">
                            <i class="fa-solid fa-arrows-rotate"></i>
                            <span>Update Membership Configuration</span>
                        </button>
                    </form>
                </div>

            </div>

            <!-- Password Reset Card -->
            <div class="glass-card rounded-3xl p-6 sm:p-8 border border-zinc-800 shadow-2xl">
                <div class="border-b border-zinc-800 pb-4 mb-4">
                    <span class="text-red-500 text-xs font-bold uppercase tracking-widest">Security Credentials</span>
                    <h3 class="font-heading text-2xl font-bold text-white uppercase">RESET MEMBER PASSWORD</h3>
                </div>

                <form method="POST" action="" class="flex flex-col sm:flex-row gap-4 items-end">
                    <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                    <input type="hidden" name="form_action" value="reset_password">

                    <div class="flex-grow w-full">
                        <label class="block text-xs font-bold uppercase tracking-wider text-zinc-300 mb-1.5">New Password (Min 6 characters)</label>
                        <input type="password" name="new_password" required minlength="6" placeholder="Enter new strong password" class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-red-500 transition-colors">
                    </div>

                    <button type="submit" class="btn-primary px-6 py-3.5 rounded-xl font-bold uppercase tracking-wider text-xs shrink-0">
                        Reset Password
                    </button>
                </form>
            </div>

        </main>
    </div>

    <!-- Live Dynamic Plan Highlight Synchronization -->
    <script>
        function onPlanChange(selectEl) {
            const selectedPlanId = parseInt(selectEl.value);
            const selectedOption = selectEl.options[selectEl.selectedIndex];
            const duration = parseInt(selectedOption.getAttribute('data-duration') || '30');

            // Sync hidden plan_id in fast 1-click renewal form
            const fastPlanInput = document.getElementById('fastRenewalPlanId');
            if (fastPlanInput) fastPlanInput.value = selectedPlanId;

            // Shift the red button highlight to the newly selected duration
            document.querySelectorAll('.fast-renew-btn').forEach(btn => {
                const btnDays = parseInt(btn.getAttribute('data-days'));
                if (btnDays === duration) {
                    btn.className = 'fast-renew-btn py-2.5 rounded-lg text-xs font-bold uppercase tracking-wider transition-all btn-primary shadow-lg shadow-red-600/30 text-white';
                } else {
                    btn.className = 'fast-renew-btn py-2.5 rounded-lg text-xs font-bold uppercase tracking-wider transition-all bg-zinc-800 hover:bg-zinc-700 text-zinc-300 hover:text-white border border-zinc-700';
                }
            });
        }
    </script>
</body>
</html>
