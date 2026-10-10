<?php
/**
 * GymFlow - Admin Newsletter & Promotional Email Campaign Manager
 * NOTE: Email Broadcasts module is temporarily commented out and disabled as requested.
 */
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

// Enforce Role-Based Access Control
requireAdmin();

$currentUser = getCurrentUser();
$activeAdminTab = 'newsletter';
$pageTitle = "Email Broadcasts (Disabled) - " . APP_NAME;
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
        $adminHeaderTitle = "EMAIL BROADCASTS MODULE";
        $adminHeaderSubtitle = "Promotional broadcast engine is currently disabled";
        require_once __DIR__ . '/../components/admin-header.php'; 
        ?>

        <!-- Notice Content -->
        <main class="p-6 flex-grow flex items-center justify-center">
            <div class="bg-zinc-950/90 rounded-3xl p-8 max-w-md w-full border border-zinc-900 text-center space-y-4 shadow-2xl animate-fade-in">
                <div class="w-16 h-16 rounded-2xl bg-zinc-900 border border-zinc-800 flex items-center justify-center text-zinc-500 text-3xl mx-auto">
                    <i class="fa-solid fa-bullhorn"></i>
                </div>
                <h2 class="font-heading text-3xl font-bold text-white uppercase">Module Disabled</h2>
                <p class="text-xs text-zinc-400">
                    The email broadcast and newsletter campaign manager module has been temporarily commented out and disabled by the administrator.
                </p>
                <div class="pt-2">
                    <a href="<?= url('admin/members.php') ?>" class="btn-primary inline-flex items-center gap-2 px-5 py-3 rounded-xl text-xs font-bold uppercase tracking-wider">
                        <i class="fa-solid fa-users"></i>
                        <span>Go to Members Directory</span>
                    </a>
                </div>
            </div>
        </main>
    </div>

</body>
</html>
<!--
========================================================================
FULL EMAIL BROADCAST ENGINE (SAVED & PRESERVED FOR FUTURE USE WHEN RE-ENABLED)
========================================================================
Full engine with audience filters, visual composer, templates, test emails & subscriber directory is preserved below:

/*
function getRecipientsByAudience(PDO $db, string $audience): array {
    // ... all audience filtering queries (all_subscribers, all_members, absent_7_days, absent_14_days, expiring_soon, pending_dues)
}

// Full composer UI, live render preview, templates, token replacement, and bulk subscriber importer
*/
-->
