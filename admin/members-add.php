<?php
/**
 * GymFlow - Admin Add New Member Form
 */
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

// Enforce Administrator & Staff RBAC
requireAdmin();

$activeAdminTab = 'members-add';
$currentUser = getCurrentUser();
$pageTitle = "Add New Member - " . APP_NAME;

$error = null;
$plansList = [];

try {
    $db = getDB();
    $stmtPlans = $db->query("SELECT * FROM membership_plans WHERE status = 'active' ORDER BY price ASC");
    $plansList = $stmtPlans->fetchAll();
} catch (Exception $e) {
    error_log("Plans load error: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Security validation failed. Please submit the form again.";
    } else {
        $fullName = trim($_POST['full_name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $phone    = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? 'Member123!';
        $planId   = (int)($_POST['plan_id'] ?? 2);
        $duration = (int)($_POST['duration_days'] ?? 30);
        $status   = in_array($_POST['status'] ?? '', ['active', 'inactive', 'suspended']) ? $_POST['status'] : 'active';

        if (empty($fullName) || empty($email)) {
            $error = "Member Name and Email Address are required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please enter a valid email address.";
        } else {
            try {
                $db = getDB();

                // Check duplicate email
                $chk = $db->prepare("SELECT id FROM users WHERE LOWER(email) = LOWER(:email) LIMIT 1");
                $chk->execute([':email' => $email]);
                if ($chk->fetch()) {
                    $error = "A member with this email address already exists in the system.";
                } else {
                    $tempCode = date('Y') . '-TEMP-' . rand(100, 999);
                    $hashedPassword = hashPassword($password);

                    $db->beginTransaction();

                    // 1. Insert user
                    $stmtUser = $db->prepare("
                        INSERT INTO users (member_code, full_name, email, phone, password, role, status)
                        VALUES (:code, :name, :email, :phone, :pass, 'member', :status)
                    ");
                    $stmtUser->execute([
                        ':code'   => $tempCode,
                        ':name'   => $fullName,
                        ':email'  => $email,
                        ':phone'  => $phone,
                        ':pass'   => $hashedPassword,
                        ':status' => $status
                    ]);
                    $newUserId = (int)$db->lastInsertId();

                    // Update to smooth member code: Year-ID (e.g. 2026-15)
                    $memberCode = date('Y') . '-' . $newUserId;
                    $db->prepare("UPDATE users SET member_code = :code WHERE id = :id")->execute([':code' => $memberCode, ':id' => $newUserId]);

                    // 2. Insert membership
                    $stmtMem = $db->prepare("
                        INSERT INTO memberships (user_id, plan_id, start_date, end_date, status, auto_renew)
                        VALUES (:uid, :pid, CURDATE(), DATE_ADD(CURDATE(), INTERVAL :days DAY), :mstatus, 1)
                    ");
                    $stmtMem->execute([
                        ':uid'     => $newUserId,
                        ':pid'     => $planId,
                        ':days'    => $duration,
                        ':mstatus' => $status
                    ]);
                    $membershipId = (int)$db->lastInsertId();

                    // 3. Determine plan price
                    $planAmount = 8000.00;
                    if (!empty($plansList)) {
                        foreach ($plansList as $pl) {
                            if ((int)$pl['id'] === $planId) {
                                $planAmount = (float)$pl['price'];
                                break;
                            }
                        }
                    } elseif ($planId === 1) {
                        $planAmount = 3000.00;
                    } elseif ($planId === 3) {
                        $planAmount = 15000.00;
                    }

                    // 4. Insert Initial Paid Record
                    $stmtPay = $db->prepare("
                        INSERT INTO payments (user_id, membership_id, amount, payment_method, transaction_id, status, due_date, paid_at, notes)
                        VALUES (:uid, :mid, :amount, 'Front Desk Activation', :txid, 'paid', CURDATE(), NOW(), 'Initial membership payment on registration')
                    ");
                    $stmtPay->execute([
                        ':uid'    => $newUserId,
                        ':mid'    => $membershipId,
                        ':amount' => $planAmount,
                        ':txid'   => 'TXN-ADM-' . strtoupper(uniqid())
                    ]);

                    $db->commit();

                    header('Location: ' . url('admin/members.php?added=1'));
                    exit;
                }
            } catch (Exception $e) {
                if (isset($db) && $db->inTransaction()) {
                    $db->rollBack();
                }
                $error = "Database Error: " . $e->getMessage();
            }
        }
    }
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
        $adminHeaderTitle = "REGISTER NEW MEMBER";
        $adminHeaderSubtitle = "Assign membership tier, create digital keycard & provision portal access";
        require_once __DIR__ . '/../components/admin-header.php'; 
        ?>

        <!-- Form Workspace -->
        <main class="p-6 w-full space-y-8 flex-grow">
            
            <?php if ($error): ?>
                <div class="p-4 rounded-2xl bg-red-950/80 border border-red-800 text-red-300 text-xs sm:text-sm flex items-center justify-between shadow-xl">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-triangle-exclamation text-red-400 text-lg"></i>
                        <span><?= htmlspecialchars($error) ?></span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-red-400 hover:text-white px-2 py-1"><i class="fa-solid fa-xmark"></i></button>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">

                <!-- Section 1: Member Personal Information -->
                <div class="glass-card rounded-3xl p-6 sm:p-8 border border-zinc-800 shadow-2xl space-y-5">
                    <div class="border-b border-zinc-800 pb-4">
                        <span class="text-red-500 text-xs font-bold uppercase tracking-widest">Step 01</span>
                        <h3 class="font-heading text-2xl font-bold text-white uppercase">Personal & Contact Profile</h3>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-zinc-300 mb-1.5">Full Legal Name *</label>
                            <input type="text" name="full_name" required placeholder="Marcus Vance" class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-4 py-3 text-sm text-white placeholder-zinc-500 focus:outline-none focus:border-red-500 transition-colors">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-zinc-300 mb-1.5">Email Address *</label>
                            <input type="email" name="email" required placeholder="marcus@example.com" class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-4 py-3 text-sm text-white placeholder-zinc-500 focus:outline-none focus:border-red-500 transition-colors">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-zinc-300 mb-1.5">Phone Number</label>
                            <input type="tel" name="phone" list="pk-phone-suggestions" placeholder="+92 300 1234567" class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-4 py-3 text-sm text-white placeholder-zinc-500 focus:outline-none focus:border-red-500 transition-colors">
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
                            <label class="block text-xs font-bold uppercase tracking-wider text-zinc-300 mb-1.5">Portal Access Password</label>
                            <input type="password" name="password" value="Member123!" required class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-4 py-3 text-sm text-white placeholder-zinc-500 focus:outline-none focus:border-red-500 transition-colors">
                        </div>
                    </div>
                </div>

                <!-- Section 2: Membership Assignment -->
                <div class="glass-card rounded-3xl p-6 sm:p-8 border border-zinc-800 shadow-2xl space-y-5">
                    <div class="border-b border-zinc-800 pb-4">
                        <span class="text-red-500 text-xs font-bold uppercase tracking-widest">Step 02</span>
                        <h3 class="font-heading text-2xl font-bold text-white uppercase">Membership Tier & Duration</h3>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-zinc-300 mb-1.5">Membership Package *</label>
                            <select name="plan_id" class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-red-500 transition-colors">
                                <?php if (!empty($plansList)): ?>
                                    <?php foreach ($plansList as $p): ?>
                                        <option value="<?= (int)$p['id'] ?>" <?= (int)$p['id'] === 2 ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($p['name']) ?> (PKR <?= number_format((float)$p['price']) ?> / <?= (int)$p['duration_days'] ?> Days)
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="1">1 Month Package (PKR 3,000 / 30 Days)</option>
                                    <option value="2" selected>3 Month Package (PKR 8,000 / 90 Days)</option>
                                    <option value="3">6 Month Package (PKR 15,000 / 180 Days)</option>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-zinc-300 mb-1.5">Duration</label>
                            <select name="duration_days" class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-red-500 transition-colors">
                                <option value="30" selected>1 Month (30 Days)</option>
                                <option value="90">3 Months (90 Days)</option>
                                <option value="180">6 Months (180 Days)</option>
                                <option value="365">1 Year (365 Days)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-zinc-300 mb-1.5">Initial Account Status</label>
                            <select name="status" class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-red-500 transition-colors">
                                <option value="active" selected>Active (24/7 Access)</option>
                                <option value="inactive">Inactive / Pending</option>
                                <option value="suspended">Suspended</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Submit Action Buttons -->
                <div class="flex items-center gap-4 pt-2">
                    <button type="submit" class="btn-primary text-xs uppercase font-bold tracking-wider px-8 py-4 rounded-xl shadow-lg shadow-red-600/30 flex items-center gap-2">
                        <i class="fa-solid fa-user-check"></i>
                        <span>Register Member & Provision Keycard</span>
                    </button>
                    <a href="<?= url('admin/members.php') ?>" class="btn-outline text-xs uppercase font-bold tracking-wider px-6 py-4 rounded-xl">
                        Cancel
                    </a>
                </div>
            </form>

        </main>
    </div>

</body>
</html>
