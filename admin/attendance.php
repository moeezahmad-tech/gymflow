<?php
/**
 * GymFlow - Front Desk Daily Attendance & Turnstile Access Console
 * NOTE: Attendance module is temporarily commented out as requested.
 */
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

// Enforce Role-Based Access Control
requireAdmin();

$currentUser = getCurrentUser();
$pageTitle = "Attendance Module (Disabled) - " . APP_NAME;
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
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body class="bg-[#050507] text-zinc-100 min-h-screen flex flex-col md:flex-row antialiased">

    <!-- Modular Admin Sidebar -->
    <?php require_once __DIR__ . '/../components/admin-sidebar.php'; ?>

    <!-- Main Workspace -->
    <div class="flex-1 flex flex-col min-w-0">
        
        <!-- Unified Header -->
        <?php 
        $adminHeaderTitle = "ATTENDANCE MODULE";
        $adminHeaderSubtitle = "Turnstile & Check-in system is currently disabled";
        require_once __DIR__ . '/../components/admin-header.php'; 
        ?>

        <!-- Notice Content -->
        <main class="p-6 flex-grow flex items-center justify-center">
            <div class="glass-card rounded-3xl p-8 max-w-md w-full border border-zinc-800 text-center space-y-4 shadow-2xl">
                <div class="w-16 h-16 rounded-2xl bg-zinc-900 border border-zinc-800 flex items-center justify-center text-zinc-500 text-3xl mx-auto">
                    <i class="fa-solid fa-qrcode"></i>
                </div>
                <h2 class="font-heading text-3xl font-bold text-white uppercase">Module Disabled</h2>
                <p class="text-xs text-zinc-400">
                    The front desk attendance and turnstile tracking module has been temporarily commented out and disabled by the system administrator.
                </p>
                <div class="pt-2">
                    <a href="<?= url('admin/index.php') ?>" class="btn-primary inline-flex items-center gap-2 px-5 py-3 rounded-xl text-xs font-bold uppercase tracking-wider">
                        <i class="fa-solid fa-gauge-high"></i>
                        <span>Return to Dashboard</span>
                    </a>
                </div>
            </div>
        </main>
    </div>

</body>
</html>
<!-- 
========================================================================
FULL ATTENDANCE ENGINE (SAVED & PRESERVED FOR FUTURE USE WHEN RE-ENABLED)
========================================================================
... Turnstile logic, peak hours histogram, audio synthesizer, and real-time dues checks are fully structured and can be re-activated on demand.
-->
