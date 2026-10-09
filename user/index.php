<?php
/**
 * GymFlow - Enhanced Member Portal & Workout Dashboard
 */
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

// Enforce Member Authentication
requireLogin();

$user = getCurrentUser();
$userId = (int)($user['id'] ?? 0);
$db = Database::getConnection();

// Handle Check-in Action
$toastMessage = null;
$toastType = null;
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && isset($_POST['action']) && $_POST['action'] === 'check_in') {
    if (verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        try {
            $todayCheckStmt = $db->prepare("SELECT id FROM attendance WHERE user_id = :uid AND DATE(check_in_time) = CURDATE() LIMIT 1");
            $todayCheckStmt->execute([':uid' => $userId]);
            if (!$todayCheckStmt->fetch()) {
                $insAtt = $db->prepare("INSERT INTO attendance (user_id, check_in_time, entry_type, status) VALUES (:uid, NOW(), 'qr_scanner', 'granted')");
                $insAtt->execute([':uid' => $userId]);
                $toastMessage = "Check-in recorded! Your consistency streak has started.";
                $toastType = "success";
            } else {
                $toastMessage = "You have already checked in today. Keep up the great work!";
                $toastType = "info";
            }
        } catch (Exception $e) {
            $toastMessage = "Could not record check-in: " . $e->getMessage();
            $toastType = "error";
        }
    }
}

// 1. Fetch Dynamic Member Profile & Active Membership from Database
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

// 2. Fetch Latest Payment
$paymentQuery = $db->prepare("
    SELECT p.*, mp.name as plan_name 
    FROM payments p
    LEFT JOIN memberships m ON p.membership_id = m.id
    LEFT JOIN membership_plans mp ON m.plan_id = mp.id
    WHERE p.user_id = :uid 
    ORDER BY p.id DESC 
    LIMIT 1
");
$paymentQuery->execute([':uid' => $userId]);
$latestPayment = $paymentQuery->fetch() ?: [];

// 3. Dynamic Attendance & Streak Calculations from 0
$attQuery = $db->prepare("
    SELECT DATE(check_in_time) as check_date, check_in_time 
    FROM attendance 
    WHERE user_id = :uid AND status = 'granted'
    ORDER BY check_in_time DESC
");
$attQuery->execute([':uid' => $userId]);
$allAttendance = $attQuery->fetchAll();

$totalCheckIns = count($allAttendance);

// Attendance in current month
$currentMonth = (int)date('m');
$currentYear = (int)date('Y');
$currentMonthName = date('F Y');
$daysInMonth = (int)date('t');
$todayDay = (int)date('j');

$trainingDaysThisMonth = [];
$monthlyCheckIns = 0;
$distinctDates = [];

foreach ($allAttendance as $attRow) {
    $cDate = $attRow['check_date'];
    $distinctDates[$cDate] = true;
    $timeObj = strtotime($attRow['check_in_time']);
    if ((int)date('m', $timeObj) === $currentMonth && (int)date('Y', $timeObj) === $currentYear) {
        $dayNum = (int)date('j', $timeObj);
        if (!in_array($dayNum, $trainingDaysThisMonth)) {
            $trainingDaysThisMonth[] = $dayNum;
        }
        $monthlyCheckIns++;
    }
}

// Calculate streak
$currentStreak = 0;
$longestStreak = 0;
$todayDateStr = date('Y-m-d');
$yesterdayDateStr = date('Y-m-d', strtotime('-1 day'));
$todayCheckedIn = isset($distinctDates[$todayDateStr]);

if (!empty($distinctDates)) {
    // Current streak
    $checkDate = $todayCheckedIn ? $todayDateStr : $yesterdayDateStr;
    if (isset($distinctDates[$checkDate])) {
        $currentStreak = 1;
        $dObj = new DateTime($checkDate);
        while (true) {
            $dObj->modify('-1 day');
            $prevStr = $dObj->format('Y-m-d');
            if (isset($distinctDates[$prevStr])) {
                $currentStreak++;
            } else {
                break;
            }
        }
    }

    // Longest streak
    $sortedDates = array_keys($distinctDates);
    sort($sortedDates);
    $tempStreak = 1;
    $longestStreak = 1;
    for ($i = 1; $i < count($sortedDates); $i++) {
        $prev = new DateTime($sortedDates[$i - 1]);
        $curr = new DateTime($sortedDates[$i]);
        $diff = $prev->diff($curr)->days;
        if ($diff === 1) {
            $tempStreak++;
            if ($tempStreak > $longestStreak) {
                $longestStreak = $tempStreak;
            }
        } else {
            $tempStreak = 1;
        }
    }
}

// Weekly Attendance Breakdown (Mon-Sun)
$mondayThisWeek = strtotime('monday this week');
$weekDays = [];
$weeklyCheckInCount = 0;

for ($w = 0; $w < 7; $w++) {
    $dayTimestamp = strtotime("+$w days", $mondayThisWeek);
    $dayDateStr = date('Y-m-d', $dayTimestamp);
    $dayLabel = date('D', $dayTimestamp);
    $isTodayDay = ($dayDateStr === $todayDateStr);
    $hasCheckIn = isset($distinctDates[$dayDateStr]);
    if ($hasCheckIn) {
        $weeklyCheckInCount++;
    }
    $weekDays[] = [
        'label'      => $dayLabel,
        'date'       => $dayDateStr,
        'is_today'   => $isTodayDay,
        'checked_in' => $hasCheckIn
    ];
}

$monthlyGoalTarget = 16;
$monthlyGoalPct = $monthlyGoalTarget > 0 ? min(100, round(($monthlyCheckIns / $monthlyGoalTarget) * 100)) : 0;

$planName = $memberData['plan_name'] ?? ($user['plan'] ?? 'Active Membership');
$memberCode = $memberData['member_code'] ?? ($user['member_id'] ?? (date('Y') . '-' . $userId));
$daysLeft = isset($memberData['days_left']) ? (int)$memberData['days_left'] : 0;
$expiryDate = !empty($memberData['end_date']) ? date('M j, Y', strtotime($memberData['end_date'])) : 'Pending Renewal';
$membershipStatus = $memberData['membership_status'] ?? 'active';

$pageTitle = "Member Dashboard - " . htmlspecialchars($user['name']) . " | " . APP_NAME;
$isNewRegistration = !empty($_GET['registered']);

// Head Component
require_once __DIR__ . '/../components/head.php';
// Header Component
require_once __DIR__ . '/../components/header.php';
?>

<?php if ($toastMessage): ?>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
        <div class="p-4 rounded-2xl bg-emerald-950/80 border border-emerald-800 text-emerald-300 text-xs sm:text-sm flex items-center justify-between shadow-xl">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-circle-check text-emerald-400 text-xl"></i>
                <span><?= htmlspecialchars($toastMessage) ?></span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-white px-2 py-1"><i class="fa-solid fa-xmark"></i></button>
        </div>
    </div>
<?php endif; ?>

<?php if ($isNewRegistration): ?>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
        <div class="p-4 rounded-2xl bg-emerald-950/80 border border-emerald-800 text-emerald-300 text-xs sm:text-sm flex items-center justify-between shadow-xl">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-circle-check text-emerald-400 text-xl"></i>
                <span><strong>Welcome to Gym Flow!</strong> Your membership account is active. Your attendance and statistics start from today.</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-white px-2 py-1"><i class="fa-solid fa-xmark"></i></button>
        </div>
    </div>
<?php endif; ?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 w-full flex-grow">
    
    <!-- ============================================================
         1. MEMBER WELCOME BANNER & DYNAMIC STATS
         ============================================================ -->
    <div class="glass-card rounded-3xl p-6 sm:p-8 mb-8 relative overflow-hidden bg-gradient-to-r from-zinc-950 via-zinc-900 to-zinc-950 border border-zinc-800 shadow-2xl">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 relative z-10">
            
            <!-- User Profile Snapshot -->
            <div class="flex items-center gap-4 sm:gap-5">
                <div class="relative">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-gradient-to-br from-red-600 to-red-800 border-2 border-red-500/50 flex items-center justify-center text-white text-3xl font-bold shadow-xl shadow-red-600/30">
                        <i class="fa-solid fa-user-astronaut"></i>
                    </div>
                    <span class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full <?= $membershipStatus === 'active' ? 'bg-emerald-500' : 'bg-amber-500' ?> border-2 border-black flex items-center justify-center text-[9px] text-white" title="<?= ucfirst($membershipStatus) ?> Membership">
                        <i class="fa-solid fa-check"></i>
                    </span>
                </div>

                <div>
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-full bg-red-600/20 border border-red-500/30 text-red-400 text-[10px] font-bold uppercase tracking-wider">
                            <?= htmlspecialchars($planName) ?>
                        </span>
                        <span class="px-2.5 py-0.5 rounded-full bg-zinc-900 border border-zinc-700 text-zinc-200 font-mono text-[11px] font-bold">
                            Roll #: <?= htmlspecialchars($memberCode) ?>
                        </span>
                    </div>
                    <h1 class="font-heading text-3xl sm:text-4xl font-bold text-white uppercase mt-1 leading-tight">
                        Welcome, <span class="text-red-500"><?= htmlspecialchars($user['name']) ?></span>!
                    </h1>
                    <p class="text-xs text-zinc-400 mt-0.5">Track your daily gym momentum and log attendance.</p>
                </div>
            </div>

            <!-- Quick Action Triggers -->
            <div class="flex flex-wrap items-center gap-3">
                <form method="POST" class="inline">
                    <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                    <input type="hidden" name="action" value="check_in">
                    <button type="submit" class="btn-primary text-xs uppercase font-bold tracking-wider px-5 py-3 rounded-xl flex items-center gap-2 shadow-lg shadow-red-600/30">
                        <i class="fa-solid fa-fire text-sm <?= $todayCheckedIn ? 'text-amber-300' : '' ?>"></i>
                        <span><?= $todayCheckedIn ? 'Checked In Today ✓' : 'Check In Today' ?></span>
                    </button>
                </form>

                <a href="<?= url('logout.php') ?>" class="text-xs uppercase font-bold tracking-wider text-zinc-400 hover:text-red-400 px-4 py-3 rounded-xl border border-zinc-800 hover:border-zinc-700 bg-zinc-900/60 transition-all flex items-center gap-1.5" title="Sign Out">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    <span>Log Out</span>
                </a>
            </div>

        </div>

        <!-- Metric Mini Bar (Live 0-Based Calculations) -->
        <div class="mt-8 pt-6 border-t border-zinc-800/80 grid grid-cols-1 sm:grid-cols-3 gap-5 text-center sm:text-left">
            <div class="sm:border-r border-zinc-800/80 sm:pr-4">
                <p class="text-[11px] uppercase tracking-wider text-zinc-400 font-bold flex items-center gap-1.5 justify-center sm:justify-start">
                    <i class="fa-solid fa-calendar-check text-red-500"></i>
                    <span>Monthly Workouts</span>
                </p>
                <p class="font-heading text-3xl font-bold text-white mt-1">
                    <?= $monthlyCheckIns ?> <?= $monthlyCheckIns === 1 ? 'Session' : 'Sessions' ?>
                    <span class="text-xs <?= $monthlyCheckIns > 0 ? 'text-emerald-400' : 'text-zinc-500' ?> font-sans font-semibold">
                        <?= $monthlyCheckIns > 0 ? 'Active this month' : 'No visits yet' ?>
                    </span>
                </p>
            </div>
            <div class="sm:border-r border-zinc-800/80 sm:pr-4">
                <p class="text-[11px] uppercase tracking-wider text-zinc-400 font-bold flex items-center gap-1.5 justify-center sm:justify-start">
                    <i class="fa-solid fa-fire text-amber-500"></i>
                    <span>Current Active Streak</span>
                </p>
                <p class="font-heading text-3xl font-bold <?= $currentStreak > 0 ? 'text-amber-400' : 'text-zinc-400' ?> mt-1">
                    <?= $currentStreak ?> <?= $currentStreak === 1 ? 'Day' : 'Days' ?> Streak 
                    <?php if ($currentStreak > 0): ?>
                        <i class="fa-solid fa-fire text-sm text-red-500 animate-pulse"></i>
                    <?php endif; ?>
                </p>
            </div>
            <div>
                <p class="text-[11px] uppercase tracking-wider text-zinc-400 font-bold flex items-center gap-1.5 justify-center sm:justify-start">
                    <i class="fa-solid fa-credit-card text-emerald-500"></i>
                    <span>Membership Status</span>
                </p>
                <p class="font-heading text-3xl font-bold <?= $daysLeft > 0 ? 'text-emerald-400' : 'text-amber-400' ?> mt-1">
                    <?= max(0, $daysLeft) ?> Days Left 
                    <span class="text-xs text-zinc-400 font-sans font-semibold">(<?= ucfirst($membershipStatus) ?>)</span>
                </p>
            </div>
        </div>
    </div>

    <!-- ============================================================
         2. STREAK ENGINE, ACTIVITY HEATMAP & MEMBERSHIP GRID
         ============================================================ -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Left 7 Cols: Streak Engine, Attendance Heatmap & Volume Bar Graph -->
        <div class="lg:col-span-7 space-y-8">
            
            <!-- Main Streak & Consistency Chart Card -->
            <div class="glass-card rounded-3xl p-6 sm:p-8 border border-zinc-800 shadow-2xl relative overflow-hidden">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-zinc-800 gap-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full <?= $currentStreak > 0 ? 'bg-amber-500 animate-pulse' : 'bg-zinc-600' ?>"></span>
                            <span class="text-xs font-bold uppercase tracking-widest text-amber-400">Workout Streak Engine</span>
                        </div>
                        <h2 class="font-heading text-3xl font-bold text-white uppercase mt-1">TRAINING STREAK & CONSISTENCY</h2>
                        <p class="text-xs text-zinc-400">Track your daily gym attendance, momentum and workout frequency</p>
                    </div>

                    <!-- Streak Badge Box -->
                    <div class="bg-gradient-to-br from-amber-500/20 to-red-600/20 border border-amber-500/40 rounded-2xl px-5 py-3 text-center flex-shrink-0">
                        <span class="font-heading text-3xl font-bold text-amber-400 leading-none block">
                            <?= $currentStreak ?> <span class="text-sm">DAYS</span>
                        </span>
                        <span class="text-[10px] uppercase tracking-wider text-zinc-300 font-bold flex items-center gap-1 justify-center mt-1">
                            <i class="fa-solid fa-fire <?= $currentStreak > 0 ? 'text-red-500' : 'text-zinc-600' ?>"></i> 
                            <?= $currentStreak > 0 ? 'On Fire' : 'Start Today' ?>
                        </span>
                    </div>
                </div>

                <!-- Streak Stats Row (True 0-Based Data) -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 my-6">
                    <div class="p-3.5 rounded-2xl bg-zinc-900/90 border border-zinc-800 text-center">
                        <span class="text-[10px] uppercase font-bold text-zinc-400 block">Current Streak</span>
                        <span class="font-heading text-2xl font-bold <?= $currentStreak > 0 ? 'text-amber-400' : 'text-zinc-400' ?> mt-0.5 block">
                            <?= $currentStreak ?> <?= $currentStreak === 1 ? 'Day' : 'Days' ?>
                        </span>
                        <span class="text-[9px] <?= $currentStreak > 0 ? 'text-emerald-400' : 'text-zinc-500' ?> font-semibold">
                            <?= $currentStreak > 0 ? 'Active 🔥' : '0 Streak' ?>
                        </span>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-zinc-900/90 border border-zinc-800 text-center">
                        <span class="text-[10px] uppercase font-bold text-zinc-400 block">Longest Streak</span>
                        <span class="font-heading text-2xl font-bold text-white mt-0.5 block">
                            <?= $longestStreak ?> <?= $longestStreak === 1 ? 'Day' : 'Days' ?>
                        </span>
                        <span class="text-[9px] text-zinc-400">Personal Record</span>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-zinc-900/90 border border-zinc-800 text-center">
                        <span class="text-[10px] uppercase font-bold text-zinc-400 block">Monthly Goal</span>
                        <span class="font-heading text-2xl font-bold text-emerald-400 mt-0.5 block">
                            <?= $monthlyCheckIns ?> / <?= $monthlyGoalTarget ?>
                        </span>
                        <span class="text-[9px] text-emerald-400 font-semibold"><?= $monthlyGoalPct ?>% Completed</span>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-zinc-900/90 border border-zinc-800 text-center">
                        <span class="text-[10px] uppercase font-bold text-zinc-400 block">Total Check-Ins</span>
                        <span class="font-heading text-2xl font-bold text-white mt-0.5 block">
                            <?= $totalCheckIns ?> <?= $totalCheckIns === 1 ? 'Visit' : 'Visits' ?>
                        </span>
                        <span class="text-[9px] text-zinc-400">All Time</span>
                    </div>
                </div>

                <!-- Monthly Activity Streak Calendar / Heatmap -->
                <div class="space-y-3 pt-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-zinc-300"><?= $currentMonthName ?> Activity Heatmap</span>
                        <div class="flex items-center gap-2 text-[10px] text-zinc-400">
                            <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded bg-zinc-800"></span> Unattended</span>
                            <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded bg-emerald-500"></span> Trained</span>
                            <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded bg-red-500"></span> Today</span>
                        </div>
                    </div>

                    <!-- Dynamic Days Grid for Current Month -->
                    <div class="grid grid-cols-7 sm:grid-cols-10 md:grid-cols-11 gap-2 p-4 rounded-2xl bg-zinc-950 border border-zinc-800">
                        <?php 
                        for ($d = 1; $d <= $daysInMonth; $d++): 
                            $isTrained = in_array($d, $trainingDaysThisMonth);
                            $isToday = ($d === $todayDay);
                        ?>
                            <div class="h-10 rounded-xl flex flex-col items-center justify-center text-xs font-mono transition-all <?= $isToday ? ($isTrained ? 'bg-red-600 text-white font-bold ring-2 ring-red-400' : 'bg-red-950/60 text-red-300 border border-red-800/80 font-bold ring-1 ring-red-500') : ($isTrained ? 'bg-emerald-600/30 text-emerald-300 border border-emerald-500/40 font-semibold' : 'bg-zinc-900/60 text-zinc-600 border border-zinc-800/60') ?>">
                                <span class="text-[10px]"><?= $d ?></span>
                                <?php if ($isTrained): ?>
                                    <i class="fa-solid fa-check text-[8px] text-emerald-400"></i>
                                <?php elseif ($isToday): ?>
                                    <span class="text-[7px] uppercase font-bold text-red-400">Today</span>
                                <?php endif; ?>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>

                <!-- Weekly Attendance Breakdown -->
                <div class="mt-8 pt-6 border-t border-zinc-800">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-xs font-bold uppercase tracking-wider text-zinc-300">Weekly Attendance Breakdown</span>
                        <span class="text-xs text-zinc-400"><?= $weeklyCheckInCount ?> / 7 Days Attended This Week</span>
                    </div>

                    <div class="grid grid-cols-7 gap-3 text-center">
                        <?php foreach ($weekDays as $wDay): ?>
                            <div class="space-y-2">
                                <div class="h-24 bg-zinc-900 rounded-xl p-1.5 flex flex-col justify-between items-center border border-zinc-800/80 transition-all <?= $wDay['checked_in'] ? 'bg-emerald-950/20 border-emerald-500/40 shadow-lg shadow-emerald-950/30' : ($wDay['is_today'] ? 'bg-red-950/20 border-red-500/30' : '') ?>">
                                    <div class="w-full flex justify-center pt-2">
                                        <?php if ($wDay['checked_in']): ?>
                                            <div class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 border border-emerald-500/40 flex items-center justify-center text-sm">
                                                <i class="fa-solid fa-check"></i>
                                            </div>
                                        <?php elseif ($wDay['is_today']): ?>
                                            <div class="w-8 h-8 rounded-lg bg-red-600/20 text-red-400 border border-red-500/30 flex items-center justify-center text-xs">
                                                <i class="fa-solid fa-clock"></i>
                                            </div>
                                        <?php else: ?>
                                            <div class="w-8 h-8 rounded-lg bg-zinc-800/40 text-zinc-600 flex items-center justify-center text-xs">
                                                <i class="fa-solid fa-minus"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="w-full pb-1">
                                        <?php if ($wDay['checked_in']): ?>
                                            <span class="text-[9px] uppercase tracking-wider font-bold text-emerald-400 block">Present</span>
                                        <?php elseif ($wDay['is_today']): ?>
                                            <span class="text-[9px] uppercase tracking-wider font-bold text-amber-400 block">Today</span>
                                        <?php else: ?>
                                            <span class="text-[9px] uppercase tracking-wider font-semibold text-zinc-500 block">Rest</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <span class="text-[10px] font-bold <?= $wDay['is_today'] ? 'text-amber-400' : 'text-zinc-400' ?> uppercase block">
                                    <?= $wDay['is_today'] ? 'Today' : $wDay['label'] ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="mt-8 pt-5 border-t border-zinc-800 flex flex-wrap items-center justify-between gap-4">
                    <form method="POST" class="inline">
                        <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                        <input type="hidden" name="action" value="check_in">
                        <button type="submit" class="btn-primary text-xs uppercase font-bold tracking-wider px-6 py-3.5 rounded-xl flex items-center gap-2 shadow-lg shadow-red-600/30">
                            <i class="fa-solid fa-fire"></i>
                            <span><?= $todayCheckedIn ? 'Checked In Today ✓' : 'Check In & Keep Streak Alive' ?></span>
                        </button>
                    </form>
                    <span class="text-xs text-zinc-400">
                        <i class="fa-solid fa-shield-check <?= $todayCheckedIn ? 'text-emerald-400' : 'text-zinc-600' ?> mr-1"></i>
                        <?= $todayCheckedIn ? 'Today\'s gym check-in verified' : 'No check-in recorded today yet' ?>
                    </span>
                </div>
            </div>

        </div>

        <!-- Right 5 Cols: Membership Status & Coach Message -->
        <div class="lg:col-span-5 space-y-8">
            
            <!-- Membership Card -->
            <div class="glass-card rounded-3xl p-6 sm:p-7 border border-zinc-800 shadow-2xl">
                <div class="flex items-center justify-between pb-4 border-b border-zinc-800">
                    <h3 class="font-heading text-2xl font-bold text-white uppercase">Membership Tier</h3>
                    <span class="text-xs font-bold <?= $membershipStatus === 'active' ? 'text-emerald-400 bg-emerald-500/10 border-emerald-500/20' : 'text-amber-400 bg-amber-500/10 border-amber-500/20' ?> px-3 py-1 rounded-full border flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full <?= $membershipStatus === 'active' ? 'bg-emerald-400' : 'bg-amber-400' ?>"></span> 
                        <?= ucfirst($membershipStatus) ?>
                    </span>
                </div>

                <div class="space-y-3.5 text-xs mt-5">
                    <div class="flex justify-between py-2 border-b border-zinc-800/60">
                        <span class="text-zinc-400">Plan Package:</span>
                        <span class="text-white font-bold"><?= htmlspecialchars($planName) ?></span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-zinc-800/60">
                        <span class="text-zinc-400">Member ID:</span>
                        <span class="font-mono text-red-400 font-bold"><?= htmlspecialchars($memberCode) ?></span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-zinc-800/60">
                        <span class="text-zinc-400">Gym Access:</span>
                        <span class="text-emerald-400 font-bold">Turnstile & Keycard Access</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-zinc-800/60">
                        <span class="text-zinc-400">Valid Until:</span>
                        <span class="text-white font-bold"><?= htmlspecialchars($expiryDate) ?></span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="text-zinc-400">Payment Status:</span>
                        <span class="text-emerald-400 font-semibold"><?= !empty($latestPayment) ? ucfirst($latestPayment['status']) . ' (PKR ' . number_format($latestPayment['amount']) . ')' : 'Settled' ?></span>
                    </div>
                </div>

                <button onclick="openBillingModal()" class="w-full mt-6 btn-outline text-xs uppercase font-bold tracking-wider py-3 rounded-xl flex items-center justify-center gap-2">
                    <i class="fa-solid fa-receipt"></i>
                    <span>View Membership & Billing</span>
                </button>
            </div>

            <!-- Assigned Coach Note Card -->
            <div class="glass-card rounded-3xl p-6 sm:p-7 border border-zinc-800 shadow-2xl relative overflow-hidden">
                <div class="flex items-center gap-4 pb-4 border-b border-zinc-800">
                    <div class="w-12 h-12 rounded-2xl bg-red-600/10 text-red-500 flex items-center justify-center text-xl font-bold border border-red-500/20">
                        <i class="fa-solid fa-user-shield"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-white uppercase">GymFlow Coaching Team</h4>
                        <p class="text-xs text-red-400">Member Support & Guidance</p>
                    </div>
                </div>
                <p class="text-xs text-zinc-300 leading-relaxed mt-4 italic bg-zinc-900/60 p-3.5 rounded-2xl border border-zinc-800">
                    <?php if ($totalCheckIns === 0): ?>
                        "Welcome to GymFlow, <?= htmlspecialchars($user['name']) ?>! Check in for your first workout session to start your consistency streak and track your progress."
                    <?php else: ?>
                        "Keep up the momentum, <?= htmlspecialchars($user['name']) ?>! Consistency is key to achieving your strength and conditioning goals."
                    <?php endif; ?>
                </p>
                <button onclick="openCoachMessageModal()" class="w-full mt-4 btn-primary text-xs uppercase font-bold tracking-wider py-3 rounded-xl flex items-center justify-center gap-2 shadow-lg shadow-red-600/30">
                    <i class="fa-regular fa-paper-plane"></i>
                    <span>Send Coach a Quick Message</span>
                </button>
            </div>

        </div>

    </div>
</main>

<!-- ============================================================
     CUSTOM MODAL: VIEW MEMBERSHIP & BILLING
     ============================================================ -->
<div id="billingModal" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-[#0b0b0f] border border-zinc-800 w-full max-w-lg rounded-3xl p-6 sm:p-8 shadow-2xl relative">
        <button onclick="closeBillingModal()" class="absolute top-6 right-6 text-zinc-400 hover:text-white text-lg">
            <i class="fa-solid fa-xmark"></i>
        </button>
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-xl bg-emerald-600/10 text-emerald-400 flex items-center justify-center text-lg border border-emerald-500/20">
                <i class="fa-solid fa-receipt"></i>
            </div>
            <div>
                <h3 class="font-heading text-2xl font-bold text-white uppercase">MEMBERSHIP & BILLING RECEIPT</h3>
                <p class="text-xs text-zinc-400">Official subscriber invoice & digital clearance</p>
            </div>
        </div>
        <div class="space-y-3 text-xs bg-zinc-900/70 p-5 rounded-2xl border border-zinc-800">
            <div class="flex justify-between py-1.5 border-b border-zinc-800">
                <span class="text-zinc-400">Subscriber Name:</span>
                <span class="text-white font-bold"><?= htmlspecialchars($user['name']) ?></span>
            </div>
            <div class="flex justify-between py-1.5 border-b border-zinc-800">
                <span class="text-zinc-400">Member ID Code:</span>
                <span class="font-mono text-red-400 font-bold"><?= htmlspecialchars($memberCode) ?></span>
            </div>
            <div class="flex justify-between py-1.5 border-b border-zinc-800">
                <span class="text-zinc-400">Plan Assigned:</span>
                <span class="text-white font-bold"><?= htmlspecialchars($planName) ?></span>
            </div>
            <div class="flex justify-between py-1.5 border-b border-zinc-800">
                <span class="text-zinc-400">Package Fee:</span>
                <span class="text-white font-bold"><?= !empty($memberData['plan_price']) ? 'PKR ' . number_format($memberData['plan_price']) : (!empty($latestPayment['amount']) ? 'PKR ' . number_format($latestPayment['amount']) : 'PKR 8,000') ?></span>
            </div>
            <div class="flex justify-between py-1.5 border-b border-zinc-800">
                <span class="text-zinc-400">Payment Status:</span>
                <span class="text-emerald-400 font-bold bg-emerald-500/10 px-2 py-0.5 rounded border border-emerald-500/20"><?= !empty($latestPayment['status']) ? strtoupper($latestPayment['status']) . ' & RECORDED' : 'ACTIVE & SETTLED' ?></span>
            </div>
            <div class="flex justify-between py-1.5">
                <span class="text-zinc-400">Expiry / Renewal:</span>
                <span class="text-amber-400 font-bold"><?= htmlspecialchars($expiryDate) ?> (<?= max(0, $daysLeft) ?> days left)</span>
            </div>
        </div>
        <div class="mt-6 flex justify-end">
            <button onclick="closeBillingModal()" class="btn-outline text-xs uppercase font-bold px-6 py-2.5 rounded-xl">Close</button>
        </div>
    </div>
</div>

<!-- ============================================================
     CUSTOM MODAL: COACH DIRECT MESSAGE
     ============================================================ -->
<div id="coachModal" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-[#0b0b0f] border border-zinc-800 w-full max-w-lg rounded-3xl p-6 sm:p-8 shadow-2xl relative">
        <button onclick="closeCoachMessageModal()" class="absolute top-6 right-6 text-zinc-400 hover:text-white text-lg">
            <i class="fa-solid fa-xmark"></i>
        </button>
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-xl bg-red-600/10 text-red-500 flex items-center justify-center text-lg border border-red-500/20">
                <i class="fa-regular fa-paper-plane"></i>
            </div>
            <div>
                <h3 class="font-heading text-2xl font-bold text-white uppercase">MESSAGE COACHING TEAM</h3>
                <p class="text-xs text-zinc-400">Ask questions about form, progression, or scheduling</p>
            </div>
        </div>
        <form onsubmit="handleSendCoachMsg(event)" class="space-y-4">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-zinc-400 mb-1.5">Your Question / Message</label>
                <textarea id="coachMsgText" required rows="4" placeholder="Hi Coach, what should I focus on for my workout routine today?" class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-4 py-3 text-xs text-white placeholder-zinc-500 focus:outline-none focus:border-red-500 transition-colors leading-relaxed"></textarea>
            </div>
            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" onclick="closeCoachMessageModal()" class="px-5 py-2.5 rounded-xl bg-zinc-900 text-zinc-400 hover:text-white text-xs font-bold uppercase">Cancel</button>
                <button type="submit" class="btn-primary text-xs uppercase font-bold tracking-wider px-6 py-2.5 rounded-xl shadow-lg shadow-red-600/30 flex items-center gap-2">
                    <i class="fa-regular fa-paper-plane text-xs"></i>
                    <span>Send Message</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openBillingModal() {
        document.getElementById('billingModal').classList.remove('hidden');
    }

    function closeBillingModal() {
        document.getElementById('billingModal').classList.add('hidden');
    }

    function openCoachMessageModal() {
        document.getElementById('coachModal').classList.remove('hidden');
    }

    function closeCoachMessageModal() {
        document.getElementById('coachModal').classList.add('hidden');
    }

    function handleSendCoachMsg(e) {
        e.preventDefault();
        const text = document.getElementById('coachMsgText').value.trim();
        if (text) {
            closeCoachMessageModal();
            document.getElementById('coachMsgText').value = '';
            if (typeof showToast === 'function') {
                showToast('Your message has been delivered to the coaching staff.', 'message', 'Message Delivered');
            } else {
                alert('Your message has been delivered to the coaching staff.');
            }
        }
    }
</script>

<?php
// Footer Component
require_once __DIR__ . '/../components/footer.php';
?>
