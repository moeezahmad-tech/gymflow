<?php
/**
 * GymFlow - Unified Admin Top Navigation Bar & Global Actions
 */
if (!defined('APP_INIT')) {
    require_once __DIR__ . '/../config/app.php';
}

$currentUser = getCurrentUser();
$headerTitle = $adminHeaderTitle ?? 'Admin Console';
$headerSubtitle = $adminHeaderSubtitle ?? ('Live Operational Control • ' . date('l, F j, Y'));
?>
<header class="bg-black/85 backdrop-blur-xl border-b border-zinc-900 px-4 sm:px-6 py-3.5 sm:py-4 flex items-center justify-between gap-3 sticky top-0 z-30 shadow-lg">
    <!-- Left: Page Title, Mobile Toggle & Breadcrumbs -->
    <div class="flex items-center gap-3 min-w-0">
        <!-- Mobile Sidebar Toggle Button -->
        <button type="button" onclick="toggleMobileSidebar(true)" class="md:hidden p-2 rounded-xl bg-zinc-900 text-zinc-300 hover:text-white border border-zinc-800 shrink-0 focus:outline-none focus:border-red-500" title="Open Navigation Menu">
            <i class="fa-solid fa-bars-staggered text-sm"></i>
        </button>

        <div class="min-w-0">
            <h1 class="font-heading text-xl sm:text-2xl lg:text-3xl font-bold text-white uppercase tracking-wider leading-none truncate">
                <?= htmlspecialchars($headerTitle) ?>
            </h1>
            <p class="text-[11px] sm:text-xs text-zinc-400 mt-0.5 truncate hidden sm:block">
                <?= htmlspecialchars($headerSubtitle) ?>
            </p>
        </div>
    </div>

    <!-- Right: Quick Action Shortcuts & User Menu -->
    <div class="flex items-center gap-2 sm:gap-2.5 shrink-0">
        
        <!-- Quick Add Member -->
        <a href="<?= url('admin/members-add.php') ?>" class="btn-primary px-3 sm:px-3.5 py-1.5 sm:py-2 rounded-xl text-[11px] sm:text-xs uppercase font-bold tracking-wider flex items-center gap-1.5 shadow-lg shadow-red-600/25 shrink-0">
            <i class="fa-solid fa-user-plus text-[10px] sm:text-xs"></i>
            <span class="hidden xs:inline">+ Member</span>
            <span class="xs:hidden">+</span>
        </a>

        <!-- Admin Profile Pill -->
        <div class="flex items-center gap-2 pl-2 border-l border-zinc-800">
            <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-red-600 to-red-800 flex items-center justify-center text-white font-bold text-xs shadow-sm shrink-0">
                <?= strtoupper(substr($currentUser['name'] ?? 'AD', 0, 2)) ?>
            </div>
            <div class="hidden xl:block text-left">
                <span class="text-xs font-bold text-white block leading-tight truncate max-w-[120px]"><?= htmlspecialchars($currentUser['name'] ?? 'Admin') ?></span>
                <span class="text-[9px] uppercase font-bold text-red-400 block tracking-wider">Super Admin</span>
            </div>
        </div>

    </div>
</header>

<!-- Global Command / Search Modal (Ctrl + K) -->
<div id="globalSearchModal" class="hidden fixed inset-0 z-50 bg-black/85 backdrop-blur-xl flex items-start justify-center pt-16 sm:pt-20 p-4">
    <div class="glass-card rounded-3xl p-5 sm:p-6 max-w-xl w-full border border-zinc-700 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-3 border-b border-zinc-800">
            <div class="flex items-center gap-2 text-white font-bold text-xs sm:text-sm uppercase font-heading tracking-wider">
                <i class="fa-solid fa-bolt text-red-500"></i>
                <span>Quick Navigation & Member Search</span>
            </div>
            <button onclick="closeGlobalSearchModal()" class="text-zinc-400 hover:text-white p-1 text-xs">
                <kbd class="px-2 py-0.5 rounded bg-zinc-800 text-[10px] text-zinc-400 border border-zinc-700">ESC</kbd>
            </button>
        </div>

        <!-- Search Input -->
        <div class="relative">
            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3.5 text-zinc-500 text-sm"></i>
            <input 
                type="text" 
                id="globalSearchInput" 
                placeholder="Search member name, ID (e.g. 2026-3), phone..." 
                class="w-full bg-zinc-900 border border-zinc-700 focus:border-red-500 rounded-xl pl-10 pr-4 py-2.5 sm:py-3 text-xs sm:text-sm text-white focus:outline-none"
            >
        </div>

        <!-- Fast Page Shortcuts -->
        <div class="space-y-1.5 pt-2">
            <span class="text-[10px] uppercase font-bold tracking-wider text-zinc-500 block px-2">Quick Page Shortcuts</span>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                <a href="<?= url('admin/index.php') ?>" class="p-2.5 rounded-xl bg-zinc-900 hover:bg-zinc-800 text-zinc-300 hover:text-white flex items-center gap-2 transition-colors">
                    <i class="fa-solid fa-gauge-high text-red-400"></i> Dashboard Overview
                </a>
                <a href="<?= url('admin/members.php') ?>" class="p-2.5 rounded-xl bg-zinc-900 hover:bg-zinc-800 text-zinc-300 hover:text-white flex items-center gap-2 transition-colors">
                    <i class="fa-solid fa-users text-blue-400"></i> Members Directory
                </a>
                <a href="<?= url('admin/payments.php') ?>" class="p-2.5 rounded-xl bg-zinc-900 hover:bg-zinc-800 text-zinc-300 hover:text-white flex items-center gap-2 transition-colors">
                    <i class="fa-solid fa-credit-card text-amber-400"></i> Fee & Dues Ledger
                </a>
                <a href="<?= url('admin/newsletter.php') ?>" class="p-2.5 rounded-xl bg-zinc-900 hover:bg-zinc-800 text-zinc-300 hover:text-white flex items-center gap-2 transition-colors">
                    <i class="fa-solid fa-bullhorn text-purple-400"></i> Email Broadcasts
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    // Global Shortcut Listener (Cmd+K / Ctrl+K)
    window.addEventListener('keydown', (e) => {
        if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
            e.preventDefault();
            openGlobalSearchModal();
        } else if (e.key === 'Escape') {
            closeGlobalSearchModal();
            toggleMobileSidebar(false);
        }
    });

    function openGlobalSearchModal() {
        const modal = document.getElementById('globalSearchModal');
        if (modal) {
            modal.classList.remove('hidden');
            const inp = document.getElementById('globalSearchInput');
            if (inp) {
                inp.focus();
                inp.value = '';
            }
        }
    }

    function closeGlobalSearchModal() {
        const modal = document.getElementById('globalSearchModal');
        if (modal) modal.classList.add('hidden');
    }

    function toggleMobileSidebar(show) {
        const sb = document.getElementById('adminSidebar');
        const backdrop = document.getElementById('adminSidebarBackdrop');
        if (!sb) return;

        const isOpen = sb.classList.contains('translate-x-0');
        const shouldOpen = typeof show === 'boolean' ? show : !isOpen;

        if (shouldOpen) {
            sb.classList.remove('-translate-x-full');
            sb.classList.add('translate-x-0');
            if (backdrop) backdrop.classList.remove('hidden');
            document.body.classList.add('overflow-hidden', 'md:overflow-auto');
        } else {
            sb.classList.add('-translate-x-full');
            sb.classList.remove('translate-x-0');
            if (backdrop) backdrop.classList.add('hidden');
            document.body.classList.remove('overflow-hidden', 'md:overflow-auto');
        }
    }
</script>
