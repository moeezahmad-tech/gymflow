<?php
/**
 * GymFlow - About Us Page
 */
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';

$pageTitle = "About Us - Our Story, Philosophy & Elite Coaches | " . APP_NAME;
$pageDescription = "Discover the story behind Gym Flow. We provide state-of-the-art training facilities, certified personal trainers, and an inspiring fitness community.";
$pageKeywords = "about gym flow, gym coaches, fitness trainers, gym story, gym culture, best gym metropolis";

// Modular Head
require_once __DIR__ . '/components/head.php';
// Global Header
require_once __DIR__ . '/components/header.php';
?>

<main class="flex-grow">
    <!-- Page Hero Banner -->
    <section class="relative py-20 lg:py-28 bg-black overflow-hidden border-b border-zinc-900">
        <div class="absolute inset-0 z-0 opacity-25 scale-105 bg-cover bg-center filter grayscale contrast-125" style="background-image: url('<?= asset('images/0d931a74f61693ae690eeeac95436444.jpg') ?>');"></div>
        <div class="absolute inset-0 z-0 bg-gradient-to-t from-black via-black/80 to-transparent"></div>
        
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <span class="text-red-500 text-xs font-bold uppercase tracking-[0.25em]">Our Legacy & Vision</span>
            <h1 class="font-heading text-5xl sm:text-7xl font-bold text-white uppercase tracking-wide mt-2">
                FORGED IN <span class="text-red-500">DISCIPLINE</span> & PASSION
            </h1>
            <p class="mt-4 text-zinc-300 max-w-2xl mx-auto text-base sm:text-lg">
                Since our inception, Gym Flow has been built around one core premise: creating an elite training sanctuary where individuals transform potential into physical dominance.
            </p>
        </div>
    </section>

    <!-- The Gym Flow Story & Core Values -->
    <section class="py-20 bg-[#070708]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                
                <!-- Left Visual Showcase -->
                <div class="lg:col-span-6 relative">
                    <div class="rounded-3xl overflow-hidden border border-zinc-800 shadow-2xl relative group">
                        <img src="<?= asset('images/3844e94a9c5f8420999736d4aa364aac.jpg') ?>" 
                             alt="Gym Flow Coaching and Training" 
                             class="w-full h-96 sm:h-[480px] object-cover filter grayscale contrast-125 group-hover:scale-105 transition-transform duration-700">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent"></div>
                        <div class="absolute bottom-6 left-6 right-6 p-6 rounded-2xl bg-black/80 backdrop-blur-md border border-zinc-800">
                            <span class="font-heading text-2xl font-bold text-white uppercase">State-of-the-Art Training Facility</span>
                            <p class="text-xs text-zinc-400 mt-1">Over 25,000 sq. ft. of heavy iron, cardio turf, combat ring, and recovery suites.</p>
                        </div>
                    </div>
                </div>

                <!-- Right Narrative Content -->
                <div class="lg:col-span-6 space-y-6">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-red-600/10 border border-red-500/20 text-red-500 text-xs font-bold uppercase tracking-widest">
                        <i class="fa-solid fa-medal"></i> World-Class Standard
                    </div>

                    <h2 class="font-heading text-4xl sm:text-5xl font-bold text-white uppercase tracking-wide leading-tight">
                        NOT JUST A GYM. <br><span class="text-red-500">A HIGH-PERFORMANCE ECOSYSTEM.</span>
                    </h2>

                    <p class="text-zinc-400 text-sm leading-relaxed">
                        At Gym Flow, we reject the one-size-fits-all workout methodology. Whether you are a professional athlete, powerlifter, or embarking on your first workout, we provide the coaching science, high-caliber equipment, and community support to exceed every target.
                    </p>

                    <!-- Core Pillars -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                        <div class="glass-card p-5 rounded-2xl">
                            <div class="w-10 h-10 rounded-xl bg-red-600/10 text-red-500 flex items-center justify-center text-lg mb-3">
                                <i class="fa-solid fa-dumbbell"></i>
                            </div>
                            <h4 class="font-heading text-xl font-bold text-white uppercase">Calibrated Equipment</h4>
                            <p class="text-xs text-zinc-400 mt-1">Olympic competition barbells, calibrated plates, and precision biomechanical machines.</p>
                        </div>

                        <div class="glass-card p-5 rounded-2xl">
                            <div class="w-10 h-10 rounded-xl bg-red-600/10 text-red-500 flex items-center justify-center text-lg mb-3">
                                <i class="fa-solid fa-heart-pulse"></i>
                            </div>
                            <h4 class="font-heading text-xl font-bold text-white uppercase">Scientifically Backed</h4>
                            <p class="text-xs text-zinc-400 mt-1">Periodized strength programming, heart-rate zone tracking, and bio-marker assessments.</p>
                        </div>

                        <div class="glass-card p-5 rounded-2xl">
                            <div class="w-10 h-10 rounded-xl bg-red-600/10 text-red-500 flex items-center justify-center text-lg mb-3">
                                <i class="fa-solid fa-users-gear"></i>
                            </div>
                            <h4 class="font-heading text-xl font-bold text-white uppercase">Master Coaches</h4>
                            <p class="text-xs text-zinc-400 mt-1">CSCS and NASM certified coaches with real competitive lifting and combat backgrounds.</p>
                        </div>

                        <div class="glass-card p-5 rounded-2xl">
                            <div class="w-10 h-10 rounded-xl bg-red-600/10 text-red-500 flex items-center justify-center text-lg mb-3">
                                <i class="fa-solid fa-spa"></i>
                            </div>
                            <h4 class="font-heading text-xl font-bold text-white uppercase">Active Recovery</h4>
                            <p class="text-xs text-zinc-400 mt-1">Finnish cedar saunas, cold plunge baths, and percussive therapy zones.</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Certified Coaches Section -->
    <section class="py-20 bg-black border-t border-zinc-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <span class="text-red-500 text-xs font-bold uppercase tracking-[0.25em]">Master Instructors</span>
                <h2 class="font-heading text-4xl sm:text-6xl font-bold text-white uppercase tracking-wide mt-2">
                    MEET OUR <span class="text-red-500">HEAD COACHES</span>
                </h2>
                <p class="text-zinc-400 text-sm mt-3">Experienced leaders committed to coaching your progression safely and effectively.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                
                <!-- Trainer 1 -->
                <div class="glass-card rounded-2xl overflow-hidden group">
                    <div class="h-80 overflow-hidden relative">
                        <img src="<?= asset('images/3dba86670044f42a9451abaef5ca07f4.jpg') ?>" 
                             alt="Marcus Vance - Combat & Conditioning Coach" 
                             class="w-full h-full object-cover filter grayscale contrast-125 group-hover:scale-105 transition-transform duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-[#0f1013] via-transparent to-transparent"></div>
                    </div>
                    <div class="p-6">
                        <span class="text-xs uppercase tracking-widest text-red-400 font-bold">Combat & HIIT Specialist</span>
                        <h3 class="font-heading text-2xl font-bold text-white mt-1">MARCUS VANCE</h3>
                        <p class="text-xs text-zinc-400 mt-2">Former Golden Gloves boxing finalist with 10+ years of high-intensity conditioning coaching.</p>
                    </div>
                </div>

                <!-- Trainer 2 -->
                <div class="glass-card rounded-2xl overflow-hidden group">
                    <div class="h-80 overflow-hidden relative">
                        <img src="<?= asset('images/0d931a74f61693ae690eeeac95436444.jpg') ?>" 
                             alt="Elena Rostova - Strength & Hypertrophy" 
                             class="w-full h-full object-cover filter grayscale contrast-125 group-hover:scale-105 transition-transform duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-[#0f1013] via-transparent to-transparent"></div>
                    </div>
                    <div class="p-6">
                        <span class="text-xs uppercase tracking-widest text-red-400 font-bold">Strength & Hypertrophy Coach</span>
                        <h3 class="font-heading text-2xl font-bold text-white mt-1">ELENA ROSTOVA</h3>
                        <p class="text-xs text-zinc-400 mt-2">Competitive powerlifter specializing in biomechanics, barbell periodization, and injury prevention.</p>
                    </div>
                </div>

                <!-- Trainer 3 -->
                <div class="glass-card rounded-2xl overflow-hidden group">
                    <div class="h-80 overflow-hidden relative">
                        <img src="<?= asset('images/3c38ea4f1e502963d98766fd1ae27d25.jpg') ?>" 
                             alt="David Thorne - Athletic Performance" 
                             class="w-full h-full object-cover filter grayscale contrast-125 group-hover:scale-105 transition-transform duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-[#0f1013] via-transparent to-transparent"></div>
                    </div>
                    <div class="p-6">
                        <span class="text-xs uppercase tracking-widest text-red-400 font-bold">Mobility & Functional Fitness</span>
                        <h3 class="font-heading text-2xl font-bold text-white mt-1">DAVID THORNE</h3>
                        <p class="text-xs text-zinc-400 mt-2">Certified strength and conditioning specialist focused on explosive speed, core power, and joint health.</p>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-16 bg-gradient-to-r from-zinc-950 via-red-950/40 to-zinc-950 border-t border-zinc-900 text-center">
        <div class="max-w-4xl mx-auto px-4">
            <h2 class="font-heading text-4xl sm:text-5xl font-bold text-white uppercase">READY TO TRAIN WITH THE BEST?</h2>
            <p class="text-zinc-300 text-sm mt-3 max-w-xl mx-auto">Claim your trial session and tour our world-class fitness facilities today.</p>
            <div class="mt-8 flex justify-center gap-4">
                <a href="<?= url('pricing.php') ?>" class="btn-primary text-xs uppercase font-bold tracking-wider px-8 py-3.5 rounded-xl">View Memberships</a>
                <a href="<?= url('contact.php') ?>" class="btn-outline text-xs uppercase font-bold tracking-wider px-8 py-3.5 rounded-xl">Contact Coaches</a>
            </div>
        </div>
    </section>
</main>

<?php
require_once __DIR__ . '/components/footer.php';
?>
