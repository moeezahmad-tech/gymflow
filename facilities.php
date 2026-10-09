<?php
/**
 * GymFlow - Facilities & Equipment
 */
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';

$pageTitle = "Facilities & Equipment - Premium Training Floors | " . APP_NAME;
$pageDescription = "Take a look inside Gym Flow: Eleiko Olympic platforms, custom dumbbell racks up to 150 lbs, turf track, Finnish saunas, and luxury locker suites.";
$pageKeywords = "gym equipment, eleiko barbells, gym facilities, sauna, cold plunge, turf track, luxury gym";

// Modular Head
require_once __DIR__ . '/components/head.php';
// Global Header
require_once __DIR__ . '/components/header.php';
?>

<main class="flex-grow">
    <!-- Hero Banner -->
    <section class="relative py-20 lg:py-24 bg-black overflow-hidden border-b border-zinc-900">
        <div class="absolute inset-0 z-0 opacity-25 scale-105 bg-cover bg-center filter grayscale contrast-125" style="background-image: url('<?= asset('images/3844e94a9c5f8420999736d4aa364aac.jpg') ?>');"></div>
        <div class="absolute inset-0 z-0 bg-gradient-to-t from-black via-black/85 to-transparent"></div>

        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <span class="text-red-500 text-xs font-bold uppercase tracking-[0.25em]">25,000 Sq. Ft. of Pure Performance</span>
            <h1 class="font-heading text-5xl sm:text-7xl font-bold text-white uppercase tracking-wide mt-2">
                FACILITIES & <span class="text-red-500">EQUIPMENT</span>
            </h1>
            <p class="mt-4 text-zinc-300 max-w-2xl mx-auto text-base sm:text-lg">
                Engineered with industry-standard competition equipment to give athletes, bodybuilders, and fitness lovers the ultimate competitive edge.
            </p>
        </div>
    </section>

    <!-- 4 Major Zones Grid -->
    <section class="py-20 bg-[#070708]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-16">
            
            <!-- Zone 1: Strength Floor -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
                <div class="lg:col-span-6 rounded-2xl overflow-hidden border border-zinc-800 shadow-2xl">
                    <img src="<?= asset('images/0d931a74f61693ae690eeeac95436444.jpg') ?>" 
                         alt="Heavy Dumbbells and Barbell Arena" 
                         class="w-full h-80 sm:h-96 object-cover filter grayscale contrast-125 hover:scale-105 transition-transform duration-500">
                </div>
                <div class="lg:col-span-6 space-y-4">
                    <span class="text-red-500 text-xs font-bold uppercase tracking-widest">Zone 01</span>
                    <h2 class="font-heading text-4xl sm:text-5xl font-bold text-white uppercase">The Heavy Iron Arena</h2>
                    <p class="text-zinc-400 text-sm leading-relaxed">
                        Featuring 8 dedicated Eleiko power racks, calibrated steel competition plates, deadlift platforms with band pegs, and dumbbells ranging from 5 lbs to 150 lbs.
                    </p>
                    <ul class="space-y-2 text-xs text-zinc-300">
                        <li class="flex items-center gap-2"><i class="fa-solid fa-circle-check text-red-500"></i> Calibrated Olympic Barbells & Specialty Bars (Safety Squat, Trap Bar)</li>
                        <li class="flex items-center gap-2"><i class="fa-solid fa-circle-check text-red-500"></i> Dual adjustable pulley cable stations & plate-loaded machines</li>
                        <li class="flex items-center gap-2"><i class="fa-solid fa-circle-check text-red-500"></i> Heavy-duty rubberized impact flooring</li>
                    </ul>
                </div>
            </div>

            <!-- Zone 2: Combat & Sprint Turf -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center lg:flex-row-reverse">
                <div class="lg:col-span-6 lg:order-2 rounded-2xl overflow-hidden border border-zinc-800 shadow-2xl">
                    <img src="<?= asset('images/3dba86670044f42a9451abaef5ca07f4.jpg') ?>" 
                         alt="Boxing ring and turf sprint track" 
                         class="w-full h-80 sm:h-96 object-cover filter grayscale contrast-125 hover:scale-105 transition-transform duration-500">
                </div>
                <div class="lg:col-span-6 lg:order-1 space-y-4">
                    <span class="text-red-500 text-xs font-bold uppercase tracking-widest">Zone 02</span>
                    <h2 class="font-heading text-4xl sm:text-5xl font-bold text-white uppercase">Combat Ring & Sprint Turf</h2>
                    <p class="text-zinc-400 text-sm leading-relaxed">
                        A full-size 20ft regulation boxing ring, Aqua heavy bags, speed bags, and a 40-yard athletic sprint turf for sled pushes and farmer walks.
                    </p>
                    <ul class="space-y-2 text-xs text-zinc-300">
                        <li class="flex items-center gap-2"><i class="fa-solid fa-circle-check text-red-500"></i> Professional heavy bags, teardrop bags & reflex balls</li>
                        <li class="flex items-center gap-2"><i class="fa-solid fa-circle-check text-red-500"></i> Weighted sleds, battle ropes, and plyometric boxes</li>
                        <li class="flex items-center gap-2"><i class="fa-solid fa-circle-check text-red-500"></i> Agility ladders, medicine balls & kettlebell racks</li>
                    </ul>
                </div>
            </div>

            <!-- Zone 3: Recovery Suite -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
                <div class="lg:col-span-6 rounded-2xl overflow-hidden border border-zinc-800 shadow-2xl">
                    <img src="<?= asset('images/3c38ea4f1e502963d98766fd1ae27d25.jpg') ?>" 
                         alt="Recovery Suite Sauna and Ice Baths" 
                         class="w-full h-80 sm:h-96 object-cover filter grayscale contrast-125 hover:scale-105 transition-transform duration-500">
                </div>
                <div class="lg:col-span-6 space-y-4">
                    <span class="text-red-500 text-xs font-bold uppercase tracking-widest">Zone 03</span>
                    <h2 class="font-heading text-4xl sm:text-5xl font-bold text-white uppercase">Thermal Recovery Lounge</h2>
                    <p class="text-zinc-400 text-sm leading-relaxed">
                        Science proves that recovery is where adaptation happens. Speed up muscle repair, reduce soreness, and revitalize your central nervous system.
                    </p>
                    <ul class="space-y-2 text-xs text-zinc-300">
                        <li class="flex items-center gap-2"><i class="fa-solid fa-circle-check text-red-500"></i> Cedarwood dry Finnish saunas (195°F) & steam rooms</li>
                        <li class="flex items-center gap-2"><i class="fa-solid fa-circle-check text-red-500"></i> Precision-temperature ice cold plunge tubs (42°F - 48°F)</li>
                        <li class="flex items-center gap-2"><i class="fa-solid fa-circle-check text-red-500"></i> Hyperice & Theragun percussion massage stations</li>
                    </ul>
                </div>
            </div>

        </div>
    </section>

    <!-- Tour Banner -->
    <section class="py-16 bg-black border-t border-zinc-900 text-center">
        <div class="max-w-4xl mx-auto px-4">
            <h2 class="font-heading text-4xl sm:text-5xl font-bold text-white uppercase">Experience the Facility in Person</h2>
            <p class="text-zinc-400 text-sm mt-2">Book a free walkthrough with one of our staff coaches today.</p>
            <div class="mt-6">
                <a href="<?= url('contact.php') ?>" class="btn-primary text-xs uppercase font-bold tracking-wider px-8 py-3.5 rounded-xl inline-flex items-center gap-2">
                    <i class="fa-regular fa-calendar"></i>
                    <span>Schedule Gym Tour</span>
                </a>
            </div>
        </div>
    </section>
</main>

<?php
require_once __DIR__ . '/components/footer.php';
?>
