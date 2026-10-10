<?php
/**
 * GymFlow - Admin Modular Categorized Sidebar Component
 */
if (!defined('APP_INIT')) {
    require_once __DIR__ . '/../config/app.php';
}

$activeTab = $activeAdminTab ?? 'dashboard';
$currentUser = getCurrentUser();

$unreadMsgCount = 0;
try {
    $sidebarDB = Database::getConnection();
    $unreadMsgCount = (int)$sidebarDB->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'unread'")->fetchColumn();
} catch (Exception $e) {
    $unreadMsgCount = 0;
}
?>
<!-- Mobile Backdrop Overlay -->
<div id="adminSidebarBackdrop" onclick="toggleMobileSidebar(false)" class="hidden fixed inset-0 bg-black/80 backdrop-blur-sm z-40 md:hidden transition-opacity"></div>

<aside id="adminSidebar" class="fixed inset-y-0 left-0 z-50 w-72 md:w-64 h-screen md:sticky md:top-0 bg-black border-r border-zinc-900 flex flex-col justify-between shrink-0 p-5 -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out">
    <!-- Brand Logo & Quick Status & Mobile Close -->
    <div class="shrink-0 pb-5 border-b border-zinc-900">
        <div class="flex items-center justify-between">
            <a href="<?= url('admin/index.php') ?>" class="flex items-center gap-3">
                <img src="<?= asset('images/logo.png') ?>" alt="Gym Flow" class="h-9 w-auto object-contain">
                <div>
                    <span class="font-heading text-2xl font-bold tracking-wider text-white leading-none block">
                        GYM<span class="text-red-500">FLOW</span>
                    </span>
                    <span class="text-[9px] uppercase tracking-[0.25em] text-red-500 font-bold">Admin Console</span>
                </div>
            </a>
            
            <div class="flex items-center gap-2">
                <span class="hidden md:flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-[10px] text-emerald-400 font-bold">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    Live
                </span>
                <!-- Mobile Close Button -->
                <button type="button" onclick="toggleMobileSidebar(false)" class="md:hidden p-2 rounded-xl bg-zinc-900 text-zinc-400 hover:text-white border border-zinc-800 focus:outline-none" title="Close Menu">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Navigation Menu (Takes flexible height, scrolls internally if needed) -->
    <nav class="flex-1 overflow-y-auto space-y-6 text-xs uppercase font-bold tracking-wider py-4 pr-1">
        
        <!-- SECTION 1: CORE OPERATIONS -->
        <div class="space-y-1.5">
            <span class="text-[10px] uppercase font-bold tracking-widest text-zinc-500 px-3 block">Overview & Desk</span>
            
            <!-- Dashboard -->
            <a href="<?= url('admin/index.php') ?>" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition-all <?= $activeTab === 'dashboard' ? 'bg-red-600 text-white shadow-lg shadow-red-600/30' : 'text-zinc-400 hover:text-white hover:bg-zinc-900' ?>">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-gauge-high text-sm w-4 text-center"></i>
                    <span>Dashboard</span>
                </div>
            </a>
        </div>

        <!-- SECTION 2: MEMBERSHIP MANAGEMENT -->
        <div class="space-y-1.5">
            <span class="text-[10px] uppercase font-bold tracking-widest text-zinc-500 px-3 block">Member Hub</span>

            <!-- Members Directory -->
            <a href="<?= url('admin/members.php') ?>" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition-all <?= $activeTab === 'members' ? 'bg-red-600 text-white shadow-lg shadow-red-600/30' : 'text-zinc-400 hover:text-white hover:bg-zinc-900' ?>">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-users text-sm w-4 text-center"></i>
                    <span>Members Directory</span>
                </div>
            </a>

            <!-- Membership Billing & History -->
            <a href="<?= url('admin/billing.php') ?>" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition-all <?= $activeTab === 'billing' ? 'bg-red-600 text-white shadow-lg shadow-red-600/30' : 'text-zinc-400 hover:text-white hover:bg-zinc-900' ?>">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-file-invoice-dollar text-sm w-4 text-center"></i>
                    <span>Billing & History</span>
                </div>
            </a>

            <!-- Membership Plans / Packages -->
            <a href="<?= url('admin/plans.php') ?>" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition-all <?= $activeTab === 'plans' ? 'bg-red-600 text-white shadow-lg shadow-red-600/30' : 'text-zinc-400 hover:text-white hover:bg-zinc-900' ?>">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-layer-group text-sm w-4 text-center"></i>
                    <span>Membership Plans</span>
                </div>
            </a>

            <!-- Add Member -->
            <a href="<?= url('admin/members-add.php') ?>" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition-all <?= $activeTab === 'members-add' ? 'bg-red-600 text-white shadow-lg shadow-red-600/30' : 'text-zinc-400 hover:text-white hover:bg-zinc-900' ?>">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-user-plus text-sm w-4 text-center"></i>
                    <span>+ Add Member</span>
                </div>
            </a>
        </div>

        <!-- SECTION 3: MARKETING & COMMUNICATIONS -->
        <div class="space-y-1.5">
            <span class="text-[10px] uppercase font-bold tracking-widest text-zinc-500 px-3 block">Outreach & Inquiries</span>

            <!-- Contact Inquiries -->
            <a href="<?= url('admin/messages.php') ?>" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition-all <?= $activeTab === 'messages' ? 'bg-red-600 text-white shadow-lg shadow-red-600/30' : 'text-zinc-400 hover:text-white hover:bg-zinc-900' ?>">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-envelope-open-text text-sm w-4 text-center"></i>
                    <span>Client Inquiries</span>
                </div>
                <?php if ($unreadMsgCount > 0): ?>
                    <span class="px-2 py-0.5 rounded-full bg-red-500 text-white text-[10px] font-bold shadow-sm shadow-red-500/50 animate-pulse">
                        <?= $unreadMsgCount ?>
                    </span>
                <?php endif; ?>
            </a>
        </div>

        <!-- SECTION 4: SYSTEM SHORTCUTS -->
        <div class="space-y-1.5 pt-2 border-t border-zinc-900">
            <a href="<?= url('index.php') ?>" target="_blank" class="flex items-center gap-3 px-3.5 py-2 rounded-xl text-zinc-400 hover:text-white hover:bg-zinc-900 transition-colors">
                <i class="fa-solid fa-arrow-up-right-from-square text-xs w-4 text-center"></i>
                <span>Public Website</span>
            </a>
        </div>
    </nav>

    <!-- Admin Profile Card & Logout (Permanently Anchored to the Bottom) -->
    <div class="shrink-0 pt-4 border-t border-zinc-900 space-y-3 mt-auto">
        <div class="flex items-center gap-3 p-2 rounded-2xl bg-zinc-950 border border-zinc-900">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-red-600 to-red-800 flex items-center justify-center text-white font-bold text-base shadow-md shrink-0">
                <i class="fa-solid fa-user-shield"></i>
            </div>
            <div class="overflow-hidden flex-1">
                <span class="text-xs font-bold text-white block truncate"><?= htmlspecialchars($currentUser['name'] ?? 'Admin Director') ?></span>
                <span class="text-[10px] text-red-400 uppercase font-semibold block tracking-wider">Super Administrator</span>
            </div>
        </div>

        <a href="<?= url('logout.php') ?>" class="w-full text-center block text-xs uppercase font-bold tracking-wider py-2.5 rounded-xl border border-zinc-800 bg-zinc-900 hover:bg-red-600/20 hover:border-red-600/40 text-zinc-300 hover:text-red-400 transition-all">
            <i class="fa-solid fa-arrow-right-from-bracket mr-1.5"></i> Sign Out
        </a>
    </div>
</aside>

<!-- ============================================================
     MOBILE APP BOTTOM NAVIGATION BAR
     Main buttons on bottom, Complete Menu opener on the far right
     ============================================================ -->
<nav id="adminMobileBottomNav" class="md:hidden fixed bottom-0 inset-x-0 z-40 bg-black/95 backdrop-blur-2xl border-t border-zinc-800/80 px-2 py-1.5 flex items-center justify-around shadow-[0_-8px_30px_rgba(0,0,0,0.85)]">
    
    <!-- 1. Dashboard -->
    <a href="<?= url('admin/index.php') ?>" class="flex flex-col items-center justify-center py-1 px-2.5 rounded-xl transition-all <?= $activeTab === 'dashboard' ? 'text-red-500 font-bold' : 'text-zinc-400 hover:text-zinc-200' ?>">
        <div class="relative">
            <i class="fa-solid fa-gauge-high text-base sm:text-lg"></i>
            <?php if ($activeTab === 'dashboard'): ?>
                <span class="absolute -top-1 -right-1 w-1.5 h-1.5 bg-red-500 rounded-full animate-pulse"></span>
            <?php endif; ?>
        </div>
        <span class="text-[10px] mt-1 font-medium tracking-tight">Overview</span>
    </a>

    <!-- 2. Members Directory -->
    <a href="<?= url('admin/members.php') ?>" class="flex flex-col items-center justify-center py-1 px-2.5 rounded-xl transition-all <?= in_array($activeTab, ['members', 'members-add', 'members-edit']) ? 'text-red-500 font-bold' : 'text-zinc-400 hover:text-zinc-200' ?>">
        <div class="relative">
            <i class="fa-solid fa-users text-base sm:text-lg"></i>
            <?php if (in_array($activeTab, ['members', 'members-add', 'members-edit'])): ?>
                <span class="absolute -top-1 -right-1 w-1.5 h-1.5 bg-red-500 rounded-full animate-pulse"></span>
            <?php endif; ?>
        </div>
        <span class="text-[10px] mt-1 font-medium tracking-tight">Members</span>
    </a>

    <!-- 3. Add Member -->
    <a href="<?= url('admin/members-add.php') ?>" class="flex flex-col items-center justify-center py-1 px-2.5 rounded-xl transition-all <?= $activeTab === 'members-add' ? 'text-red-500 font-bold' : 'text-zinc-400 hover:text-zinc-200' ?>">
        <div class="relative">
            <i class="fa-solid fa-user-plus text-base sm:text-lg"></i>
            <?php if ($activeTab === 'members-add'): ?>
                <span class="absolute -top-1 -right-1 w-1.5 h-1.5 bg-red-500 rounded-full animate-pulse"></span>
            <?php endif; ?>
        </div>
        <span class="text-[10px] mt-1 font-medium tracking-tight">Add</span>
    </a>

    <!-- 4. Client Inquiries -->
    <a href="<?= url('admin/messages.php') ?>" class="flex flex-col items-center justify-center py-1 px-2.5 rounded-xl transition-all <?= $activeTab === 'messages' ? 'text-red-500 font-bold' : 'text-zinc-400 hover:text-zinc-200' ?>">
        <div class="relative">
            <i class="fa-solid fa-envelope text-base sm:text-lg"></i>
            <?php if ($unreadMsgCount > 0): ?>
                <span class="absolute -top-1 -right-1 w-1.5 h-1.5 bg-red-500 rounded-full animate-pulse"></span>
            <?php endif; ?>
        </div>
        <span class="text-[10px] mt-1 font-medium tracking-tight">Messages</span>
    </a>

    <!-- 5. Complete Menu Opener (Most Right) -->
    <button type="button" onclick="toggleMobileSidebar(true)" class="flex flex-col items-center justify-center py-1 px-2 rounded-xl text-zinc-400 hover:text-white transition-all cursor-pointer group focus:outline-none" title="Open Complete Menu">
        <div class="w-8 h-8 rounded-xl bg-zinc-900 border border-zinc-800 flex items-center justify-center group-hover:border-red-500/50 group-hover:bg-red-500/10 transition-colors">
            <i class="fa-solid fa-bars-staggered text-xs text-zinc-300 group-hover:text-red-400"></i>
        </div>
        <span class="text-[10px] mt-0.5 font-bold tracking-tight text-zinc-300 group-hover:text-white">Menu</span>
    </button>

</nav>
