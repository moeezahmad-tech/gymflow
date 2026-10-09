<?php
/**
 * GymFlow - Privacy Policy Page
 */
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';

$pageTitle = "Privacy Policy - Data Protection & Security | " . APP_NAME;
$pageDescription = "Read the Gym Flow Privacy Policy detailing how we collect, store, and safeguard your personal information, workout data, and payment details.";
$pageKeywords = "gym privacy policy, data protection, Gym Flow terms, security";

// Modular Head
require_once __DIR__ . '/components/head.php';
// Global Header
require_once __DIR__ . '/components/header.php';
?>

<main class="flex-grow py-16 bg-[#050507]">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="border-b border-zinc-800 pb-8 mb-10">
            <span class="text-red-500 text-xs font-bold uppercase tracking-[0.25em]">Legal & Compliance</span>
            <h1 class="font-heading text-4xl sm:text-6xl font-bold text-white uppercase tracking-wide mt-2">
                PRIVACY <span class="text-red-500">POLICY</span>
            </h1>
            <p class="text-xs text-zinc-400 mt-2">Effective Date: October 1, 2026 • Last Updated: October 2026</p>
        </div>

        <!-- Privacy Content Sections -->
        <div class="glass-card rounded-3xl p-8 sm:p-10 space-y-8 text-sm text-zinc-300 leading-relaxed">
            
            <section class="space-y-3">
                <h3 class="font-heading text-2xl font-bold text-white uppercase">1. Information We Collect</h3>
                <p>When you register for a Gym Flow membership, book fitness classes, or download our mobile tracker, we collect certain personal identification details:</p>
                <ul class="list-disc pl-5 space-y-1.5 text-zinc-400 text-xs">
                    <li>Contact details including full legal name, email address, telephone number, and home address.</li>
                    <li>Billing and payment information processed securely through PCI-DSS certified payment gateways.</li>
                    <li>Health waivers and emergency contact information required for physical training safety.</li>
                    <li>Gym facility check-in timestamps and attendance telemetry.</li>
                </ul>
            </section>

            <section class="space-y-3">
                <h3 class="font-heading text-2xl font-bold text-white uppercase">2. How We Use Your Information</h3>
                <p>Gym Flow utilizes collected information strictly for operational and member service purposes:</p>
                <ul class="list-disc pl-5 space-y-1.5 text-zinc-400 text-xs">
                    <li>To activate and manage your 24/7 keycard entry and locker reservations.</li>
                    <li>To deliver tailored workout plans and communicate class scheduling updates.</li>
                    <li>To issue monthly subscription billing invoices and transaction receipts.</li>
                    <li>To uphold gym safety standards and emergency response protocols.</li>
                </ul>
            </section>

            <section class="space-y-3">
                <h3 class="font-heading text-2xl font-bold text-white uppercase">3. Data Security & Retention</h3>
                <p>We implement bank-grade encryption protocols (TLS 1.3) and secure cloud databases to safeguard personal data. We do NOT sell, rent, or trade your personal records to third-party advertisers under any circumstances.</p>
            </section>

            <section class="space-y-3">
                <h3 class="font-heading text-2xl font-bold text-white uppercase">4. Your Data Rights</h3>
                <p>You may request an export of your fitness records, update billing information, or request account data deletion at any time by contacting our privacy officer at <a href="mailto:privacy@gymflow.com" class="text-red-400 underline">privacy@gymflow.com</a>.</p>
            </section>

        </div>

    </div>
</main>

<?php
require_once __DIR__ . '/components/footer.php';
?>
