<?php
/**
 * GymFlow - Terms & Conditions Page
 */
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';

$pageTitle = "Terms of Service & Gym Code of Conduct | " . APP_NAME;
$pageDescription = "Review the official Gym Flow Terms of Service, membership agreements, cancellation rules, facility safety guidelines, and code of conduct.";
$pageKeywords = "gym terms of service, gym rules, cancellation policy, membership agreement";

// Modular Head
require_once __DIR__ . '/components/head.php';
// Global Header
require_once __DIR__ . '/components/header.php';
?>

<main class="flex-grow py-16 bg-[#050507]">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="border-b border-zinc-800 pb-8 mb-10">
            <span class="text-red-500 text-xs font-bold uppercase tracking-[0.25em]">Rules & Policies</span>
            <h1 class="font-heading text-4xl sm:text-6xl font-bold text-white uppercase tracking-wide mt-2">
                TERMS OF <span class="text-red-500">SERVICE</span>
            </h1>
            <p class="text-xs text-zinc-400 mt-2">Last Updated: October 2026 • Applies to all members & guests</p>
        </div>

        <!-- Terms Content Sections -->
        <div class="glass-card rounded-3xl p-8 sm:p-10 space-y-8 text-sm text-zinc-300 leading-relaxed">
            
            <section class="space-y-3">
                <h3 class="font-heading text-2xl font-bold text-white uppercase">1. Membership & Access</h3>
                <p>Access to Gym Flow facilities is granted solely to registered members possessing valid and active accounts. Membership badges or digital QR passes are strictly non-transferable and may not be loaned or shared with non-members.</p>
            </section>

            <section class="space-y-3">
                <h3 class="font-heading text-2xl font-bold text-white uppercase">2. Facility Code of Conduct & Etiquette</h3>
                <p>To ensure a premier environment for all fitness enthusiasts, every member agrees to:</p>
                <ul class="list-disc pl-5 space-y-1.5 text-zinc-400 text-xs">
                    <li>Re-rack all dumbbells, barbells, and bumper plates in their designated storage racks after use.</li>
                    <li>Wipe down all machinery and benches with provided disinfectant towels after every set.</li>
                    <li>Wear appropriate athletic footwear and attire at all times across training floors.</li>
                    <li>Use barbell collars/clamps on all loaded barbell movements for lifting safety.</li>
                </ul>
            </section>

            <section class="space-y-3">
                <h3 class="font-heading text-2xl font-bold text-white uppercase">3. Billing & Cancellation Policy</h3>
                <p>Memberships renew automatically on a recurring 30-day billing cycle. Members may cancel or freeze their membership at any time with a 14-day notice submitted via the online Member Portal or directly at our front desk.</p>
            </section>

            <section class="space-y-3">
                <h3 class="font-heading text-2xl font-bold text-white uppercase">4. Physical Activity Liability Waiver</h3>
                <p>Members acknowledge that weightlifting, high-intensity cardio, and combative exercises involve inherent physical exertion. Members represent that they are in adequate physical health and assume all voluntary risks associated with training.</p>
            </section>

        </div>

    </div>
</main>

<?php
require_once __DIR__ . '/components/footer.php';
?>
