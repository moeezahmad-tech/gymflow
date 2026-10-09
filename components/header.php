<?php
/**
 * GymFlow - Global Reusable Header & Navigation Bar
 */
if (!defined('APP_INIT')) {
    require_once __DIR__ . '/../config/app.php';
}

$currentPage = basename($_SERVER['SCRIPT_NAME'], '.php');
$loggedIn = isLoggedIn();
$currentUser = getCurrentUser();
?>
<!-- Top Utility Bar -->
<div class="hidden lg:block bg-[#050507] border-b border-zinc-900 text-xs py-2 px-4 text-zinc-400">
    <div class="max-w-7xl mx-auto flex justify-between items-center">
        <div class="flex items-center space-x-6">
            <span class="flex items-center gap-2">
                <i class="fa-solid fa-location-dot text-red-500"></i>
                <span>742 Evergreen Fitness Blvd, Metropolis</span>
            </span>
            <span class="flex items-center gap-2">
                <i class="fa-solid fa-clock text-red-500"></i>
                <span>Open 7 Days: 05:00 AM - 11:00 PM</span>
            </span>
        </div>
        <div class="flex items-center space-x-6">
            <a href="tel:+923001234567" class="hover:text-red-400 flex items-center gap-2 transition-colors">
                <i class="fa-solid fa-phone text-red-500"></i>
                <span class="font-semibold text-zinc-200">+92 300 1234567</span>
            </a>
            <div class="flex items-center space-x-3 text-zinc-400">
                <a href="https://instagram.com" target="_blank" rel="noopener" class="hover:text-red-500 transition-colors" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
                <a href="https://facebook.com" target="_blank" rel="noopener" class="hover:text-red-500 transition-colors" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                <a href="https://youtube.com" target="_blank" rel="noopener" class="hover:text-red-500 transition-colors" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
            </div>
        </div>
    </div>
</div>

<!-- Main Sticky Header -->
<header id="mainHeader" class="sticky top-0 z-50 bg-[#070709] lg:bg-[#070709]/95 glass-nav border-b border-zinc-800 transition-all duration-300 py-3.5">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between">
        
        <!-- Brand Logo (High Contrast White Logo for Dark Background) -->
        <a href="<?= url('index.php') ?>" class="flex items-center gap-3.5 group focus:outline-none">
            <div class="relative flex items-center">
                <img src="<?= asset('images/logo.png') ?>" 
                     alt="Gym Flow Logo" 
                     class="h-11 w-auto max-w-[160px] object-contain transition-transform duration-300 group-hover:scale-105 filter drop-shadow-[0_2px_12px_rgba(255,255,255,0.2)]"
                     onerror="this.style.display='none'; document.getElementById('brandLogoFallback').style.display='flex';" />
                
                <div id="brandLogoFallback" style="display:none;" class="items-center gap-2">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-red-600 to-red-800 flex items-center justify-center text-white font-bold text-xl shadow-lg shadow-red-600/30">
                        <i class="fa-solid fa-dumbbell"></i>
                    </div>
                    <span class="font-heading text-3xl font-bold tracking-wider text-white leading-none group-hover:text-red-500 transition-colors">
                        GYM<span class="text-red-500">FLOW</span>
                    </span>
                </div>
            </div>
        </a>

        <!-- Desktop Navigation Links -->
        <nav class="hidden lg:flex items-center space-x-1 xl:space-x-2">
            <a href="<?= url('index.php') ?>" class="px-3 py-2 text-xs uppercase tracking-wider font-bold transition-colors <?= $currentPage === 'index' ? 'text-red-500' : 'text-zinc-300 hover:text-white' ?>">Home</a>
            <a href="<?= url('about.php') ?>" class="px-3 py-2 text-xs uppercase tracking-wider font-bold transition-colors <?= $currentPage === 'about' ? 'text-red-500' : 'text-zinc-300 hover:text-white' ?>">About Us</a>
            <a href="<?= url('programs.php') ?>" class="px-3 py-2 text-xs uppercase tracking-wider font-bold transition-colors <?= $currentPage === 'programs' ? 'text-red-500' : 'text-zinc-300 hover:text-white' ?>">Programs</a>
            <a href="<?= url('facilities.php') ?>" class="px-3 py-2 text-xs uppercase tracking-wider font-bold transition-colors <?= $currentPage === 'facilities' ? 'text-red-500' : 'text-zinc-300 hover:text-white' ?>">Facilities</a>
            <a href="<?= url('pricing.php') ?>" class="px-3 py-2 text-xs uppercase tracking-wider font-bold transition-colors <?= $currentPage === 'pricing' ? 'text-red-500' : 'text-zinc-300 hover:text-white' ?>">Memberships</a>
            <a href="<?= url('contact.php') ?>" class="px-3 py-2 text-xs uppercase tracking-wider font-bold transition-colors <?= $currentPage === 'contact' ? 'text-red-500' : 'text-zinc-300 hover:text-white' ?>">Contact</a>
        </nav>

        <!-- Right Action Buttons (Dynamic based on Logged-in State) -->
        <div class="hidden md:flex items-center space-x-3.5">
            <?php if ($loggedIn): ?>
                <!-- LOGGED IN STATE: User Profile Dropdown / Hub -->
                <div class="flex items-center gap-3">
                    <a href="<?= ($currentUser['role'] === 'admin') ? url('admin/index.php') : url('user/index.php') ?>" class="flex items-center gap-3 px-3.5 py-2 rounded-xl bg-zinc-900 border border-zinc-700/80 hover:border-red-500 transition-all group">
                        <div class="w-8 h-8 rounded-lg bg-red-600/20 border border-red-500/40 flex items-center justify-center text-red-500 font-bold text-xs">
                            <i class="fa-solid <?= ($currentUser['role'] === 'admin') ? 'fa-shield-halved' : 'fa-user-check' ?>"></i>
                        </div>
                        <div class="text-left">
                            <span class="block text-xs font-bold text-white group-hover:text-red-400 transition-colors leading-tight">
                                <?= htmlspecialchars($currentUser['name']) ?>
                            </span>
                            <span class="block text-[10px] text-zinc-400 uppercase tracking-wider">
                                <?= ($currentUser['role'] === 'admin') ? 'Admin Hub' : 'Member Portal' ?>
                            </span>
                        </div>
                    </a>

                    <!-- Logout Button -->
                    <a href="<?= url('logout.php') ?>" title="Log Out" class="w-9 h-9 rounded-xl bg-zinc-900/80 border border-zinc-800 flex items-center justify-center text-zinc-400 hover:text-red-400 hover:bg-zinc-800 transition-all" aria-label="Log Out">
                        <i class="fa-solid fa-arrow-right-from-bracket text-xs"></i>
                    </a>
                </div>
            <?php else: ?>
                <!-- NOT LOGGED IN STATE: Login Trigger & Join CTA -->
                <a href="<?= url('login.php') ?>" class="text-xs uppercase font-bold tracking-wider text-zinc-200 hover:text-white px-4 py-2.5 rounded-xl border border-zinc-700/80 hover:border-zinc-500 bg-zinc-900/60 hover:bg-zinc-800 transition-all flex items-center gap-2">
                    <i class="fa-regular fa-user text-red-500 text-sm"></i>
                    <span>Member Login</span>
                </a>

                <a href="<?= url('pricing.php') ?>" class="btn-primary text-xs uppercase font-bold tracking-wider px-5 py-2.5 rounded-xl inline-flex items-center gap-2 shadow-lg shadow-red-600/30">
                    <span>Join Now</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            <?php endif; ?>
        </div>

        <!-- Mobile Hamburger Button -->
        <div class="flex items-center lg:hidden">
            <button id="mobileMenuBtn" type="button" class="w-10 h-10 rounded-xl bg-zinc-900 border border-zinc-800 text-zinc-300 hover:text-white flex items-center justify-center focus:outline-none cursor-pointer" aria-label="Toggle Navigation Menu">
                <i class="fa-solid fa-bars text-xl"></i>
            </button>
        </div>
    </div>

    <!-- Mobile Drawer Navigation (100% Solid Readable Background) -->
    <div id="mobileMenu" class="hidden fixed inset-0 z-[9999] bg-[#070709] mobile-menu-drawer flex flex-col justify-between p-6 overflow-y-auto min-h-screen">
        <div>
            <div class="flex items-center justify-between pb-5 border-b border-zinc-800">
                <a href="<?= url('index.php') ?>" class="flex items-center gap-2">
                    <img src="<?= asset('images/logo.png') ?>" alt="Gym Flow" class="h-9 w-auto object-contain">
                </a>
                <button id="mobileMenuClose" type="button" class="w-10 h-10 rounded-xl bg-zinc-900 border border-zinc-800 text-zinc-300 hover:text-white flex items-center justify-center" aria-label="Close menu">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>

            <nav class="flex flex-col space-y-2 mt-5">
                <a href="<?= url('index.php') ?>" class="text-sm font-heading tracking-wider uppercase <?= $currentPage === 'index' ? 'text-red-500 bg-red-600/10 border-red-500/30' : 'text-zinc-200 hover:text-white bg-zinc-900/70 border-zinc-800' ?> px-4 py-3 rounded-xl border flex items-center justify-between transition-colors">
                    <span>Home</span>
                    <i class="fa-solid fa-angle-right text-xs opacity-50"></i>
                </a>
                <a href="<?= url('about.php') ?>" class="text-sm font-heading tracking-wider uppercase <?= $currentPage === 'about' ? 'text-red-500 bg-red-600/10 border-red-500/30' : 'text-zinc-200 hover:text-white bg-zinc-900/70 border-zinc-800' ?> px-4 py-3 rounded-xl border flex items-center justify-between transition-colors">
                    <span>About Us</span>
                    <i class="fa-solid fa-angle-right text-xs opacity-50"></i>
                </a>
                <a href="<?= url('programs.php') ?>" class="text-sm font-heading tracking-wider uppercase <?= $currentPage === 'programs' ? 'text-red-500 bg-red-600/10 border-red-500/30' : 'text-zinc-200 hover:text-white bg-zinc-900/70 border-zinc-800' ?> px-4 py-3 rounded-xl border flex items-center justify-between transition-colors">
                    <span>Programs</span>
                    <i class="fa-solid fa-angle-right text-xs opacity-50"></i>
                </a>
                <a href="<?= url('facilities.php') ?>" class="text-sm font-heading tracking-wider uppercase <?= $currentPage === 'facilities' ? 'text-red-500 bg-red-600/10 border-red-500/30' : 'text-zinc-200 hover:text-white bg-zinc-900/70 border-zinc-800' ?> px-4 py-3 rounded-xl border flex items-center justify-between transition-colors">
                    <span>Facilities & Equipment</span>
                    <i class="fa-solid fa-angle-right text-xs opacity-50"></i>
                </a>
                <a href="<?= url('pricing.php') ?>" class="text-sm font-heading tracking-wider uppercase <?= $currentPage === 'pricing' ? 'text-red-500 bg-red-600/10 border-red-500/30' : 'text-zinc-200 hover:text-white bg-zinc-900/70 border-zinc-800' ?> px-4 py-3 rounded-xl border flex items-center justify-between transition-colors">
                    <span>Membership Plans</span>
                    <i class="fa-solid fa-angle-right text-xs opacity-50"></i>
                </a>
                <a href="<?= url('contact.php') ?>" class="text-sm font-heading tracking-wider uppercase <?= $currentPage === 'contact' ? 'text-red-500 bg-red-600/10 border-red-500/30' : 'text-zinc-200 hover:text-white bg-zinc-900/70 border-zinc-800' ?> px-4 py-3 rounded-xl border flex items-center justify-between transition-colors">
                    <span>Contact & Hours</span>
                    <i class="fa-solid fa-angle-right text-xs opacity-50"></i>
                </a>
                
                <?php if ($loggedIn): ?>
                    <a href="<?= ($currentUser['role'] === 'admin') ? url('admin/index.php') : url('user/index.php') ?>" class="text-sm font-heading tracking-wider uppercase text-emerald-400 bg-emerald-950/50 border-emerald-800 px-4 py-3 rounded-xl border flex items-center gap-2 mt-2">
                        <i class="fa-solid fa-gauge-high text-sm"></i>
                        <span>My Dashboard (<?= htmlspecialchars($currentUser['name']) ?>)</span>
                    </a>
                    <a href="<?= url('logout.php') ?>" class="text-sm font-heading tracking-wider uppercase text-red-400 bg-red-950/40 border-red-900/60 px-4 py-3 rounded-xl border flex items-center gap-2">
                        <i class="fa-solid fa-arrow-right-from-bracket text-sm"></i>
                        <span>Log Out</span>
                    </a>
                <?php else: ?>
                    <a href="<?= url('login.php') ?>" class="text-sm font-heading tracking-wider uppercase text-white bg-zinc-900 border-zinc-700 hover:border-red-500 px-4 py-3 rounded-xl border flex items-center gap-2 mt-2 transition-colors">
                        <i class="fa-solid fa-arrow-right-to-bracket text-red-500 text-sm"></i>
                        <span>Member Login</span>
                    </a>
                <?php endif; ?>

                <!-- Mobile PWA Install Trigger -->
                <button type="button" onclick="window.triggerPWAInstall()" class="text-sm font-heading tracking-wider uppercase text-zinc-300 bg-zinc-900 border-zinc-800 hover:border-red-500 px-4 py-3 rounded-xl border flex items-center justify-between mt-2 transition-colors">
                    <span class="flex items-center gap-2.5">
                        <i class="fa-solid fa-mobile-screen text-red-500"></i>
                        <span>Install Mobile App (iOS / Android)</span>
                    </span>
                    <i class="fa-solid fa-download text-xs text-red-400"></i>
                </button>
            </nav>
        </div>

        <div class="space-y-3 pt-5 border-t border-zinc-800 mt-6">
            <?php if (!$loggedIn): ?>
                <a href="<?= url('pricing.php') ?>" class="w-full text-center block text-xs font-bold uppercase py-3.5 rounded-xl btn-primary shadow-lg shadow-red-600/30">
                    Join Gym Flow Today
                </a>
            <?php else: ?>
                <a href="<?= url('user/index.php') ?>" class="w-full text-center block text-xs font-bold uppercase py-3.5 rounded-xl btn-primary shadow-lg shadow-red-600/30">
                    Open Workout Hub
                </a>
            <?php endif; ?>
            <div class="text-center text-xs text-zinc-400">
                Call Support: <a href="tel:+923001234567" class="text-red-400 font-semibold underline ml-1">+92 300 1234567</a>
            </div>
        </div>
    </div>
</header>

