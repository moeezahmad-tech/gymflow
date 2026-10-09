<?php
/**
 * GymFlow - Membership Plans & Pricing
 */
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';

$pageTitle = "Membership Plans & Transparent Pricing | " . APP_NAME;
$pageDescription = "Simple, transparent gym memberships with no hidden maintenance fees. Select Basic, Pro Athlete, or VIP Elite with month-to-month flexibility.";
$pageKeywords = "gym membership cost, gym pricing, cheap gym plans, VIP fitness membership, no contract gym";

// Modular Head
require_once __DIR__ . '/components/head.php';
// Global Header
require_once __DIR__ . '/components/header.php';
?>

<main class="flex-grow">
    <!-- Hero Banner -->
    <section class="relative py-20 lg:py-24 bg-black overflow-hidden border-b border-zinc-900">
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <span class="text-red-500 text-xs font-bold uppercase tracking-[0.25em]">Transparent Pricing</span>
            <h1 class="font-heading text-5xl sm:text-7xl font-bold text-white uppercase tracking-wide mt-2">
                MEMBERSHIP <span class="text-red-500">TIERS</span>
            </h1>
            <p class="mt-4 text-zinc-300 max-w-2xl mx-auto text-base sm:text-lg">
                No hidden initiation costs. No annual maintenance trickery. Just world-class training access and results.
            </p>

            <!-- 1-Day Free Pass CTA Action -->
            <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
                <a href="<?= url('contact.php?program=Free+1-Day+Trial+Pass+%26+Tour') ?>" class="btn-primary text-sm sm:text-base font-bold uppercase tracking-wider px-8 py-4 rounded-xl shadow-xl shadow-red-600/30 inline-flex items-center gap-2.5">
                    <i class="fa-solid fa-ticket text-white"></i>
                    <span>Claim 1-Day Free Pass</span>
                </a>
                <a href="#plans" class="btn-outline text-sm sm:text-base font-bold uppercase tracking-wider px-8 py-4 rounded-xl inline-flex items-center gap-2">
                    <span>View All Plans</span>
                    <i class="fa-solid fa-arrow-down text-xs"></i>
                </a>
            </div>
        </div>
    </section>

<?php
// Load dynamic plans from database with reliable fallback
$dbPlans = [];
try {
    $db = getDB();
    $stmtP = $db->query("SELECT * FROM membership_plans WHERE status = 'active' ORDER BY duration_days ASC");
    $dbPlans = $stmtP->fetchAll();
} catch (Exception $e) {
    $dbPlans = [];
}
?>

    <!-- Pricing Cards Section -->
    <section id="plans" class="py-20 bg-[#070708]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-stretch">
                <?php if (!empty($dbPlans)): ?>
                    <?php foreach ($dbPlans as $plan): 
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
                                    <?php foreach ($features as $feat): ?>
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

    <!-- FAQ Section -->
    <section class="py-20 bg-black border-t border-zinc-900">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <span class="text-red-500 text-xs font-bold uppercase tracking-[0.25em]">Clear Answers</span>
                <h2 class="font-heading text-4xl sm:text-5xl font-bold text-white uppercase mt-2">
                    FREQUENTLY ASKED <span class="text-red-500">QUESTIONS</span>
                </h2>
            </div>

            <div class="space-y-4">
                <div class="glass-card rounded-xl p-5">
                    <h4 class="font-heading text-xl font-bold text-white uppercase">What payment methods are accepted in PKR?</h4>
                    <p class="text-xs text-zinc-400 mt-1">We accept Cash, Debit/Credit cards, JazzCash, EasyPaisa, and Direct Bank Transfers at the front desk or through your member dashboard.</p>
                </div>

                <div class="glass-card rounded-xl p-5">
                    <h4 class="font-heading text-xl font-bold text-white uppercase">How do the multi-month packages save money?</h4>
                    <p class="text-xs text-zinc-400 mt-1">The 1-month package is PKR 3,000. With our 3-month package at PKR 8,000 you instantly save PKR 1,000. With our 6-month package at PKR 15,000 you save PKR 3,000!</p>
                </div>

                <div class="glass-card rounded-xl p-5">
                    <h4 class="font-heading text-xl font-bold text-white uppercase">Can I try the gym before committing?</h4>
                    <p class="text-xs text-zinc-400 mt-1">Yes! We provide a 1-day complimentary experience pass for all first-time visitors. You can tour the facility, lift, and try any class.</p>
                </div>

                <div class="glass-card rounded-xl p-5">
                    <h4 class="font-heading text-xl font-bold text-white uppercase">Are group workout classes included?</h4>
                    <p class="text-xs text-zinc-400 mt-1">Group fitness classes (Boxing, HIIT, and Hypertrophy labs) are fully included in our 3-month and 6-month membership packages.</p>
                </div>
            </div>
        </div>
    </section>
</main>

<?php
require_once __DIR__ . '/components/footer.php';
?>
