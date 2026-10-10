<?php
/**
 * GymFlow - Global Reusable Premium Header & Navigation Bar
 */
if (!defined('APP_INIT')) {
    require_once __DIR__ . '/../config/app.php';
}

$currentPage = basename($_SERVER['SCRIPT_NAME'], '.php');
$loggedIn = isLoggedIn();
$currentUser = getCurrentUser();
?>
<!-- Top Ambient Glow Line -->
<div class="fixed top-0 inset-x-0 z-[51] h-[1px] bg-gradient-to-r from-transparent via-red-500/60 to-transparent pointer-events-none"></div>

<!-- Main Sticky Navbar -->
<header id="mainHeader" class="sticky top-0 z-50 w-full bg-black/90 backdrop-blur-2xl border-b border-zinc-900/90 shadow-[0_8px_30px_rgba(0,0,0,0.85)] transition-all duration-300">
    <!-- Top Glowing Red Ambient Line -->
    <div class="absolute top-0 inset-x-0 h-[1.5px] bg-gradient-to-r from-transparent via-red-500/70 to-transparent pointer-events-none"></div>

    <div class="relative z-10 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between gap-6">
        
        <!-- Left: Brand Logo with Subtle Ambient Halo -->
        <div class="flex items-center min-w-[180px] shrink-0">
            <a href="<?= url('index.php') ?>" class="flex items-center gap-3 group focus:outline-none">
                <div class="relative flex items-center">
                    <img src="<?= asset('images/logo.png') ?>" 
                         alt="Gym Flow Logo" 
                         class="h-9 w-auto max-w-[140px] object-contain transition-transform duration-300 group-hover:scale-105 filter drop-shadow-[0_0_15px_rgba(255,42,42,0.25)]"
                         onerror="this.style.display='none'; document.getElementById('brandLogoFallback').style.display='flex';" />
                    
                    <div id="brandLogoFallback" style="display:none;" class="items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-red-600 to-red-800 flex items-center justify-center text-white font-bold text-lg shadow-lg shadow-red-600/40">
                            <i class="fa-solid fa-dumbbell"></i>
                        </div>
                        <span class="font-heading text-2xl sm:text-3xl font-bold tracking-wider text-white leading-none group-hover:text-red-500 transition-colors">
                            GYM<span class="text-red-500">FLOW</span>
                        </span>
                    </div>
                </div>
            </a>
        </div>

        <!-- Center: Centered Floating Navigation Capsule -->
        <nav class="hidden lg:flex items-center justify-center">
            <div class="flex items-center bg-zinc-950/90 border border-zinc-800/90 shadow-[inset_0_1px_1px_rgba(255,255,255,0.06),0_8px_24px_rgba(0,0,0,0.6)] rounded-full p-1.5 backdrop-blur-xl">
                <a href="<?= url('index.php') ?>" class="px-4 py-2 rounded-full text-xs uppercase font-extrabold tracking-wider transition-all duration-200 <?= $currentPage === 'index' ? 'bg-gradient-to-r from-red-600 to-red-700 text-white shadow-lg shadow-red-600/35' : 'text-zinc-400 hover:text-white hover:bg-zinc-900/80' ?>">Home</a>
                <a href="<?= url('about.php') ?>" class="px-4 py-2 rounded-full text-xs uppercase font-extrabold tracking-wider transition-all duration-200 <?= $currentPage === 'about' ? 'bg-gradient-to-r from-red-600 to-red-700 text-white shadow-lg shadow-red-600/35' : 'text-zinc-400 hover:text-white hover:bg-zinc-900/80' ?>">About</a>
                <a href="<?= url('programs.php') ?>" class="px-4 py-2 rounded-full text-xs uppercase font-extrabold tracking-wider transition-all duration-200 <?= $currentPage === 'programs' ? 'bg-gradient-to-r from-red-600 to-red-700 text-white shadow-lg shadow-red-600/35' : 'text-zinc-400 hover:text-white hover:bg-zinc-900/80' ?>">Programs</a>
                <a href="<?= url('facilities.php') ?>" class="px-4 py-2 rounded-full text-xs uppercase font-extrabold tracking-wider transition-all duration-200 <?= $currentPage === 'facilities' ? 'bg-gradient-to-r from-red-600 to-red-700 text-white shadow-lg shadow-red-600/35' : 'text-zinc-400 hover:text-white hover:bg-zinc-900/80' ?>">Facilities</a>
                <a href="<?= url('pricing.php') ?>" class="px-4 py-2 rounded-full text-xs uppercase font-extrabold tracking-wider transition-all duration-200 <?= $currentPage === 'pricing' ? 'bg-gradient-to-r from-red-600 to-red-700 text-white shadow-lg shadow-red-600/35' : 'text-zinc-400 hover:text-white hover:bg-zinc-900/80' ?>">Memberships</a>
                <a href="<?= url('contact.php') ?>" class="px-4 py-2 rounded-full text-xs uppercase font-extrabold tracking-wider transition-all duration-200 <?= $currentPage === 'contact' ? 'bg-gradient-to-r from-red-600 to-red-700 text-white shadow-lg shadow-red-600/35' : 'text-zinc-400 hover:text-white hover:bg-zinc-900/80' ?>">Contact</a>
            </div>
        </nav>

        <!-- Right: Action Buttons (Dynamic based on Logged-in State) -->
        <div class="hidden md:flex items-center justify-end space-x-3 min-w-[180px] shrink-0">
            <?php if ($loggedIn): ?>
                <!-- LOGGED IN STATE: User Profile Capsule -->
                <div class="flex items-center gap-2.5">
                    <a href="<?= ($currentUser['role'] === 'admin') ? url('admin/index.php') : url('user/index.php') ?>" class="flex items-center gap-2.5 px-3.5 py-1.5 rounded-full bg-zinc-950 border border-zinc-800 hover:border-red-500/60 shadow-md transition-all group">
                        <div class="w-7 h-7 rounded-full bg-gradient-to-br from-red-600 to-red-800 flex items-center justify-center text-white font-bold text-xs shadow-sm">
                            <i class="fa-solid <?= ($currentUser['role'] === 'admin') ? 'fa-shield-halved' : 'fa-user-check' ?> text-[11px]"></i>
                        </div>
                        <span class="text-xs font-bold text-white group-hover:text-red-400 transition-colors leading-tight">
                            <?= htmlspecialchars($currentUser['name']) ?>
                        </span>
                    </a>

                    <!-- Logout Button -->
                    <a href="<?= url('logout.php') ?>" title="Log Out" class="w-8 h-8 rounded-full bg-zinc-900 border border-zinc-800 flex items-center justify-center text-zinc-400 hover:text-red-400 hover:border-red-500/40 transition-all" aria-label="Log Out">
                        <i class="fa-solid fa-arrow-right-from-bracket text-xs"></i>
                    </a>
                </div>
            <?php else: ?>
                <!-- NOT LOGGED IN STATE: Login & Join CTA -->
                <a href="<?= url('login.php') ?>" class="text-xs uppercase font-extrabold tracking-wider text-zinc-300 hover:text-white px-4 py-2.5 rounded-full border border-zinc-800 hover:border-zinc-700 bg-zinc-950/80 hover:bg-zinc-900 transition-all flex items-center gap-2">
                    <i class="fa-regular fa-user text-red-500 text-xs"></i>
                    <span>Login</span>
                </a>

                <a href="<?= url('pricing.php') ?>" class="text-xs uppercase font-extrabold tracking-wider px-5 py-2.5 rounded-full bg-gradient-to-r from-red-600 to-red-700 hover:from-red-500 hover:to-red-600 text-white shadow-[0_0_20px_rgba(255,42,42,0.35)] hover:shadow-[0_0_28px_rgba(255,42,42,0.5)] transition-all transform hover:scale-[1.02] inline-flex items-center gap-2">
                    <span>Join Now</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            <?php endif; ?>
        </div>

        <!-- Mobile Hamburger Button -->
        <div class="flex items-center lg:hidden">
            <button id="mobileMenuBtn" type="button" class="w-10 h-10 rounded-2xl bg-zinc-950 border border-zinc-800 text-zinc-300 hover:text-white flex items-center justify-center focus:outline-none cursor-pointer hover:border-red-500 transition-colors" aria-label="Toggle Navigation Menu">
                <i class="fa-solid fa-bars text-lg"></i>
            </button>
        </div>
    </div>

    <!-- Mobile Drawer Navigation -->
    <div id="mobileMenu" class="hidden fixed inset-0 z-[9999] bg-[#070709]/95 backdrop-blur-2xl flex flex-col justify-between p-6 overflow-y-auto min-h-screen">
        <div>
            <div class="flex items-center justify-between pb-5 border-b border-zinc-800">
                <a href="<?= url('index.php') ?>" class="flex items-center gap-2">
                    <img src="<?= asset('images/logo.png') ?>" alt="Gym Flow" class="h-9 w-auto object-contain">
                </a>
                <button id="mobileMenuClose" type="button" class="w-10 h-10 rounded-2xl bg-zinc-900 border border-zinc-800 text-zinc-300 hover:text-white flex items-center justify-center" aria-label="Close menu">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>

            <nav class="flex flex-col space-y-2 mt-5">
                <a href="<?= url('index.php') ?>" class="text-sm font-heading tracking-wider uppercase <?= $currentPage === 'index' ? 'text-white bg-gradient-to-r from-red-600 to-red-800 font-bold' : 'text-zinc-200 hover:text-white bg-zinc-900/70 border border-zinc-800' ?> px-4 py-3.5 rounded-2xl flex items-center justify-between transition-colors">
                    <span>Home</span>
                    <i class="fa-solid fa-angle-right text-xs opacity-60"></i>
                </a>
                <a href="<?= url('about.php') ?>" class="text-sm font-heading tracking-wider uppercase <?= $currentPage === 'about' ? 'text-white bg-gradient-to-r from-red-600 to-red-800 font-bold' : 'text-zinc-200 hover:text-white bg-zinc-900/70 border border-zinc-800' ?> px-4 py-3.5 rounded-2xl flex items-center justify-between transition-colors">
                    <span>About Us</span>
                    <i class="fa-solid fa-angle-right text-xs opacity-60"></i>
                </a>
                <a href="<?= url('programs.php') ?>" class="text-sm font-heading tracking-wider uppercase <?= $currentPage === 'programs' ? 'text-white bg-gradient-to-r from-red-600 to-red-800 font-bold' : 'text-zinc-200 hover:text-white bg-zinc-900/70 border border-zinc-800' ?> px-4 py-3.5 rounded-2xl flex items-center justify-between transition-colors">
                    <span>Programs</span>
                    <i class="fa-solid fa-angle-right text-xs opacity-60"></i>
                </a>
                <a href="<?= url('facilities.php') ?>" class="text-sm font-heading tracking-wider uppercase <?= $currentPage === 'facilities' ? 'text-white bg-gradient-to-r from-red-600 to-red-800 font-bold' : 'text-zinc-200 hover:text-white bg-zinc-900/70 border border-zinc-800' ?> px-4 py-3.5 rounded-2xl flex items-center justify-between transition-colors">
                    <span>Facilities & Equipment</span>
                    <i class="fa-solid fa-angle-right text-xs opacity-60"></i>
                </a>
                <a href="<?= url('pricing.php') ?>" class="text-sm font-heading tracking-wider uppercase <?= $currentPage === 'pricing' ? 'text-white bg-gradient-to-r from-red-600 to-red-800 font-bold' : 'text-zinc-200 hover:text-white bg-zinc-900/70 border border-zinc-800' ?> px-4 py-3.5 rounded-2xl flex items-center justify-between transition-colors">
                    <span>Membership Plans</span>
                    <i class="fa-solid fa-angle-right text-xs opacity-60"></i>
                </a>
                <a href="<?= url('contact.php') ?>" class="text-sm font-heading tracking-wider uppercase <?= $currentPage === 'contact' ? 'text-white bg-gradient-to-r from-red-600 to-red-800 font-bold' : 'text-zinc-200 hover:text-white bg-zinc-900/70 border border-zinc-800' ?> px-4 py-3.5 rounded-2xl flex items-center justify-between transition-colors">
                    <span>Contact & Location</span>
                    <i class="fa-solid fa-angle-right text-xs opacity-60"></i>
                </a>
                
                <?php if ($loggedIn): ?>
                    <a href="<?= ($currentUser['role'] === 'admin') ? url('admin/index.php') : url('user/index.php') ?>" class="text-sm font-heading tracking-wider uppercase text-emerald-400 bg-emerald-950/50 border border-emerald-800 px-4 py-3.5 rounded-2xl flex items-center gap-2 mt-3">
                        <i class="fa-solid fa-gauge-high text-sm"></i>
                        <span>Dashboard (<?= htmlspecialchars($currentUser['name']) ?>)</span>
                    </a>
                    <a href="<?= url('logout.php') ?>" class="text-sm font-heading tracking-wider uppercase text-red-400 bg-red-950/40 border border-red-900/60 px-4 py-3.5 rounded-2xl flex items-center gap-2">
                        <i class="fa-solid fa-arrow-right-from-bracket text-sm"></i>
                        <span>Log Out</span>
                    </a>
                <?php else: ?>
                    <a href="<?= url('login.php') ?>" class="text-sm font-heading tracking-wider uppercase text-white bg-zinc-900 border border-zinc-700 hover:border-red-500 px-4 py-3.5 rounded-2xl flex items-center gap-2 mt-3 transition-colors">
                        <i class="fa-solid fa-arrow-right-to-bracket text-red-500 text-sm"></i>
                        <span>Member Login</span>
                    </a>
                <?php endif; ?>

                <a href="<?= url('app/index.php') ?>" class="text-sm font-heading tracking-wider uppercase text-white bg-gradient-to-r from-red-600/20 to-red-900/30 border border-red-500/40 hover:border-red-500 px-4 py-3.5 rounded-2xl flex items-center justify-between mt-2 transition-all">
                    <span class="flex items-center gap-2.5">
                        <i class="fa-solid fa-mobile-screen-button text-red-500"></i>
                        <span>Launch GymFlow App</span>
                    </span>
                    <i class="fa-solid fa-arrow-right text-xs text-red-400"></i>
                </a>
            </nav>
        </div>

        <div class="space-y-3 pt-5 border-t border-zinc-800 mt-6">
            <?php if (!$loggedIn): ?>
                <a href="<?= url('pricing.php') ?>" class="w-full text-center block text-xs font-bold uppercase py-3.5 rounded-2xl bg-gradient-to-r from-red-600 to-red-700 text-white shadow-lg shadow-red-600/40">
                    Join Gym Flow Today
                </a>
            <?php else: ?>
                <a href="<?= url('user/index.php') ?>" class="w-full text-center block text-xs font-bold uppercase py-3.5 rounded-2xl bg-gradient-to-r from-red-600 to-red-700 text-white shadow-lg shadow-red-600/40">
                    Open Workout Hub
                </a>
            <?php endif; ?>
        </div>
    </div>
</header>
