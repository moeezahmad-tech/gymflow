<?php
/**
 * GymFlow - Contact & Location Page
 */
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';

$successMsg = null;
$errorMsg = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errorMsg = "Security token mismatch or session expired. Please refresh and try again.";
    } else {
        $fullName = trim($_POST['full_name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $phone    = trim($_POST['phone'] ?? '');
        $program  = trim($_POST['program'] ?? 'General Membership Inquiry');
        $message  = trim($_POST['message'] ?? '');

        if (empty($fullName) || empty($email) || empty($message)) {
            $errorMsg = "Please fill in all required fields (Name, Email, and Message).";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorMsg = "Please provide a valid email address.";
        } else {
            try {
                $db = Database::getConnection();
                $stmt = $db->prepare("
                    INSERT INTO contact_messages (full_name, email, phone, program, message, status, created_at)
                    VALUES (:name, :email, :phone, :program, :msg, 'unread', NOW())
                ");
                $stmt->execute([
                    ':name'    => $fullName,
                    ':email'   => $email,
                    ':phone'   => $phone,
                    ':program' => $program,
                    ':msg'     => $message
                ]);
                $successMsg = "Thank you, " . htmlspecialchars($fullName) . "! Your inquiry has been received. Our team will get back to you shortly.";
            } catch (Exception $e) {
                error_log("Contact form DB error: " . $e->getMessage());
                $errorMsg = "An error occurred while submitting your message. Please try again or reach out directly.";
            }
        }
    }
}

$pageTitle = "Contact Us & Location Directions | " . APP_NAME;
$pageDescription = "Get in touch with Gym Flow. Find our gym address, operating hours, phone numbers, and send a direct inquiry to our coaching staff.";
$pageKeywords = "contact gym flow, gym location, gym hours, gym customer support, fitness center address metropolis";

// Modular Head
require_once __DIR__ . '/components/head.php';
// Global Header
require_once __DIR__ . '/components/header.php';
?>

<main class="flex-grow">
    <!-- Hero Banner -->
    <section class="relative py-20 lg:py-24 bg-black overflow-hidden border-b border-zinc-900">
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <span class="text-red-500 text-xs font-bold uppercase tracking-[0.25em]">We're Here For You</span>
            <h1 class="font-heading text-5xl sm:text-7xl font-bold text-white uppercase tracking-wide mt-2">
                GET IN <span class="text-red-500">TOUCH</span>
            </h1>
            <p class="mt-4 text-zinc-300 max-w-2xl mx-auto text-base sm:text-lg">
                Have questions about memberships, private coaching, or group workouts? Stop by or reach out below.
            </p>
        </div>
    </section>

    <!-- Main Contact & Location Layout -->
    <section class="py-20 bg-[#070708]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
                
                <!-- Left: Interactive Contact Form -->
                <div class="lg:col-span-7">
                    <div class="glass-card rounded-3xl p-8 sm:p-10 border border-zinc-800 shadow-2xl">
                        <h2 class="font-heading text-3xl font-bold text-white uppercase mb-2">Send Us A Message</h2>
                        <p class="text-xs text-zinc-400 mb-8">Our front desk team responds to all inquiries within 2 business hours.</p>

                        <?php if ($successMsg): ?>
                            <div class="p-4 rounded-2xl bg-emerald-950/80 border border-emerald-800 text-emerald-300 text-sm flex items-center justify-between mb-6 shadow-xl">
                                <div class="flex items-center gap-3">
                                    <i class="fa-solid fa-circle-check text-emerald-400 text-xl flex-shrink-0"></i>
                                    <span><?= $successMsg ?></span>
                                </div>
                                <button onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-white px-2 py-1"><i class="fa-solid fa-xmark"></i></button>
                            </div>
                        <?php elseif ($errorMsg): ?>
                            <div class="p-4 rounded-2xl bg-red-950/80 border border-red-800 text-red-300 text-sm flex items-center justify-between mb-6 shadow-xl">
                                <div class="flex items-center gap-3">
                                    <i class="fa-solid fa-triangle-exclamation text-red-400 text-xl flex-shrink-0"></i>
                                    <span><?= htmlspecialchars($errorMsg) ?></span>
                                </div>
                                <button onclick="this.parentElement.remove()" class="text-red-400 hover:text-white px-2 py-1"><i class="fa-solid fa-xmark"></i></button>
                            </div>
                        <?php endif; ?>

                        <form id="contactForm" action="<?= url('contact.php') ?>" method="POST" class="space-y-5">
                            <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-zinc-300 mb-2">Full Name <span class="text-red-500">*</span></label>
                                    <input type="text" name="full_name" required value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" placeholder="John Doe" class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-4 py-3 text-sm text-white placeholder-zinc-500 focus:outline-none focus:border-red-500 transition-colors">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-zinc-300 mb-2">Email Address <span class="text-red-500">*</span></label>
                                    <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="john@example.com" class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-4 py-3 text-sm text-white placeholder-zinc-500 focus:outline-none focus:border-red-500 transition-colors">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-zinc-300 mb-2">Phone Number</label>
                                    <input type="tel" name="phone" list="pk-phone-suggestions" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" placeholder="+92 300 1234567" class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-4 py-3 text-sm text-white placeholder-zinc-500 focus:outline-none focus:border-red-500 transition-colors">
                                    <datalist id="pk-phone-suggestions">
                                        <option value="+92 300 ">Jazz / Mobilink (+92 300)</option>
                                        <option value="+92 301 ">Jazz (+92 301)</option>
                                        <option value="+92 321 ">Warid (+92 321)</option>
                                        <option value="+92 333 ">Ufone (+92 333)</option>
                                        <option value="+92 345 ">Telenor (+92 345)</option>
                                        <option value="+92 312 ">Zong (+92 312)</option>
                                    </datalist>
                                </div>
                                <div>
                                    <?php $selectedProg = $_POST['program'] ?? $_GET['program'] ?? ''; ?>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-zinc-300 mb-2">Interested Program</label>
                                    <select name="program" class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-red-500 transition-colors">
                                        <option value="General Membership Inquiry" <?= ($selectedProg === 'General Membership Inquiry') ? 'selected' : '' ?>>General Membership Inquiry</option>
                                        <option value="1-on-1 Personal Training" <?= ($selectedProg === '1-on-1 Personal Training') ? 'selected' : '' ?>>1-on-1 Personal Training</option>
                                        <option value="Combat & Boxing Classes" <?= ($selectedProg === 'Combat & Boxing Classes') ? 'selected' : '' ?>>Combat & Boxing Classes</option>
                                        <option value="Free 1-Day Trial Pass & Tour" <?= (strpos($selectedProg, '1-Day') !== false || $selectedProg === 'Free 1-Day Trial Pass & Tour') ? 'selected' : '' ?>>Free 1-Day Trial Pass & Tour</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-zinc-300 mb-2">Your Message <span class="text-red-500">*</span></label>
                                <textarea name="message" rows="4" required placeholder="Tell us about your fitness goals or questions..." class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-4 py-3 text-sm text-white placeholder-zinc-500 focus:outline-none focus:border-red-500 transition-colors"><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                            </div>

                            <button type="submit" id="submitBtn" class="btn-primary w-full py-3.5 rounded-xl font-bold uppercase tracking-wider text-xs flex items-center justify-center gap-2 shadow-lg shadow-red-600/30">
                                <span>Send Inquiry</span>
                                <i class="fa-solid fa-paper-plane text-[11px]"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Right: Information & Direct Channels -->
                <div class="lg:col-span-5 space-y-6">
                    
                    <!-- Contact Info Card -->
                    <div class="glass-card rounded-2xl p-6 space-y-5">
                        <h3 class="font-heading text-2xl font-bold text-white uppercase border-b border-zinc-800 pb-3">Facility Information</h3>
                        
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-xl bg-red-600/10 text-red-500 flex items-center justify-center flex-shrink-0 text-lg">
                                <i class="fa-solid fa-location-dot"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-white uppercase">Main Location</h4>
                                <p class="text-xs text-zinc-400 mt-0.5">742 Evergreen Fitness Blvd, Metropolis, NY 10001</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-xl bg-red-600/10 text-red-500 flex items-center justify-center flex-shrink-0 text-lg">
                                <i class="fa-solid fa-phone"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-white uppercase">Direct Phone</h4>
                                <a href="tel:+923001234567" class="text-xs text-zinc-300 hover:text-red-400 mt-0.5 block">+92 300 1234567</a>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-xl bg-red-600/10 text-red-500 flex items-center justify-center flex-shrink-0 text-lg">
                                <i class="fa-solid fa-envelope"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-white uppercase">Support Email</h4>
                                <a href="mailto:support@gymflow.com" class="text-xs text-zinc-300 hover:text-red-400 mt-0.5 block">support@gymflow.com</a>
                            </div>
                        </div>
                    </div>

                    <!-- Hours Card -->
                    <div class="glass-card rounded-2xl p-6 space-y-3">
                        <h3 class="font-heading text-2xl font-bold text-white uppercase border-b border-zinc-800 pb-3">Operational Hours</h3>
                        <div class="flex justify-between text-xs py-1 border-b border-zinc-800/60">
                            <span class="text-zinc-400">Monday - Friday:</span>
                            <span class="text-white font-bold">05:00 AM - 11:00 PM</span>
                        </div>
                        <div class="flex justify-between text-xs py-1 border-b border-zinc-800/60">
                            <span class="text-zinc-400">Saturday:</span>
                            <span class="text-white font-bold">06:00 AM - 10:00 PM</span>
                        </div>
                        <div class="flex justify-between text-xs py-1 border-b border-zinc-800/60">
                            <span class="text-zinc-400">Sunday:</span>
                            <span class="text-white font-bold">07:00 AM - 09:00 PM</span>
                        </div>
                        <div class="flex justify-between text-xs pt-2">
                            <span class="text-zinc-400">VIP Pro Members:</span>
                            <span class="text-emerald-400 font-bold"><i class="fa-solid fa-key text-[10px]"></i> 24/7 Keycard Access</span>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </section>
</main>

<?php
require_once __DIR__ . '/components/footer.php';
?>
