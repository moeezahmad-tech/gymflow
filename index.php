<?php
/**
 * GymFlow - Main Public Landing Page
 */
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';

$pageTitle = "Gym Flow - Begin Your Fitness Journey | High Performance Training";
$pageDescription = "Gym Flow is a premier athletic facility featuring Eleiko strength platforms, combat boxing, recovery saunas, and certified coaching in Metropolis.";
$pageKeywords = "gym, bodybuilding, fitness club, personal trainer, HIIT workout, crossfit, strength training, Gym Flow";

// Include Modular Head Component
require_once __DIR__ . '/components/head.php';

// Include Global Header/Navbar
require_once __DIR__ . '/components/header.php';
?>

<main class="flex-grow">
    <!-- ============================================================
         1. HIGH-CONVERSION HERO SECTION
         ============================================================ -->
    <section class="relative min-h-[92vh] flex items-center justify-center overflow-hidden bg-black py-20 lg:py-32">
        <!-- Background Dark Gradient Overlay & Athletic Dot Pattern -->
        <div class="absolute inset-0 z-0 bg-gradient-to-b from-black/85 via-black/60 to-black pointer-events-none"></div>
        <div class="absolute inset-0 z-0 bg-[radial-gradient(#ff2a2a_1px,transparent_1px)] [background-size:32px_32px] opacity-10 pointer-events-none"></div>
        
        <!-- Local Hero Background Banner Image -->
        <div class="absolute inset-0 z-[-1] opacity-40 scale-105 transform filter grayscale contrast-125 bg-cover bg-center" style="background-image: url('<?= asset('images/0d931a74f61693ae690eeeac95436444.jpg') ?>');"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center flex flex-col items-center">
            

            <!-- Watermark Athletic Outline Text -->
            <div class="outline-text font-heading text-6xl sm:text-8xl md:text-9xl font-extrabold uppercase tracking-widest leading-none select-none -mb-8 sm:-mb-14 opacity-35">
                YOUR FITNESS
            </div>

            <!-- Main High Impact Title -->
            <h1 class="font-heading text-5xl sm:text-7xl md:text-8xl font-black uppercase text-white tracking-wide leading-tight drop-shadow-2xl">
                BEGIN YOUR <br class="hidden sm:block" />
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-white via-zinc-200 to-red-500">FITNESS JOURNEY</span>
            </h1>

            <p class="mt-6 text-base sm:text-lg text-zinc-300 max-w-2xl font-normal leading-relaxed">
                Transform your physique and elevate athletic capacity with specialized barbell programming, HIIT combat, and master personal coaches.
            </p>

            <!-- Call to Actions -->
            <div class="mt-10 flex flex-wrap items-center justify-center gap-4 sm:gap-6">
                <a href="<?= url('pricing.php') ?>" class="btn-primary text-sm sm:text-base font-bold uppercase tracking-wider px-8 py-4 rounded-xl shadow-xl shadow-red-600/30 inline-flex items-center gap-2">
                    <span>Explore Memberships</span>
                    <i class="fa-solid fa-arrow-right text-xs"></i>
                </a>
                <button type="button" onclick="window.triggerPWAInstall()" class="btn-outline text-sm sm:text-base font-bold uppercase tracking-wider px-8 py-4 rounded-xl inline-flex items-center gap-2.5 hover:border-red-500 transition-all cursor-pointer">
                    <i class="fa-solid fa-mobile-screen-button text-red-500 text-base"></i>
                    <span>Get App</span>
                </button>
            </div>

            <!-- Stats Bar -->
            <div class="mt-16 grid grid-cols-2 md:grid-cols-4 gap-6 sm:gap-10 w-full max-w-4xl border-t border-zinc-800/80 pt-8">
                <div class="text-center">
                    <span class="font-heading text-4xl sm:text-5xl font-bold text-red-500">500+</span>
                    <p class="text-xs uppercase tracking-widest text-zinc-400 font-semibold mt-1">Active Members</p>
                </div>
                <div class="text-center">
                    <span class="font-heading text-4xl sm:text-5xl font-bold text-white">45+</span>
                    <p class="text-xs uppercase tracking-widest text-zinc-400 font-semibold mt-1">Weekly Classes</p>
                </div>
                <div class="text-center">
                    <span class="font-heading text-4xl sm:text-5xl font-bold text-red-500">20+</span>
                    <p class="text-xs uppercase tracking-widest text-zinc-400 font-semibold mt-1">Elite Coaches</p>
                </div>
                <div class="text-center">
                    <span class="font-heading text-4xl sm:text-5xl font-bold text-white">24/7</span>
                    <p class="text-xs uppercase tracking-widest text-zinc-400 font-semibold mt-1">Keycard Access</p>
                </div>
            </div>

        </div>
    </section>

    <!-- ============================================================
         2. "WE RAISE YOUR CONFIDENCE" ABOUT SECTION
         ============================================================ -->
    <section id="about" class="py-24 bg-[#070708] border-y border-zinc-900 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                
                <!-- Left Visual Collage with Local Uploaded Images -->
                <div class="lg:col-span-6 relative">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-4">
                            <div class="rounded-2xl overflow-hidden border border-zinc-800 shadow-2xl relative group">
                                <img src="<?= asset('images/3844e94a9c5f8420999736d4aa364aac.jpg') ?>" 
                                     alt="Athlete Training at Gym Flow" 
                                     class="w-full h-64 object-cover filter grayscale contrast-125 group-hover:scale-105 transition-transform duration-500"
                                     onerror="this.src='<?= asset('images/0d931a74f61693ae690eeeac95436444.jpg') ?>'">
                            </div>
                            <!-- "DO IT" Kettlebell Badge -->
                            <div class="bg-zinc-950 border border-zinc-800 rounded-2xl p-6 flex flex-col items-center text-center shadow-xl">
                                <div class="w-16 h-16 rounded-full bg-red-600/10 border border-red-500/30 flex items-center justify-center text-red-500 text-2xl mb-2">
                                    <i class="fa-solid fa-fire"></i>
                                </div>
                                <span class="font-heading text-2xl font-bold text-white tracking-wider">DO IT NOW</span>
                                <span class="text-xs text-zinc-400">Push Beyond Limits</span>
                            </div>
                        </div>

                        <div class="pt-8">
                            <div class="rounded-2xl overflow-hidden border border-zinc-800 shadow-2xl relative group">
                                <img src="<?= asset('images/3c38ea4f1e502963d98766fd1ae27d25.jpg') ?>" 
                                     alt="Fitness workout session" 
                                     class="w-full h-80 object-cover filter grayscale contrast-125 group-hover:scale-105 transition-transform duration-500"
                                     onerror="this.src='<?= asset('images/3dba86670044f42a9451abaef5ca07f4.jpg') ?>'">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Copy & Value Propositions -->
                <div class="lg:col-span-6 space-y-6">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-red-600/10 border border-red-500/20 text-red-500 text-xs font-bold uppercase tracking-widest">
                        <i class="fa-solid fa-bolt"></i> About Gym Flow
                    </div>

                    <h2 class="font-heading text-4xl sm:text-6xl font-bold text-white tracking-wide uppercase leading-none">
                        WE RAISE YOUR <span class="text-red-500">CONFIDENCE</span>
                    </h2>

                    <p class="text-zinc-400 text-base leading-relaxed">
                        At Gym Flow, our philosophy revolves around continuous personal evolution. Whether your target is muscular hypertrophy, cardiovascular health, or athletic agility, our facilities give you the edge.
                    </p>

                    <!-- Feature Checkmarks with Red Trending Icons -->
                    <div class="space-y-3.5 pt-2">
                        <div class="flex items-start gap-3">
                            <i class="fa-solid fa-arrow-trend-up text-red-500 mt-1"></i>
                            <span class="text-zinc-300 text-sm font-medium">Customized periodized workout and nutritional blueprints.</span>
                        </div>
                        <div class="flex items-start gap-3">
                            <i class="fa-solid fa-arrow-trend-up text-red-500 mt-1"></i>
                            <span class="text-zinc-300 text-sm font-medium">Olympic competition barbells, calibrated plates, and precision cable rigs.</span>
                        </div>
                        <div class="flex items-start gap-3">
                            <i class="fa-solid fa-arrow-trend-up text-red-500 mt-1"></i>
                            <span class="text-zinc-300 text-sm font-medium">Cardiovascular conditioning & recovery zones with cedar sauna.</span>
                        </div>
                        <div class="flex items-start gap-3">
                            <i class="fa-solid fa-arrow-trend-up text-red-500 mt-1"></i>
                            <span class="text-zinc-300 text-sm font-medium">Qualified personal trainers tracking every milestone in real time.</span>
                        </div>
                    </div>

                    <div class="pt-4 flex flex-wrap gap-4">
                        <a href="<?= url('about.php') ?>" class="inline-flex items-center gap-3 bg-zinc-900 hover:bg-zinc-800 text-white font-heading text-xl uppercase tracking-wider px-8 py-3.5 rounded-xl border border-zinc-700 transition-all">
                            <span>Learn More About Us</span>
                            <i class="fa-solid fa-chevron-right text-xs text-red-500"></i>
                        </a>
                    </div>

                </div>

            </div>
        </div>
    </section>

    <!-- ============================================================
         3. DISCIPLINES & VISUAL GRID
         ============================================================ -->
    <section id="programs" class="py-20 bg-black">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="text-center max-w-2xl mx-auto mb-16">
                <span class="text-red-500 text-xs font-bold uppercase tracking-[0.25em]">Disciplines & Programs</span>
                <h2 class="font-heading text-4xl sm:text-6xl font-bold text-white tracking-wide uppercase mt-2">
                    FIND YOUR <span class="text-red-500">TRAINING FOCUS</span>
                </h2>
            </div>

            <!-- 4-Block Alternating Visual Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 border border-zinc-800 rounded-2xl overflow-hidden shadow-2xl">
                
                <!-- Tile 1: CARDIO MAN (Dark) -->
                <div class="bg-zinc-950 p-8 flex flex-col justify-between border-b lg:border-b-0 border-r border-zinc-800 relative group hover:bg-[#0d0e12] transition-colors min-h-[280px]">
                    <div class="space-y-4">
                        <div class="w-12 h-12 rounded-xl bg-zinc-900 border border-zinc-800 flex items-center justify-center text-red-500 text-xl group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-person-running"></i>
                        </div>
                        <span class="text-[10px] uppercase tracking-widest text-zinc-400 font-bold block">Explore The Futures</span>
                        <h3 class="font-heading text-3xl font-bold text-white tracking-wider">CARDIO MAN</h3>
                        <p class="text-xs text-zinc-400 leading-relaxed">
                            High-intensity interval stamina training designed to incinerate body fat and elevate VO2 max.
                        </p>
                    </div>
                    <div class="mt-6 flex justify-between items-end">
                        <a href="<?= url('programs.php') ?>" class="text-xs font-bold text-red-500 uppercase tracking-wider flex items-center gap-1 group-hover:translate-x-1 transition-transform">Explore <i class="fa-solid fa-arrow-right text-[10px]"></i></a>
                    </div>
                </div>

                <!-- Tile 2: Local Boxer Visual Photo (01) -->
                <div class="relative overflow-hidden group min-h-[280px] border-b lg:border-b-0 border-r border-zinc-800 bg-zinc-900">
                    <img src="<?= asset('images/3dba86670044f42a9451abaef5ca07f4.jpg') ?>" 
                         alt="Gym Flow Boxer & Fighter" 
                         class="w-full h-full object-cover filter grayscale contrast-125 group-hover:scale-105 transition-transform duration-500">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                    <div class="absolute bottom-4 right-6">
                        <span class="font-heading text-6xl font-extrabold outline-text select-none">01</span>
                    </div>
                </div>

                <!-- Tile 3: SELF DEFENSE (Red Accent Block) -->
                <div class="bg-red-600 p-8 flex flex-col justify-between border-b lg:border-b-0 border-r border-zinc-800 relative group text-white min-h-[280px]">
                    <div class="space-y-4">
                        <div class="w-12 h-12 rounded-xl bg-black/20 border border-white/20 flex items-center justify-center text-white text-xl group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-hand-fist"></i>
                        </div>
                        <span class="text-[10px] uppercase tracking-widest text-red-200 font-bold block">Body Shape & Combat</span>
                        <h3 class="font-heading text-3xl font-bold text-white tracking-wider">SELF DEFENSE</h3>
                        <p class="text-xs text-red-100 leading-relaxed">
                            Boxing, combatives, and tactical movement taught by elite combat instructors.
                        </p>
                    </div>
                    <div class="mt-6 flex justify-between items-end">
                        <a href="<?= url('programs.php') ?>" class="text-xs font-bold text-white uppercase tracking-wider flex items-center gap-1 group-hover:translate-x-1 transition-transform">Explore <i class="fa-solid fa-arrow-right text-[10px]"></i></a>
                    </div>
                </div>

                <!-- Tile 4: Local Athlete Visual Photo (02) -->
                <div class="relative overflow-hidden group min-h-[280px] bg-zinc-900">
                    <img src="<?= asset('images/0d931a74f61693ae690eeeac95436444.jpg') ?>" 
                         alt="Gym Flow Bodybuilder" 
                         class="w-full h-full object-cover filter grayscale contrast-125 group-hover:scale-105 transition-transform duration-500">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                    <div class="absolute bottom-4 right-6">
                        <span class="font-heading text-6xl font-extrabold outline-text select-none">02</span>
                    </div>
                </div>

            </div>

        </div>
    </section>

    <!-- ============================================================
         4. FACILITIES & GEAR PREVIEW SECTION
         ============================================================ -->
    <section id="facilities" class="py-20 bg-[#070708] border-t border-zinc-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-14 gap-6">
                <div>
                    <span class="text-red-500 text-xs font-bold uppercase tracking-[0.25em]">25,000 Sq. Ft. Facility</span>
                    <h2 class="font-heading text-4xl sm:text-6xl font-bold text-white uppercase mt-2">
                        CHAMPION-GRADE <span class="text-red-500">EQUIPMENT</span>
                    </h2>
                </div>
                <a href="<?= url('facilities.php') ?>" class="btn-outline text-xs uppercase font-bold tracking-wider px-6 py-3 rounded-xl inline-flex items-center gap-2 self-start md:self-auto">
                    <span>View All Facilities</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="glass-card rounded-2xl p-6 space-y-4">
                    <div class="w-12 h-12 rounded-xl bg-red-600/10 text-red-500 flex items-center justify-center text-xl">
                        <i class="fa-solid fa-dumbbell"></i>
                    </div>
                    <h3 class="font-heading text-2xl font-bold text-white uppercase">Olympic Free Weights</h3>
                    <p class="text-xs text-zinc-400 leading-relaxed">Eleiko competition barbells, calibrated steel discs, 8 dedicated squat cages, and dumbbells up to 150 lbs.</p>
                </div>

                <div class="glass-card rounded-2xl p-6 space-y-4">
                    <div class="w-12 h-12 rounded-xl bg-red-600/10 text-red-500 flex items-center justify-center text-xl">
                        <i class="fa-solid fa-fire-burner"></i>
                    </div>
                    <h3 class="font-heading text-2xl font-bold text-white uppercase">Thermal Sauna & Cold Plunge</h3>
                    <p class="text-xs text-zinc-400 leading-relaxed">Dry cedarwood Finnish sauna operating at 195°F paired with 45°F cold hydrotherapy tubs for rapid muscle recovery.</p>
                </div>

                <div class="glass-card rounded-2xl p-6 space-y-4">
                    <div class="w-12 h-12 rounded-xl bg-red-600/10 text-red-500 flex items-center justify-center text-xl">
                        <i class="fa-solid fa-ring"></i>
                    </div>
                    <h3 class="font-heading text-2xl font-bold text-white uppercase">Combat Ring & Turf</h3>
                    <p class="text-xs text-zinc-400 leading-relaxed">Full size boxing ring, heavy bag arena, and a 40-yard sprint turf track with weighted sleds and battle ropes.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================
         5. MEMBERSHIP PRICING OVERVIEW
         ============================================================ -->
    <section id="pricing" class="py-24 bg-black border-t border-zinc-900 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="text-center max-w-2xl mx-auto mb-16">
                <span class="text-red-500 text-xs font-bold uppercase tracking-[0.25em]">Flexible Commitments</span>
                <h2 class="font-heading text-4xl sm:text-6xl font-bold text-white tracking-wide uppercase mt-2">
                    MEMBERSHIP <span class="text-red-500">TIERS</span>
                </h2>
                <p class="text-zinc-400 text-sm mt-3">Select the tier engineered for your personal goals. Month-to-month flexibility.</p>
            </div>

<?php
// Load dynamic plans from database with fallback
$homePlans = [];
try {
    $db = getDB();
    $stmtHP = $db->query("SELECT * FROM membership_plans WHERE status = 'active' ORDER BY duration_days ASC LIMIT 3");
    $homePlans = $stmtHP->fetchAll();
} catch (Exception $e) {
    $homePlans = [];
}
?>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?php if (!empty($homePlans)): ?>
                    <?php foreach ($homePlans as $plan): 
                        $features = json_decode($plan['features'] ?? '[]', true) ?: [];
                        $isPop = (bool)$plan['is_popular'];
                        $durationMonths = max(1, round($plan['duration_days'] / 30));
                        $monthlyEquiv = round($plan['price'] / $durationMonths);
                    ?>
                        <!-- Plan Card -->
                        <div class="glass-card rounded-2xl p-8 flex flex-col justify-between relative transition-all border <?= $isPop ? 'border-red-600/60 shadow-2xl shadow-red-600/20 scale-105 bg-gradient-to-b from-zinc-900 to-black z-10' : 'border-zinc-800 hover:border-zinc-700' ?>">
                            
                            <?php if ($isPop): ?>
                                <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 bg-red-600 text-white text-[10px] font-extrabold uppercase tracking-widest px-4 py-1 rounded-full shadow-lg flex items-center gap-1.5">
                                    <i class="fa-solid fa-fire text-[9px]"></i>
                                    <span>Most Popular</span>
                                </div>
                            <?php endif; ?>

                            <div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs uppercase tracking-widest <?= $isPop ? 'text-red-400' : 'text-zinc-400' ?> font-bold">
                                        <?= $plan['duration_days'] >= 180 ? 'Semi-Annual VIP' : ($plan['duration_days'] >= 90 ? 'Quarterly Pass' : 'Monthly Pass') ?>
                                    </span>
                                    <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-full <?= $isPop ? 'bg-red-500/20 text-red-400 border border-red-500/30' : 'bg-zinc-800 text-zinc-300' ?>">
                                        <?= (int)$plan['duration_days'] ?> Days
                                    </span>
                                </div>
                                <h3 class="font-heading text-3xl font-bold text-white mt-1 uppercase"><?= htmlspecialchars($plan['name']) ?></h3>
                                
                                <div class="mt-6 flex items-baseline">
                                    <span class="font-heading text-4xl sm:text-5xl font-extrabold text-white">PKR <?= number_format((float)$plan['price']) ?></span>
                                    <span class="text-zinc-400 text-xs ml-2">/ <?= (int)$plan['duration_days'] ?> days</span>
                                </div>
                                <?php if ($durationMonths > 1): ?>
                                    <p class="text-[11px] text-emerald-400 font-semibold mt-1">PKR <?= number_format($monthlyEquiv) ?> / month equivalent</p>
                                <?php endif; ?>
                                <p class="text-xs text-zinc-400 mt-2 pb-6 border-b border-zinc-800"><?= htmlspecialchars($plan['description'] ?? 'Unlimited world-class gym training access.') ?></p>

                                <ul class="space-y-3.5 my-6 text-xs text-zinc-300">
                                    <?php foreach (array_slice($features, 0, 5) as $feat): ?>
                                        <li class="flex items-center gap-3">
                                            <i class="fa-solid fa-check text-red-500 shrink-0"></i>
                                            <span><?= htmlspecialchars($feat) ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>

                            <a href="<?= url('register.php?plan=' . (int)$plan['id']) ?>" class="w-full text-center block <?= $isPop ? 'btn-primary shadow-lg shadow-red-600/40' : 'btn-outline' ?> font-bold uppercase tracking-wider py-3.5 rounded-xl text-xs mt-6">
                                Select <?= htmlspecialchars($plan['name']) ?>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </div>
    </section>

    <!-- ============================================================
         6. CONTACT & LOCATION SECTION
         ============================================================ -->
    <section id="contact" class="py-20 bg-[#070708] relative border-t border-zinc-900">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="glass-card rounded-3xl p-10 md:p-14 relative overflow-hidden bg-gradient-to-r from-[#0d0e12] via-zinc-900 to-[#0d0e12]">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 items-center">
                    <div class="space-y-6">
                        <span class="text-red-500 text-xs font-bold uppercase tracking-[0.25em]">Visit Our Sanctuary</span>
                        <h2 class="font-heading text-4xl sm:text-6xl font-bold text-white tracking-wide uppercase leading-none">
                            GET YOUR FREE 1-DAY <span class="text-red-500">EXPERIENCE PASS</span>
                        </h2>
                        <p class="text-zinc-400 text-sm">
                            Step onto the gym floor, tour our recovery suites, and work out with our elite coaches.
                        </p>
                        
                        <div class="space-y-3 text-xs text-zinc-300">
                            <p class="flex items-center gap-2"><i class="fa-solid fa-location-dot text-red-500"></i> 742 Evergreen Fitness Blvd, Metropolis, NY</p>
                            <p class="flex items-center gap-2"><i class="fa-solid fa-phone text-red-500"></i> +92 300 1234567</p>
                            <p class="flex items-center gap-2"><i class="fa-solid fa-clock text-red-500"></i> Mon - Sun: 05:00 AM - 11:00 PM</p>
                        </div>

                        <div class="pt-2 flex flex-wrap gap-4">
                            <a href="<?= url('pricing.php') ?>" class="btn-primary px-8 py-3.5 rounded-xl text-xs uppercase font-bold tracking-wider">
                                View Passes & Memberships
                            </a>
                            <a href="<?= url('contact.php') ?>" class="btn-outline px-8 py-3.5 rounded-xl text-xs uppercase font-bold tracking-wider inline-flex items-center gap-2">
                                <i class="fa-solid fa-envelope text-red-500"></i>
                                <span>Contact Desk</span>
                            </a>
                        </div>
                    </div>

                    <!-- Map / Location Card Visual -->
                    <div class="bg-black/60 rounded-2xl p-6 border border-zinc-800 space-y-4">
                        <div class="flex items-center justify-between">
                            <h4 class="font-heading text-2xl font-bold text-white uppercase">METROPOLIS HEADQUARTERS</h4>
                            <span class="px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-400 text-xs font-bold">Open Now</span>
                        </div>
                        <div class="h-44 rounded-xl bg-zinc-900 border border-zinc-800 flex flex-col items-center justify-center text-center p-4">
                            <i class="fa-solid fa-map-location-dot text-4xl text-red-500 mb-2"></i>
                            <span class="text-xs font-bold text-white uppercase">Gym Flow Metropolis Center</span>
                            <span class="text-[11px] text-zinc-500 mt-1">Free underground parking available for all active members</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php
// Include Modular Footer Component
require_once __DIR__ . '/components/footer.php';
?>
