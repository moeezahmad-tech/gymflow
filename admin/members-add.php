<?php
/**
 * GymFlow - Admin Add New Member Form (Simplified & Streamlined)
 */
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

// Enforce Administrator & Staff RBAC
requireAdmin();

$activeAdminTab = 'members-add';
$currentUser = getCurrentUser();
$pageTitle = "Register New Member - " . APP_NAME;

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
        $password = !empty($_POST['password']) ? $_POST['password'] : 'Member123!';
        $planId   = (int)($_POST['plan_id'] ?? 1);
        $status   = 'active'; // Default active account with 24/7 access

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
                    // Determine package duration & price automatically from selected plan
                    $planDuration = 30;
                    $planAmount = 3500.00;
                    $planName = 'Membership Package';

                    if (!empty($plansList)) {
                        foreach ($plansList as $pl) {
                            if ((int)$pl['id'] === $planId) {
                                $planDuration = (int)($pl['duration_days'] ?? 30);
                                $planAmount = (float)($pl['price'] ?? 3500.00);
                                $planName = $pl['name'];
                                break;
                            }
                        }
                    }

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

                    // Update to clean member code: Year-ID (e.g. 2026-15)
                    $memberCode = date('Y') . '-' . $newUserId;
                    $db->prepare("UPDATE users SET member_code = :code WHERE id = :id")->execute([':code' => $memberCode, ':id' => $newUserId]);

                    // 2. Insert membership aligned with 10th of every month schedule
                    $months = max(1, (int)round($planDuration / 30));
                    $today = new DateTime();
                    $day = (int)$today->format('j');
                    $dt = clone $today;
                    if ($day >= 10) {
                        $dt->modify("+{$months} month");
                    } else {
                        $dt->modify("+" . max(1, $months) . " month");
                    }
                    $dt->setDate((int)$dt->format('Y'), (int)$dt->format('m'), 10);
                    $initialEndDate = $dt->format('Y-m-d');

                    $stmtMem = $db->prepare("
                        INSERT INTO memberships (user_id, plan_id, start_date, end_date, status, auto_renew)
                        VALUES (:uid, :pid, CURDATE(), :end_date, 'active', 1)
                    ");
                    $stmtMem->execute([
                        ':uid'      => $newUserId,
                        ':pid'      => $planId,
                        ':end_date' => $initialEndDate
                    ]);
                    $membershipId = (int)$db->lastInsertId();

                    // 3. Insert Initial Paid Record
                    $stmtPay = $db->prepare("
                        INSERT INTO payments (user_id, membership_id, amount, payment_method, transaction_id, status, due_date, paid_at, notes)
                        VALUES (:uid, :mid, :amount, 'Front Desk Activation', :txid, 'paid', :due_date, NOW(), :notes)
                    ");
                    $stmtPay->execute([
                        ':uid'      => $newUserId,
                        ':mid'      => $membershipId,
                        ':amount'   => $planAmount,
                        ':txid'     => 'TXN-ADM-' . strtoupper(uniqid()),
                        ':due_date' => $initialEndDate,
                        ':notes'    => 'Initial ' . $planName . ' activation payment (Monthly 10th cycle)'
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
        $adminHeaderSubtitle = "Enter member profile and select an active package to provision 24/7 keycard access";
        require_once __DIR__ . '/../components/admin-header.php'; 
        ?>

        <!-- Form Workspace -->
        <main class="p-4 sm:p-6 lg:p-8 pb-24 md:pb-8 w-full space-y-6 sm:space-y-8 flex-grow min-w-0">
            
            <?php if ($error): ?>
                <div class="p-4 rounded-2xl bg-red-950/80 border border-red-800 text-red-300 text-xs sm:text-sm flex items-center justify-between shadow-xl animate-fade-in">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-triangle-exclamation text-red-400 text-lg"></i>
                        <span><?= htmlspecialchars($error) ?></span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-red-400 hover:text-white px-2 py-1"><i class="fa-solid fa-xmark"></i></button>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="space-y-6 w-full">
                <?= getCSRFTokenInput() ?>

                <!-- Step 01: Member Basic Details -->
                <div class="bg-zinc-950/80 border border-zinc-900 rounded-3xl p-6 sm:p-8 space-y-5 shadow-sm">
                    <div class="border-b border-zinc-900 pb-4">
                        <span class="text-red-500 text-[10px] font-extrabold uppercase tracking-widest block">Step 01</span>
                        <h3 class="font-heading text-xl font-bold text-white uppercase tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-user-plus text-red-500"></i> Basic Member Details
                        </h3>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-zinc-300 mb-1.5">
                                Full Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="full_name" required placeholder="e.g. Marcus Vance" class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-4 py-3 text-sm text-white placeholder-zinc-500 focus:outline-none focus:border-red-500 transition-colors">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-zinc-300 mb-1.5">
                                Email Address <span class="text-red-500">*</span>
                            </label>
                            <input type="email" name="email" required placeholder="e.g. marcus@gymflow.com" class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-4 py-3 text-sm text-white placeholder-zinc-500 focus:outline-none focus:border-red-500 transition-colors">
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
                            </datalist>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-zinc-300 mb-1.5">
                                Portal Access Password
                            </label>
                            <div class="relative">
                                <input type="password" id="memberPasswordInput" name="password" value="Member123!" required class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-4 py-3 pr-10 text-sm text-white placeholder-zinc-500 focus:outline-none focus:border-red-500 transition-colors">
                                <button type="button" onclick="togglePasswordVisibility()" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-zinc-500 hover:text-zinc-300 text-xs cursor-pointer">
                                    <i id="eyeIcon" class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                            <span class="text-[10px] text-zinc-500 mt-1 block">Default: Member123! (Member can change on login)</span>
                        </div>
                    </div>
                </div>

                <!-- Step 02: Active Membership Package Only -->
                <div class="bg-zinc-950/80 border border-zinc-900 rounded-3xl p-6 sm:p-8 space-y-5 shadow-sm">
                    <div class="border-b border-zinc-900 pb-4 flex items-center justify-between">
                        <div>
                            <span class="text-red-500 text-[10px] font-extrabold uppercase tracking-widest block">Step 02</span>
                            <h3 class="font-heading text-xl font-bold text-white uppercase tracking-wider flex items-center gap-2">
                                <i class="fa-solid fa-cubes text-red-500"></i> Active Membership Package
                            </h3>
                        </div>
                        <span class="text-[11px] font-bold px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center gap-1.5">
                            <i class="fa-solid fa-bolt"></i> Auto-Active 24/7 Access
                        </span>
                    </div>

                    <!-- Interactive Package Selection Cards -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
                        <?php 
                        $firstPlan = true;
                        foreach ($plansList as $p): 
                            $isDefault = $firstPlan;
                            $firstPlan = false;
                        ?>
                            <label class="package-card relative flex flex-col justify-between p-5 rounded-2xl border transition-all cursor-pointer <?= $isDefault ? 'border-red-500 bg-zinc-900/90 shadow-lg shadow-red-500/10' : 'border-zinc-800 bg-zinc-900/50 hover:border-zinc-700 hover:bg-zinc-900' ?>">
                                <input type="radio" name="plan_id" value="<?= (int)$p['id'] ?>" <?= $isDefault ? 'checked' : '' ?> onchange="highlightSelectedPackage(this)" class="sr-only">
                                
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-bold uppercase tracking-wider text-zinc-300"><?= htmlspecialchars($p['name']) ?></span>
                                        <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-full bg-red-500/10 text-red-400 border border-red-500/20">
                                            <?= (int)$p['duration_days'] ?> Days
                                        </span>
                                    </div>
                                    <div class="text-2xl font-heading font-extrabold text-white">
                                        PKR <?= number_format((float)$p['price']) ?>
                                    </div>
                                    <?php if (!empty($p['description'])): ?>
                                        <p class="text-[11px] text-zinc-400 line-clamp-2"><?= htmlspecialchars($p['description']) ?></p>
                                    <?php endif; ?>
                                </div>

                                <div class="mt-4 pt-3 border-t border-zinc-800/80 flex items-center justify-between text-[11px]">
                                    <span class="text-zinc-400 flex items-center gap-1">
                                        <i class="fa-solid fa-shield-halved text-emerald-400 text-xs"></i> 24/7 Turnstile Pass
                                    </span>
                                    <span class="check-indicator font-bold text-red-500 <?= $isDefault ? '' : 'opacity-0' ?>">
                                        <i class="fa-solid fa-circle-check text-base"></i>
                                    </span>
                                </div>
                            </label>
                        <?php endforeach; ?>

                        <?php if (empty($plansList)): ?>
                            <label class="package-card relative flex flex-col justify-between p-5 rounded-2xl border border-red-500 bg-zinc-900/90 shadow-lg shadow-red-500/10 cursor-pointer">
                                <input type="radio" name="plan_id" value="1" checked class="sr-only">
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-bold uppercase tracking-wider text-zinc-300">1 Month Package</span>
                                        <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-full bg-red-500/10 text-red-400 border border-red-500/20">30 Days</span>
                                    </div>
                                    <div class="text-2xl font-heading font-extrabold text-white">PKR 3,500</div>
                                </div>
                                <div class="mt-4 pt-3 border-t border-zinc-800/80 flex items-center justify-between text-[11px]">
                                    <span class="text-zinc-400"><i class="fa-solid fa-shield-halved text-emerald-400"></i> Full Access</span>
                                    <span class="check-indicator font-bold text-red-500"><i class="fa-solid fa-circle-check text-base"></i></span>
                                </div>
                            </label>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Submit Action Buttons -->
                <div class="flex items-center gap-4 pt-2">
                    <button type="submit" class="btn-primary text-xs uppercase font-bold tracking-wider px-8 py-4 rounded-xl shadow-lg shadow-red-600/30 flex items-center gap-2 cursor-pointer">
                        <i class="fa-solid fa-user-check"></i>
                        <span>Register Member & Provision Access</span>
                    </button>
                    <a href="<?= url('admin/members.php') ?>" class="btn-outline text-xs uppercase font-bold tracking-wider px-6 py-4 rounded-xl">
                        Cancel
                    </a>
                </div>
            </form>

        </main>
    </div>

    <script>
    function highlightSelectedPackage(radio) {
        document.querySelectorAll('.package-card').forEach(card => {
            const input = card.querySelector('input[type="radio"]');
            const check = card.querySelector('.check-indicator');
            if (input && input.checked) {
                card.classList.add('border-red-500', 'bg-zinc-900/90', 'shadow-lg', 'shadow-red-500/10');
                card.classList.remove('border-zinc-800', 'bg-zinc-900/50');
                if (check) check.classList.remove('opacity-0');
            } else {
                card.classList.remove('border-red-500', 'bg-zinc-900/90', 'shadow-lg', 'shadow-red-500/10');
                card.classList.add('border-zinc-800', 'bg-zinc-900/50');
                if (check) check.classList.add('opacity-0');
            }
        });
    }

    function togglePasswordVisibility() {
        const input = document.getElementById('memberPasswordInput');
        const icon = document.getElementById('eyeIcon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
    </script>
</body>
</html>
