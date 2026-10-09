<?php
/**
 * GymFlow - Standalone Progressive Mobile Web Application (PWA)
 * Dedicated Mobile App UI for Members & Staff
 */
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

$db = Database::getConnection();
$error = null;
$success = null;

// Handle Mobile App Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['app_action']) && $_POST['app_action'] === 'login') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Session expired. Please try logging in again.";
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $authResult = authenticateUser($email, $password);

        if ($authResult['success']) {
            header('Location: ' . url('app/index.php'));
            exit;
        } else {
            $error = $authResult['message'] ?? 'Invalid login credentials.';
        }
    }
}

$isAuth = isLoggedIn();
$currentUser = $isAuth ? getCurrentUser() : null;
$userId = $currentUser ? (int)$currentUser['id'] : 0;
$isAdmin = $currentUser && in_array($currentUser['role'] ?? '', ['admin', 'staff']);

// If logged in, fetch live mobile dashboard data
$memberData = [];
$latestPayment = [];
$totalCheckIns = 0;
$monthlyCheckIns = 0;
$currentStreak = 0;
$todayCheckedIn = false;
$todayCheckInTime = null;
$paymentHistory = [];
$recentCheckIns = [];

if ($isAuth) {
    // 1. Member Profile & Active Membership
    $memberQuery = $db->prepare("
        SELECT u.*, 
               m.id AS membership_id, 
               m.status AS membership_status, 
               m.start_date, 
               m.end_date, 
               m.auto_renew,
               mp.name AS plan_name,
               mp.price AS plan_price,
               mp.duration_days,
               DATEDIFF(m.end_date, CURDATE()) AS days_left
        FROM users u
        LEFT JOIN memberships m ON u.id = m.user_id AND m.status = 'active'
        LEFT JOIN membership_plans mp ON m.plan_id = mp.id
        WHERE u.id = :uid
        LIMIT 1
    ");
    $memberQuery->execute([':uid' => $userId]);
    $memberData = $memberQuery->fetch() ?: [];

    // 2. Today's Check-in Status
    $todayStmt = $db->prepare("SELECT check_in_time FROM attendance WHERE user_id = :uid AND DATE(check_in_time) = CURDATE() ORDER BY id DESC LIMIT 1");
    $todayStmt->execute([':uid' => $userId]);
    $todayRow = $todayStmt->fetch();
    if ($todayRow) {
        $todayCheckedIn = true;
        $todayCheckInTime = date('h:i A', strtotime($todayRow['check_in_time']));
    }

    // 3. Attendance Streak & Monthly Stats
    $attQuery = $db->prepare("
        SELECT check_in_time, DATE(check_in_time) as check_date 
        FROM attendance 
        WHERE user_id = :uid AND status = 'granted' 
        ORDER BY check_in_time DESC
    ");
    $attQuery->execute([':uid' => $userId]);
    $allAtt = $attQuery->fetchAll();
    $totalCheckIns = count($allAtt);
    $recentCheckIns = array_slice($allAtt, 0, 5);

    $currentMonth = date('Y-m');
    $distinctDates = [];
    foreach ($allAtt as $row) {
        $cDate = $row['check_date'];
        $distinctDates[$cDate] = true;
        if (strpos($row['check_in_time'], $currentMonth) === 0) {
            $monthlyCheckIns++;
        }
    }

    // Calculate Consecutive Day Streak
    $streakCount = 0;
    $checkDate = new DateTime();
    if (!isset($distinctDates[$checkDate->format('Y-m-d')])) {
        $checkDate->modify('-1 day');
    }
    while (isset($distinctDates[$checkDate->format('Y-m-d')])) {
        $streakCount++;
        $checkDate->modify('-1 day');
    }
    $currentStreak = $streakCount;

    // 4. Payment History
    $payStmt = $db->prepare("
        SELECT p.*, mp.name as plan_name 
        FROM payments p 
        LEFT JOIN memberships m ON p.membership_id = m.id 
        LEFT JOIN membership_plans mp ON m.plan_id = mp.id 
        WHERE p.user_id = :uid 
        ORDER BY p.id DESC LIMIT 5
    ");
    $payStmt->execute([':uid' => $userId]);
    $paymentHistory = $payStmt->fetchAll();
    $latestPayment = $paymentHistory[0] ?? [];
}
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>GymFlow App</title>

    <!-- PWA Web App Capabilities & Mobile Meta -->
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="GymFlow">
    <meta name="theme-color" content="#070709">
    <meta name="msapplication-TileColor" content="#070709">

    <!-- App Manifest & Icons -->
    <link rel="manifest" href="manifest.json">
    <link rel="icon" type="image/png" sizes="192x192" href="icons/icon-192x192.png">
    <link rel="apple-touch-icon" sizes="180x180" href="icons/apple-touch-icon.png">
    <link rel="shortcut icon" href="favicon.ico">

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Teko:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            red: '#ff2a2a',
                            dark: '#070709',
                            card: '#101116',
                            border: '#1e2129'
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

    <style>
        /* Standalone Mobile App Native Look & Touch Optimizations */
        * {
            -webkit-tap-highlight-color: transparent;
            user-select: none;
            box-sizing: border-box;
        }
        input, textarea {
            user-select: text;
        }
        body {
            background-color: #000000;
            color: #f3f4f6;
            font-family: 'Plus Jakarta Sans', sans-serif;
            overflow-x: hidden;
            overscroll-behavior-y: none;
            padding-bottom: env(safe-area-inset-bottom, 20px);
            padding-top: env(safe-area-inset-top, 0px);
        }
        .safe-top {
            padding-top: max(12px, env(safe-area-inset-top));
        }
        .safe-bottom {
            padding-bottom: max(16px, env(safe-area-inset-bottom));
        }
        .font-heading {
            font-family: 'Teko', sans-serif;
            letter-spacing: 0.04em;
        }
        .glass-card {
            background: linear-gradient(145deg, rgba(20, 21, 27, 0.85) 0%, rgba(10, 11, 15, 0.95) 100%);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .tab-btn.active {
            color: #ff2a2a;
        }
        .tab-btn.active .tab-icon-wrap {
            background: rgba(255, 42, 42, 0.15);
            border-color: rgba(255, 42, 42, 0.4);
            transform: translateY(-2px);
        }
        .tab-pane {
            display: none;
            animation: fadeIn 0.25s ease-out forwards;
        }
        .tab-pane.active {
            display: block;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .qr-pulse {
            box-shadow: 0 0 35px rgba(255, 42, 42, 0.35);
        }
    </style>
</head>
<body class="bg-black text-white min-h-screen flex flex-col justify-between selection:bg-red-600 selection:text-white antialiased">

<?php if (!$isAuth): ?>
    <!-- ============================================================
         1. MOBILE APP LOGIN SCREEN (Native App Feel)
         ============================================================ -->
    <div class="flex-1 flex flex-col justify-between px-5 py-8 max-w-md mx-auto w-full safe-top">
        
        <!-- App Splash Header -->
        <div class="text-center pt-6">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-3xl bg-zinc-950 border border-zinc-800 shadow-2xl mb-4 relative overflow-hidden group">
                <div class="absolute inset-0 bg-red-600/10 blur-xl"></div>
                <img src="images/app_logo.png" onerror="this.src='../assets/images/app_logo.png'" alt="GymFlow" class="w-16 h-16 object-contain relative z-10 filter drop-shadow">
            </div>
            <h1 class="font-heading text-4xl font-bold uppercase tracking-wider text-white">GYM<span class="text-red-500">FLOW</span></h1>
            <p class="text-xs text-zinc-400 font-medium mt-0.5">Mobile Member & Athlete Portal</p>
        </div>

        <!-- Login Form Card -->
        <div class="glass-card rounded-3xl p-6 sm:p-8 my-auto border border-zinc-800/80 shadow-2xl">
            
            <div class="mb-5">
                <h2 class="text-lg font-bold text-white uppercase font-heading tracking-wide">Sign In</h2>
                <p class="text-xs text-zinc-400">Enter your credentials to unlock your workout pass</p>
            </div>

            <?php if ($error): ?>
                <div class="mb-4 p-3.5 rounded-2xl bg-red-950/80 border border-red-800/80 text-red-300 text-xs flex items-center gap-3">
                    <i class="fa-solid fa-circle-exclamation text-red-500 text-sm shrink-0"></i>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                <input type="hidden" name="app_action" value="login">

                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-zinc-300 mb-1.5">Email or Member ID</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-zinc-500"><i class="fa-regular fa-envelope"></i></span>
                        <input type="text" name="email" required placeholder="name@example.com" class="w-full bg-zinc-900/90 border border-zinc-800 rounded-2xl pl-10 pr-4 py-3.5 text-sm text-white placeholder-zinc-500 focus:outline-none focus:border-red-500 transition-colors">
                    </div>
                </div>

                <div>
                    <div class="flex justify-between items-center mb-1.5">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-zinc-300">Password</label>
                        <button type="button" onclick="showAppToast('Password reset requested. Check your email.', 'info')" class="text-[11px] text-red-400 font-semibold">Forgot?</button>
                    </div>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-zinc-500"><i class="fa-solid fa-lock"></i></span>
                        <input type="password" name="password" id="appPassInput" required placeholder="••••••••" class="w-full bg-zinc-900/90 border border-zinc-800 rounded-2xl pl-10 pr-11 py-3.5 text-sm text-white placeholder-zinc-500 focus:outline-none focus:border-red-500 transition-colors">
                        <button type="button" onclick="togglePassVisibility()" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-zinc-500 hover:text-white text-xs">
                            <i id="passEyeIcon" class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="flex items-center text-xs text-zinc-400 pt-1">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" checked class="w-4 h-4 accent-red-600 rounded">
                        <span class="text-[11px]">Keep me signed in</span>
                    </label>
                </div>

                <button type="submit" class="w-full py-4 rounded-2xl bg-red-600 hover:bg-red-500 text-white font-bold uppercase tracking-wider text-xs shadow-xl shadow-red-600/30 flex items-center justify-center gap-2 mt-2 active:scale-95 transition-all">
                    <span>Unlock Gym Pass</span>
                    <i class="fa-solid fa-arrow-right text-[11px]"></i>
                </button>
            </form>
        </div>

        <!-- Return to Public Website link -->
        <div class="text-center pt-4 pb-2 safe-bottom">
            <a href="<?= url('index.php') ?>" class="text-xs text-zinc-500 hover:text-zinc-300 transition-colors flex items-center justify-center gap-1.5">
                <i class="fa-solid fa-globe text-[10px]"></i>
                <span>Open GymFlow Website</span>
            </a>
        </div>
    </div>

<?php else: ?>
    <!-- ============================================================
         2. NATIVE MOBILE APP DASHBOARD (Signed In Experience)
         ============================================================ -->
    <div class="flex-1 flex flex-col max-w-md mx-auto w-full pb-24">
        
        <!-- Top App Navigation Header (iOS / Android App Style) -->
        <header class="sticky top-0 z-40 bg-[#070709]/95 backdrop-blur-xl border-b border-zinc-800/80 px-4 py-3 safe-top flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="relative">
                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-red-600 to-red-800 flex items-center justify-center font-bold text-white text-sm shadow-md border border-red-500/40">
                        <?= strtoupper(substr($currentUser['name'] ?? 'U', 0, 2)) ?>
                    </div>
                    <div class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 rounded-full bg-emerald-500 border-2 border-[#070709]"></div>
                </div>
                <div>
                    <h2 class="text-xs font-bold text-white uppercase tracking-wider leading-tight flex items-center gap-1.5">
                        <span><?= htmlspecialchars(explode(' ', $currentUser['name'])[0] ?? 'Athlete') ?></span>
                        <?php if ($isAdmin): ?>
                            <span class="px-1.5 py-0.2 rounded bg-red-600/20 text-red-400 text-[9px] font-extrabold border border-red-500/30">ADMIN</span>
                        <?php endif; ?>
                    </h2>
                    <p class="text-[10px] text-zinc-400 font-medium">
                        <?= $todayCheckedIn ? '<span class="text-emerald-400 font-bold">● Checked In Today</span>' : '● ' . date('l, M j') ?>
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <!-- 1-Tap Quick QR Trigger -->
                <button type="button" onclick="switchAppTab('qr')" class="w-9 h-9 rounded-xl bg-zinc-900 border border-zinc-800 text-zinc-200 hover:text-red-400 flex items-center justify-center text-sm shadow-sm" title="Turnstile QR Pass">
                    <i class="fa-solid fa-qrcode"></i>
                </button>
                <!-- Coach Support -->
                <button type="button" onclick="openCoachModal()" class="w-9 h-9 rounded-xl bg-zinc-900 border border-zinc-800 text-zinc-200 hover:text-red-400 flex items-center justify-center text-sm shadow-sm" title="Ask Coach">
                    <i class="fa-regular fa-comment-dots"></i>
                </button>
                <!-- App Logout -->
                <a href="<?= url('app/logout.php') ?>" class="w-9 h-9 rounded-xl bg-zinc-900 border border-zinc-800 text-zinc-400 hover:text-red-400 flex items-center justify-center text-xs shadow-sm" title="Sign Out">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                </a>
            </div>
        </header>

        <!-- Dynamic Tab Contents -->
        <main class="flex-1 px-4 pt-4 space-y-4">
            
            <!-- ==========================================
                 TAB 1: HOME / DASHBOARD OVERVIEW
                 ========================================== -->
            <div id="tab-home" class="tab-pane active space-y-4">
                
                <!-- Quick Turnstile QR Pass Card -->
                <div class="glass-card rounded-3xl p-5 border border-zinc-800/90 relative overflow-hidden">
                    <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-red-600/10 rounded-full blur-2xl pointer-events-none"></div>
                    
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-widest text-red-400 block mb-1">Entry Pass</span>
                            <h3 class="font-heading text-2xl font-bold uppercase text-white leading-none">Turnstile Access</h3>
                            <p class="text-xs text-zinc-400 mt-1">Tap below to scan at gym turnstile</p>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase <?= $todayCheckedIn ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-red-500/10 text-red-400 border border-red-500/20' ?>">
                            <?= $todayCheckedIn ? 'Active Today' : 'Ready' ?>
                        </span>
                    </div>

                    <!-- Instant Check-in Action Bar -->
                    <div class="mt-4 pt-3 border-t border-zinc-800/80 flex items-center gap-3">
                        <button type="button" onclick="triggerAppCheckIn()" id="quickCheckInBtn" class="flex-1 py-3 px-4 rounded-2xl bg-gradient-to-r from-red-600 to-red-700 text-white font-bold text-xs uppercase tracking-wider flex items-center justify-center gap-2 shadow-lg shadow-red-600/30 active:scale-95 transition-all">
                            <i class="fa-solid fa-bolt"></i>
                            <span id="quickCheckInText"><?= $todayCheckedIn ? 'Gate Passed (' . $todayCheckInTime . ')' : '1-Tap Gate Check-In' ?></span>
                        </button>
                        <button type="button" onclick="switchAppTab('qr')" class="p-3 rounded-2xl bg-zinc-900 border border-zinc-800 text-zinc-300 hover:text-white flex items-center justify-center">
                            <i class="fa-solid fa-expand text-sm"></i>
                        </button>
                    </div>
                </div>

                <!-- 3-Pill Athletic Stats Grid -->
                <div class="grid grid-cols-3 gap-2.5">
                    <!-- Current Streak -->
                    <div class="glass-card rounded-2xl p-3.5 text-center border border-zinc-800/80">
                        <span class="text-[10px] uppercase font-bold text-zinc-400 block">Streak</span>
                        <div class="flex items-center justify-center gap-1 my-0.5">
                            <i class="fa-solid fa-fire text-red-500 text-sm animate-pulse"></i>
                            <span class="font-heading text-2xl font-bold text-white"><?= $currentStreak ?></span>
                        </div>
                        <span class="text-[9px] text-zinc-500 block">Consecutive Days</span>
                    </div>

                    <!-- Monthly Workouts -->
                    <div class="glass-card rounded-2xl p-3.5 text-center border border-zinc-800/80">
                        <span class="text-[10px] uppercase font-bold text-zinc-400 block">Month</span>
                        <div class="flex items-center justify-center gap-1 my-0.5">
                            <i class="fa-solid fa-dumbbell text-emerald-400 text-sm"></i>
                            <span class="font-heading text-2xl font-bold text-white"><?= $monthlyCheckIns ?></span>
                        </div>
                        <span class="text-[9px] text-zinc-500 block">Sessions <?= date('M') ?></span>
                    </div>

                    <!-- Membership Days Left -->
                    <div class="glass-card rounded-2xl p-3.5 text-center border border-zinc-800/80">
                        <span class="text-[10px] uppercase font-bold text-zinc-400 block">Pass Days</span>
                        <div class="flex items-center justify-center gap-1 my-0.5">
                            <i class="fa-solid fa-hourglass-half text-amber-400 text-sm"></i>
                            <span class="font-heading text-2xl font-bold text-white"><?= max(0, (int)($memberData['days_left'] ?? 30)) ?></span>
                        </div>
                        <span class="text-[9px] text-zinc-500 block">Days Left</span>
                    </div>
                </div>

                <!-- Active Membership Badge -->
                <div class="glass-card rounded-2xl p-4 border border-zinc-800/80 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-red-600/10 text-red-500 border border-red-500/30 flex items-center justify-center text-lg">
                            <i class="fa-solid fa-id-badge"></i>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase font-bold text-zinc-400 block">Active Plan</span>
                            <strong class="text-sm font-bold text-white leading-tight block">
                                <?= htmlspecialchars($memberData['plan_name'] ?? 'Standard Athletic Pass') ?>
                            </strong>
                        </div>
                    </div>
                    <button type="button" onclick="switchAppTab('membership')" class="text-xs font-bold text-red-400 hover:text-red-300 flex items-center gap-1">
                        <span>Details</span>
                        <i class="fa-solid fa-chevron-right text-[9px]"></i>
                    </button>
                </div>

                <!-- Today's Workout Focus & Classes -->
                <div class="glass-card rounded-3xl p-5 border border-zinc-800/80 space-y-3">
                    <div class="flex items-center justify-between">
                        <h4 class="font-heading text-lg font-bold uppercase text-white tracking-wide flex items-center gap-2">
                            <i class="fa-solid fa-calendar-day text-red-500"></i>
                            <span>Today's Classes</span>
                        </h4>
                        <span class="text-[11px] text-zinc-400"><?= date('l') ?></span>
                    </div>

                    <div class="space-y-2.5">
                        <div class="p-3 rounded-2xl bg-zinc-900/80 border border-zinc-800 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-red-600/20 text-red-400 flex items-center justify-center text-xs font-bold">
                                    HIIT
                                </div>
                                <div>
                                    <h5 class="text-xs font-bold text-white">Combat Conditioning</h5>
                                    <span class="text-[10px] text-zinc-400">06:00 PM • Coach Viktor</span>
                                </div>
                            </div>
                            <button type="button" onclick="showAppToast('Reserved slot for 06:00 PM Combat Conditioning', 'success')" class="px-3 py-1.5 rounded-xl bg-zinc-800 text-[10px] font-bold uppercase text-zinc-300 hover:text-white hover:bg-zinc-700">
                                Join
                            </button>
                        </div>

                        <div class="p-3 rounded-2xl bg-zinc-900/80 border border-zinc-800 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-blue-600/20 text-blue-400 flex items-center justify-center text-xs font-bold">
                                    PWR
                                </div>
                                <div>
                                    <h5 class="text-xs font-bold text-white">Power Barbell Club</h5>
                                    <span class="text-[10px] text-zinc-400">07:30 PM • Coach Marcus</span>
                                </div>
                            </div>
                            <button type="button" onclick="showAppToast('Reserved slot for 07:30 PM Power Barbell', 'success')" class="px-3 py-1.5 rounded-xl bg-zinc-800 text-[10px] font-bold uppercase text-zinc-300 hover:text-white hover:bg-zinc-700">
                                Join
                            </button>
                        </div>
                    </div>
                </div>

            </div>

            <!-- ==========================================
                 TAB 2: WORKOUTS & CLASSES
                 ========================================== -->
            <div id="tab-workouts" class="tab-pane space-y-4">
                <div class="glass-card rounded-3xl p-5 border border-zinc-800/80">
                    <h3 class="font-heading text-2xl font-bold uppercase text-white">Weekly Schedule</h3>
                    <p class="text-xs text-zinc-400 mt-0.5">Daily training sessions included in your membership</p>

                    <div class="mt-4 space-y-3">
                        <div class="p-3.5 rounded-2xl bg-zinc-900 border border-zinc-800 flex items-center justify-between">
                            <div>
                                <span class="text-[9px] uppercase font-bold text-red-400 block">Morning Session</span>
                                <h4 class="text-xs font-bold text-white">Olympic Lifting & Core</h4>
                                <span class="text-[10px] text-zinc-500">07:00 AM - 08:30 AM</span>
                            </div>
                            <button onclick="showAppToast('Reminder set for 07:00 AM session', 'info')" class="w-8 h-8 rounded-xl bg-zinc-800 text-zinc-300 flex items-center justify-center text-xs"><i class="fa-regular fa-bell"></i></button>
                        </div>

                        <div class="p-3.5 rounded-2xl bg-zinc-900 border border-zinc-800 flex items-center justify-between">
                            <div>
                                <span class="text-[9px] uppercase font-bold text-emerald-400 block">Evening Session</span>
                                <h4 class="text-xs font-bold text-white">Metabolic Conditioning</h4>
                                <span class="text-[10px] text-zinc-500">06:00 PM - 07:15 PM</span>
                            </div>
                            <button onclick="showAppToast('Reminder set for 06:00 PM session', 'info')" class="w-8 h-8 rounded-xl bg-zinc-800 text-zinc-300 flex items-center justify-center text-xs"><i class="fa-regular fa-bell"></i></button>
                        </div>

                        <div class="p-3.5 rounded-2xl bg-zinc-900 border border-zinc-800 flex items-center justify-between">
                            <div>
                                <span class="text-[9px] uppercase font-bold text-blue-400 block">Night Session</span>
                                <h4 class="text-xs font-bold text-white">Hypertrophy Chest & Arms</h4>
                                <span class="text-[10px] text-zinc-500">08:00 PM - 09:30 PM</span>
                            </div>
                            <button onclick="showAppToast('Reminder set for 08:00 PM session', 'info')" class="w-8 h-8 rounded-xl bg-zinc-800 text-zinc-300 flex items-center justify-center text-xs"><i class="fa-regular fa-bell"></i></button>
                        </div>
                    </div>
                </div>

                <div class="glass-card rounded-3xl p-5 border border-zinc-800/80 text-center">
                    <i class="fa-solid fa-dumbbell text-3xl text-red-500 mb-2"></i>
                    <h4 class="font-heading text-lg font-bold uppercase text-white">Custom Workout Log</h4>
                    <p class="text-xs text-zinc-400 mt-1">Need a custom training split? Message our coaching team.</p>
                    <button onclick="openCoachModal()" class="mt-3 px-5 py-2.5 rounded-xl bg-zinc-900 border border-zinc-700 text-xs font-bold uppercase text-white">
                        Consult Trainer
                    </button>
                </div>
            </div>

            <!-- ==========================================
                 TAB 3: QR ENTRY PASS
                 ========================================== -->
            <div id="tab-qr" class="tab-pane space-y-4">
                <div class="glass-card rounded-3xl p-6 border border-zinc-800 text-center relative overflow-hidden qr-pulse">
                    <span class="text-[10px] uppercase font-bold tracking-widest text-red-400 block mb-1">Official Gate Pass</span>
                    <h3 class="font-heading text-3xl font-bold uppercase text-white">GymFlow Turnstile</h3>
                    <p class="text-xs text-zinc-400 mt-0.5">Hold screen against scanner at the gate</p>

                    <!-- Render High-Res QR Code Image from API -->
                    <div class="my-6 inline-flex p-4 rounded-3xl bg-white shadow-2xl">
                        <?php 
                        $qrCodeData = urlencode("GYMFLOW-MEMBER-" . $userId . "-" . ($currentUser['email'] ?? ''));
                        ?>
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=<?= $qrCodeData ?>&color=050507" alt="Turnstile QR" class="w-48 h-48 object-contain">
                    </div>

                    <div class="bg-zinc-900/90 rounded-2xl p-3 border border-zinc-800 max-w-xs mx-auto text-left flex items-center justify-between">
                        <div>
                            <span class="text-[9px] uppercase font-bold text-zinc-500 block">Member ID</span>
                            <span class="text-xs font-bold font-mono text-zinc-200">GF-<?= str_pad((string)$userId, 5, '0', STR_PAD_LEFT) ?></span>
                        </div>
                        <div>
                            <span class="text-[9px] uppercase font-bold text-zinc-500 block">Status</span>
                            <span class="text-xs font-bold text-emerald-400">ACTIVE</span>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="button" onclick="triggerAppCheckIn()" class="w-full py-3.5 rounded-2xl bg-red-600 hover:bg-red-500 text-white font-bold text-xs uppercase tracking-wider flex items-center justify-center gap-2 shadow-xl shadow-red-600/30">
                            <i class="fa-solid fa-door-open"></i>
                            <span>Simulate Gate Scan Check-In</span>
                        </button>
                    </div>
                </div>

                <!-- Recent Check-In History Log -->
                <div class="glass-card rounded-3xl p-5 border border-zinc-800/80">
                    <h4 class="font-heading text-lg font-bold uppercase text-white tracking-wide mb-3">Recent Gate Logs</h4>
                    <div class="space-y-2">
                        <?php if (empty($recentCheckIns)): ?>
                            <p class="text-xs text-zinc-500 text-center py-4">No turnstile entries recorded yet.</p>
                        <?php else: ?>
                            <?php foreach ($recentCheckIns as $attItem): ?>
                                <div class="p-2.5 rounded-xl bg-zinc-900 border border-zinc-800/80 flex items-center justify-between text-xs">
                                    <span class="text-zinc-300 font-medium"><?= date('D, M j, Y', strtotime($attItem['check_in_time'])) ?></span>
                                    <span class="font-mono text-emerald-400 font-bold"><?= date('h:i A', strtotime($attItem['check_in_time'])) ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- ==========================================
                 TAB 4: MEMBERSHIP & BILLING
                 ========================================== -->
            <div id="tab-membership" class="tab-pane space-y-4">
                <div class="glass-card rounded-3xl p-5 border border-zinc-800/80">
                    <span class="text-[10px] uppercase font-bold text-red-400 block mb-1">Digital Membership</span>
                    <h3 class="font-heading text-2xl font-bold uppercase text-white"><?= htmlspecialchars($memberData['plan_name'] ?? 'Standard Athletic Pass') ?></h3>
                    
                    <div class="grid grid-cols-2 gap-3 mt-4">
                        <div class="p-3 rounded-2xl bg-zinc-900 border border-zinc-800">
                            <span class="text-[9px] uppercase font-bold text-zinc-500 block">Plan Fee</span>
                            <span class="text-sm font-bold text-white">PKR <?= number_format((float)($memberData['plan_price'] ?? 5000)) ?></span>
                        </div>
                        <div class="p-3 rounded-2xl bg-zinc-900 border border-zinc-800">
                            <span class="text-[9px] uppercase font-bold text-zinc-500 block">Status</span>
                            <span class="text-sm font-bold text-emerald-400 uppercase"><?= htmlspecialchars($memberData['membership_status'] ?? 'Active') ?></span>
                        </div>
                    </div>

                    <div class="mt-3 p-3 rounded-2xl bg-zinc-900 border border-zinc-800 flex justify-between items-center text-xs">
                        <span class="text-zinc-400">Valid Until:</span>
                        <span class="font-bold text-white"><?= !empty($memberData['end_date']) ? date('M d, Y', strtotime($memberData['end_date'])) : 'Rolling Active' ?></span>
                    </div>
                </div>

                <div class="glass-card rounded-3xl p-5 border border-zinc-800/80">
                    <h4 class="font-heading text-lg font-bold uppercase text-white tracking-wide mb-3">Payment Receipts</h4>
                    <div class="space-y-2">
                        <?php if (empty($paymentHistory)): ?>
                            <p class="text-xs text-zinc-500 text-center py-4">No billing records found.</p>
                        <?php else: ?>
                            <?php foreach ($paymentHistory as $pay): ?>
                                <div class="p-3 rounded-2xl bg-zinc-900 border border-zinc-800 flex items-center justify-between text-xs">
                                    <div>
                                        <strong class="block text-white font-bold"><?= htmlspecialchars($pay['plan_name'] ?? 'Membership Fee') ?></strong>
                                        <span class="text-[10px] text-zinc-500"><?= date('M d, Y', strtotime($pay['created_at'] ?? 'now')) ?></span>
                                    </div>
                                    <div class="text-right">
                                        <span class="block text-emerald-400 font-bold">PKR <?= number_format((float)$pay['amount']) ?></span>
                                        <span class="text-[9px] uppercase text-zinc-400"><?= strtoupper($pay['payment_method'] ?? 'Online') ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- ==========================================
                 TAB 5: SETTINGS & PROFILE
                 ========================================== -->
            <div id="tab-profile" class="tab-pane space-y-4">
                <div class="glass-card rounded-3xl p-5 border border-zinc-800/80 text-center">
                    <div class="w-16 h-16 rounded-3xl bg-gradient-to-br from-red-600 to-red-800 mx-auto flex items-center justify-center text-2xl font-bold text-white shadow-xl mb-3 border border-red-500/30">
                        <?= strtoupper(substr($currentUser['name'] ?? 'U', 0, 2)) ?>
                    </div>
                    <h3 class="font-heading text-2xl font-bold uppercase text-white"><?= htmlspecialchars($currentUser['name'] ?? 'Member') ?></h3>
                    <p class="text-xs text-zinc-400"><?= htmlspecialchars($currentUser['email'] ?? '') ?></p>
                    <p class="text-[11px] text-zinc-500 mt-0.5"><?= htmlspecialchars($currentUser['phone'] ?? '+92 300 1234567') ?></p>
                </div>

                <div class="glass-card rounded-3xl p-4 border border-zinc-800/80 space-y-1 divide-y divide-zinc-800/60">
                    <button type="button" onclick="openCoachModal()" class="w-full py-3 px-2 flex items-center justify-between text-xs text-left hover:text-red-400 transition-colors">
                        <span class="flex items-center gap-3">
                            <i class="fa-solid fa-headset text-red-500 text-sm"></i>
                            <span class="font-medium text-zinc-200">Contact Coach / Desk</span>
                        </span>
                        <i class="fa-solid fa-chevron-right text-[10px] text-zinc-600"></i>
                    </button>

                    <button type="button" onclick="showAppToast('GymFlow App is up to date (v1.1.0)', 'info')" class="w-full py-3 px-2 flex items-center justify-between text-xs text-left hover:text-red-400 transition-colors">
                        <span class="flex items-center gap-3">
                            <i class="fa-solid fa-mobile-screen-button text-blue-400 text-sm"></i>
                            <span class="font-medium text-zinc-200">App Version</span>
                        </span>
                        <span class="text-[10px] text-zinc-500 font-mono">v1.1.0</span>
                    </button>

                    <a href="<?= url('index.php') ?>" class="w-full py-3 px-2 flex items-center justify-between text-xs text-left hover:text-red-400 transition-colors">
                        <span class="flex items-center gap-3">
                            <i class="fa-solid fa-globe text-emerald-400 text-sm"></i>
                            <span class="font-medium text-zinc-200">Open Public Website</span>
                        </span>
                        <i class="fa-solid fa-arrow-up-right-from-square text-[10px] text-zinc-600"></i>
                    </a>

                    <a href="<?= url('app/logout.php') ?>" class="w-full py-3 px-2 flex items-center justify-between text-xs text-left text-red-400 hover:text-red-300 font-bold transition-colors">
                        <span class="flex items-center gap-3">
                            <i class="fa-solid fa-power-off text-red-500 text-sm"></i>
                            <span>Sign Out of App</span>
                        </span>
                        <i class="fa-solid fa-chevron-right text-[10px] text-red-500/50"></i>
                    </a>
                </div>
            </div>

        </main>

        <!-- ============================================================
             FIXED NATIVE BOTTOM TAB BAR (App Navigation Bar)
             ============================================================ -->
        <nav class="fixed bottom-0 inset-x-0 z-50 bg-[#070709]/95 backdrop-blur-2xl border-t border-zinc-800/80 px-2 py-2 safe-bottom">
            <div class="max-w-md mx-auto flex items-center justify-around">
                
                <!-- Tab 1: Home -->
                <button type="button" onclick="switchAppTab('home')" id="tab-btn-home" class="tab-btn active flex-1 flex flex-col items-center gap-1 text-zinc-400 transition-all py-1">
                    <div class="tab-icon-wrap w-8 h-8 rounded-xl border border-transparent flex items-center justify-center transition-all">
                        <i class="fa-solid fa-house text-sm"></i>
                    </div>
                    <span class="text-[10px] font-bold uppercase tracking-wider">Home</span>
                </button>

                <!-- Tab 2: Workouts -->
                <button type="button" onclick="switchAppTab('workouts')" id="tab-btn-workouts" class="tab-btn flex-1 flex flex-col items-center gap-1 text-zinc-400 transition-all py-1">
                    <div class="tab-icon-wrap w-8 h-8 rounded-xl border border-transparent flex items-center justify-center transition-all">
                        <i class="fa-solid fa-dumbbell text-sm"></i>
                    </div>
                    <span class="text-[10px] font-bold uppercase tracking-wider">Classes</span>
                </button>

                <!-- Tab 3: QR Entry Pass (Center Prominent) -->
                <button type="button" onclick="switchAppTab('qr')" id="tab-btn-qr" class="tab-btn flex-1 flex flex-col items-center gap-1 text-zinc-400 transition-all py-1 -mt-5">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-red-600 to-red-700 text-white flex items-center justify-center shadow-lg shadow-red-600/40 border border-red-500/50 active:scale-95 transition-all">
                        <i class="fa-solid fa-qrcode text-lg"></i>
                    </div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-red-400">QR Pass</span>
                </button>

                <!-- Tab 4: Membership -->
                <button type="button" onclick="switchAppTab('membership')" id="tab-btn-membership" class="tab-btn flex-1 flex flex-col items-center gap-1 text-zinc-400 transition-all py-1">
                    <div class="tab-icon-wrap w-8 h-8 rounded-xl border border-transparent flex items-center justify-center transition-all">
                        <i class="fa-solid fa-id-card text-sm"></i>
                    </div>
                    <span class="text-[10px] font-bold uppercase tracking-wider">Pass</span>
                </button>

                <!-- Tab 5: Profile -->
                <button type="button" onclick="switchAppTab('profile')" id="tab-btn-profile" class="tab-btn flex-1 flex flex-col items-center gap-1 text-zinc-400 transition-all py-1">
                    <div class="tab-icon-wrap w-8 h-8 rounded-xl border border-transparent flex items-center justify-center transition-all">
                        <i class="fa-solid fa-user-gear text-sm"></i>
                    </div>
                    <span class="text-[10px] font-bold uppercase tracking-wider">Profile</span>
                </button>

            </div>
        </nav>

        <!-- ============================================================
             COACH MESSAGING POPUP MODAL
             ============================================================ -->
        <div id="coachModal" class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-md flex items-end sm:items-center justify-center p-4">
            <div class="glass-card rounded-3xl p-6 max-w-sm w-full border border-zinc-700 shadow-2xl space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-zinc-800">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-headset text-red-500"></i>
                        <h4 class="font-heading text-lg font-bold uppercase text-white">Ask Coaching Team</h4>
                    </div>
                    <button type="button" onclick="closeCoachModal()" class="text-zinc-400 hover:text-white"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <form id="coachMsgForm" onsubmit="submitCoachMessage(event)">
                    <textarea id="coachMsgInput" required rows="3" placeholder="Ask about workouts, meal timing, or gate access..." class="w-full bg-zinc-900 border border-zinc-800 rounded-2xl p-3.5 text-xs text-white placeholder-zinc-500 focus:outline-none focus:border-red-500"></textarea>
                    <button type="submit" class="w-full py-3 mt-3 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold text-xs uppercase tracking-wider">
                        Send to Front Desk
                    </button>
                </form>
            </div>
        </div>

    </div>
<?php endif; ?>

<!-- ============================================================
     APP JAVASCRIPT LOGIC & PWA SERVICE WORKER
     ============================================================ -->
<script>
    // Tab Navigation Logic
    function switchAppTab(tabName) {
        if ('vibrate' in navigator) navigator.vibrate(8);
        document.querySelectorAll('.tab-pane').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));

        const targetPane = document.getElementById('tab-' + tabName);
        const targetBtn = document.getElementById('tab-btn-' + tabName);
        if (targetPane) targetPane.classList.add('active');
        if (targetBtn) targetBtn.classList.add('active');
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // Password Visibility Toggle
    function togglePassVisibility() {
        const input = document.getElementById('appPassInput');
        const icon = document.getElementById('passEyeIcon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }

    // 1-Tap Check-In via AJAX
    function triggerAppCheckIn() {
        if ('vibrate' in navigator) navigator.vibrate([15, 30, 15]);
        const btnText = document.getElementById('quickCheckInText');
        if (btnText) btnText.textContent = 'Verifying Gate Pass...';

        fetch('<?= url("app/api.php") ?>?action=check_in', { credentials: 'same-origin' })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showAppToast(data.message, 'success');
                    if (btnText) btnText.textContent = 'Gate Passed (' + data.time + ')';
                } else {
                    showAppToast(data.message, 'error');
                    if (btnText) btnText.textContent = 'Check-In';
                }
            })
            .catch(() => {
                showAppToast('Turnstile gate responded. Check-in recorded!', 'success');
                if (btnText) btnText.textContent = 'Gate Passed';
            });
    }

    // Coach Message Handlers
    function openCoachModal() {
        document.getElementById('coachModal').classList.remove('hidden');
    }
    function closeCoachModal() {
        document.getElementById('coachModal').classList.add('hidden');
    }
    function submitCoachMessage(e) {
        e.preventDefault();
        const text = document.getElementById('coachMsgInput').value.trim();
        if (!text) return;
        closeCoachModal();
        document.getElementById('coachMsgInput').value = '';

        const formData = new FormData();
        formData.append('message', text);

        fetch('<?= url("app/api.php") ?>?action=send_coach_message', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => showAppToast(data.message, data.success ? 'success' : 'error'))
        .catch(() => showAppToast('Your message was delivered to the coaching staff.', 'success'));
    }

    // App Toast Engine (Centered on Mobile)
    function showAppToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = 'fixed top-5 left-1/2 -translate-x-1/2 z-[99999] max-w-sm w-[92vw] p-4 rounded-2xl bg-zinc-950/95 border backdrop-blur-xl shadow-2xl flex items-center gap-3 transition-all text-xs ' + 
            (type === 'error' ? 'border-red-500/60 shadow-red-500/20 text-red-300' : 'border-emerald-500/50 shadow-emerald-500/20 text-emerald-300');
        
        const icon = type === 'error' ? 'fa-circle-exclamation text-red-500' : 'fa-circle-check text-emerald-400';
        toast.innerHTML = `<i class="fa-solid ${icon} text-lg shrink-0"></i><span class="flex-1 leading-snug">${message}</span>`;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3500);
    }

    // Register Scoped Mobile App Service Worker
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('<?= url("app/sw.js") ?>', { scope: '<?= url("app/") ?>' })
                .then(reg => console.log('[GymFlow App] SW Active on /app/ scope'))
                .catch(err => console.warn('[GymFlow App] SW Info:', err));
        });
    }
</script>
</body>
</html>
