<?php
/**
 * GymFlow - Training Programs & Classes
 */
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';

$pageTitle = "Training Programs & Class Schedules | " . APP_NAME;
$pageDescription = "Explore our elite training programs including Olympic Barbell, High-Intensity HIIT, Combat Boxing, CrossFit conditioning, and Mobility Yoga.";
$pageKeywords = "gym programs, fitness classes, boxing classes, crossfit, powerlifting, yoga, schedule metropolis";

// Modular Head
require_once __DIR__ . '/components/head.php';
// Global Header
require_once __DIR__ . '/components/header.php';
?>

<main class="flex-grow">
    <!-- Hero Banner -->
    <section class="relative py-20 lg:py-24 bg-black overflow-hidden border-b border-zinc-900">
        <div class="absolute inset-0 z-0 opacity-25 scale-105 bg-cover bg-center filter grayscale contrast-125" style="background-image: url('<?= asset('images/3dba86670044f42a9451abaef5ca07f4.jpg') ?>');"></div>
        <div class="absolute inset-0 z-0 bg-gradient-to-t from-black via-black/85 to-transparent"></div>

        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <span class="text-red-500 text-xs font-bold uppercase tracking-[0.25em]">Elevate Your Capacity</span>
            <h1 class="font-heading text-5xl sm:text-7xl font-bold text-white uppercase tracking-wide mt-2">
                ELITE TRAINING <span class="text-red-500">PROGRAMS</span>
            </h1>
            <p class="mt-4 text-zinc-300 max-w-2xl mx-auto text-base sm:text-lg">
                Structured workout regimens engineered by sports science professionals for peak strength, stamina, and physical longevity.
            </p>
        </div>
    </section>

    <!-- Program Categories Grid -->
    <section class="py-20 bg-[#070708]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                
                <!-- Program 1 -->
                <div class="glass-card rounded-2xl overflow-hidden group">
                    <div class="h-60 overflow-hidden relative">
                        <img src="<?= asset('images/0d931a74f61693ae690eeeac95436444.jpg') ?>" 
                             alt="Heavy Barbell Strength" 
                             class="w-full h-full object-cover filter grayscale contrast-125 group-hover:scale-105 transition-transform duration-500">
                        <div class="absolute top-4 left-4 bg-red-600 text-white text-[10px] font-bold uppercase px-3 py-1 rounded-full">
                            Strength & Mass
                        </div>
                    </div>
                    <div class="p-6 space-y-3">
                        <h3 class="font-heading text-2xl font-bold text-white uppercase">Powerlifting & Hypertrophy</h3>
                        <p class="text-xs text-zinc-400 leading-relaxed">
                            Focus on the big three lifts (Squat, Bench, Deadlift) and accessory movements to maximize muscle tension and pure strength.
                        </p>
                        <div class="pt-2 border-t border-zinc-800/80 flex items-center justify-between text-xs text-zinc-400">
                            <span><i class="fa-solid fa-clock text-red-500"></i> 60 Mins</span>
                            <span><i class="fa-solid fa-gauge-high text-red-500"></i> Advanced</span>
                        </div>
                    </div>
                </div>

                <!-- Program 2 -->
                <div class="glass-card rounded-2xl overflow-hidden group">
                    <div class="h-60 overflow-hidden relative">
                        <img src="<?= asset('images/3dba86670044f42a9451abaef5ca07f4.jpg') ?>" 
                             alt="Combat Boxing & Striking" 
                             class="w-full h-full object-cover filter grayscale contrast-125 group-hover:scale-105 transition-transform duration-500">
                        <div class="absolute top-4 left-4 bg-red-600 text-white text-[10px] font-bold uppercase px-3 py-1 rounded-full">
                            Combat Conditioning
                        </div>
                    </div>
                    <div class="p-6 space-y-3">
                        <h3 class="font-heading text-2xl font-bold text-white uppercase">Boxing & Striking Combatives</h3>
                        <p class="text-xs text-zinc-400 leading-relaxed">
                            Heavy bag combinations, footwork agility, and mitt-work drills for self-defense, coordination, and fat loss.
                        </p>
                        <div class="pt-2 border-t border-zinc-800/80 flex items-center justify-between text-xs text-zinc-400">
                            <span><i class="fa-solid fa-clock text-red-500"></i> 45 Mins</span>
                            <span><i class="fa-solid fa-gauge-high text-red-500"></i> All Levels</span>
                        </div>
                    </div>
                </div>

                <!-- Program 3 -->
                <div class="glass-card rounded-2xl overflow-hidden group">
                    <div class="h-60 overflow-hidden relative">
                        <img src="<?= asset('images/3c38ea4f1e502963d98766fd1ae27d25.jpg') ?>" 
                             alt="HIIT & Cardio Shred" 
                             class="w-full h-full object-cover filter grayscale contrast-125 group-hover:scale-105 transition-transform duration-500">
                        <div class="absolute top-4 left-4 bg-red-600 text-white text-[10px] font-bold uppercase px-3 py-1 rounded-full">
                            High Intensity
                        </div>
                    </div>
                    <div class="p-6 space-y-3">
                        <h3 class="font-heading text-2xl font-bold text-white uppercase">Cardio Blast & Core Blitz</h3>
                        <p class="text-xs text-zinc-400 leading-relaxed">
                            Full-body metabolic intervals using assault bikes, rowing ergometers, plyometrics, and functional core circuits.
                        </p>
                        <div class="pt-2 border-t border-zinc-800/80 flex items-center justify-between text-xs text-zinc-400">
                            <span><i class="fa-solid fa-clock text-red-500"></i> 45 Mins</span>
                            <span><i class="fa-solid fa-gauge-high text-red-500"></i> Intermediate</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Weekly Schedule Table (Commented Out)
    <section class="py-20 bg-black border-t border-zinc-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-14">
                <span class="text-red-500 text-xs font-bold uppercase tracking-[0.25em]">Class Timetable</span>
                <h2 class="font-heading text-4xl sm:text-5xl font-bold text-white uppercase tracking-wide mt-2">
                    WEEKLY <span class="text-red-500">SCHEDULE</span>
                </h2>
                <p class="text-zinc-400 text-sm mt-3">All group classes are included free for Pro Athlete & Elite members.</p>
            </div>

            <div class="glass-card rounded-2xl overflow-x-auto">
                <table class="w-full text-left text-xs sm:text-sm text-zinc-300">
                    <thead class="bg-zinc-900 text-zinc-400 uppercase font-heading text-base tracking-wider border-b border-zinc-800">
                        <tr>
                            <th class="py-4 px-6">Time Slot</th>
                            <th class="py-4 px-6">Monday</th>
                            <th class="py-4 px-6">Wednesday</th>
                            <th class="py-4 px-6">Friday</th>
                            <th class="py-4 px-6">Saturday</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-800/60">
                        <tr class="hover:bg-zinc-900/40 transition-colors">
                            <td class="py-4 px-6 font-bold text-red-400">06:00 AM</td>
                            <td class="py-4 px-6"><span class="font-semibold text-white">Cardio Shred</span><br><span class="text-xs text-zinc-500">Coach David</span></td>
                            <td class="py-4 px-6"><span class="font-semibold text-white">Power Barbell</span><br><span class="text-xs text-zinc-500">Coach Elena</span></td>
                            <td class="py-4 px-6"><span class="font-semibold text-white">Cardio Shred</span><br><span class="text-xs text-zinc-500">Coach David</span></td>
                            <td class="py-4 px-6"><span class="font-semibold text-white">Open Gym / HIIT</span><br><span class="text-xs text-zinc-500">All Coaches</span></td>
                        </tr>
                        <tr class="hover:bg-zinc-900/40 transition-colors">
                            <td class="py-4 px-6 font-bold text-red-400">12:00 PM</td>
                            <td class="py-4 px-6"><span class="font-semibold text-white">Boxing Drills</span><br><span class="text-xs text-zinc-500">Coach Marcus</span></td>
                            <td class="py-4 px-6"><span class="font-semibold text-white">Core & Mobility</span><br><span class="text-xs text-zinc-500">Coach David</span></td>
                            <td class="py-4 px-6"><span class="font-semibold text-white">Boxing Drills</span><br><span class="text-xs text-zinc-500">Coach Marcus</span></td>
                            <td class="py-4 px-6"><span class="font-semibold text-white">Olympic Lifting</span><br><span class="text-xs text-zinc-500">Coach Elena</span></td>
                        </tr>
                        <tr class="hover:bg-zinc-900/40 transition-colors">
                            <td class="py-4 px-6 font-bold text-red-400">06:30 PM</td>
                            <td class="py-4 px-6"><span class="font-semibold text-white">Hypertrophy Lab</span><br><span class="text-xs text-zinc-500">Coach Elena</span></td>
                            <td class="py-4 px-6"><span class="font-semibold text-white">Combat Striking</span><br><span class="text-xs text-zinc-500">Coach Marcus</span></td>
                            <td class="py-4 px-6"><span class="font-semibold text-white">Hypertrophy Lab</span><br><span class="text-xs text-zinc-500">Coach Elena</span></td>
                            <td class="py-4 px-6"><span class="font-semibold text-white">Recovery Yoga</span><br><span class="text-xs text-zinc-500">Coach Sarah</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="text-center mt-8">
                <a href="<?= url('pricing.php') ?>" class="btn-primary text-xs uppercase font-bold tracking-wider px-8 py-3.5 rounded-xl inline-flex items-center gap-2">
                    <span>Unlock Full Schedule</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>
        </div>
    </section>
    -->
</main>

<?php
require_once __DIR__ . '/components/footer.php';
?>
