<?php
/**
 * GymFlow - Secure Member Registration
 */
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';

$pageTitle = "Create Your Account - Join " . APP_NAME;
$pageDescription = "Sign up for a Gym Flow membership. Select your plan, configure your digital access keycard, and start your fitness transformation today.";

$error = null;
$success = null;
$selectedPlan = (int)($_GET['plan'] ?? 2); // Default Pro

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Security token mismatch. Please submit the form again.";
    } else {
        $regResult = registerMember([
            'full_name' => $_POST['full_name'] ?? '',
            'email'     => $_POST['email'] ?? '',
            'phone'     => $_POST['phone'] ?? '',
            'password'  => $_POST['password'] ?? '',
            'plan_id'   => (int)($_POST['plan_id'] ?? 2)
        ]);

        if ($regResult['success']) {
            header('Location: ' . url('user/index.php?registered=1'));
            exit;
        } else {
            $error = $regResult['message'] ?? 'An error occurred during registration.';
        }
    }
}

// Fetch plans from DB or fallback
$plans = [];
try {
    $db = getDB();
    $stmtPlans = $db->query("SELECT id, name, price, duration_days, is_popular FROM membership_plans WHERE status = 'active' ORDER BY price ASC");
    $plans = $stmtPlans->fetchAll();
} catch (Exception $e) {
    $plans = [
        ['id' => 1, 'name' => '1 Month Package', 'price' => 3000, 'duration_days' => 30, 'is_popular' => 0],
        ['id' => 2, 'name' => '3 Month Package', 'price' => 8000, 'duration_days' => 90, 'is_popular' => 1],
        ['id' => 3, 'name' => '6 Month Package', 'price' => 15000, 'duration_days' => 180, 'is_popular' => 0],
    ];
}

// Head Component
require_once __DIR__ . '/components/head.php';
// Global Header
require_once __DIR__ . '/components/header.php';
?>

<main class="flex-grow flex items-center justify-center py-16 px-4 bg-[#050507] relative overflow-hidden">
    <!-- Background Ambient Glow -->
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[550px] h-[550px] bg-red-600/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-lg relative z-10">
        
        <!-- Brand Header -->
        <div class="text-center mb-8">
            <a href="<?= url('index.php') ?>" class="inline-flex items-center gap-3">
                <img src="<?= asset('images/logo.png') ?>" alt="Gym Flow" class="h-10 w-auto object-contain">
            </a>
            <h1 class="font-heading text-4xl sm:text-5xl font-bold text-white uppercase tracking-wider mt-3">JOIN THE ELITE</h1>
            <p class="text-xs text-zinc-400 mt-1">Create your Gym Flow account in 60 seconds</p>
        </div>

        <!-- Registration Card -->
        <div class="glass-card rounded-3xl p-8 sm:p-10 border border-zinc-800 shadow-2xl">
            
            <?php if ($error): ?>
                <div class="mb-6 p-4 rounded-xl bg-red-950/80 border border-red-800/80 text-red-300 text-xs flex items-center gap-3">
                    <i class="fa-solid fa-triangle-exclamation text-base text-red-500"></i>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-zinc-300 mb-1.5">Full Legal Name</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-zinc-500"><i class="fa-regular fa-user"></i></span>
                        <input type="text" name="full_name" required placeholder="Marcus Phoenix" class="w-full bg-zinc-900 border border-zinc-800 rounded-xl pl-10 pr-4 py-3 text-sm text-white placeholder-zinc-500 focus:outline-none focus:border-red-500 transition-colors">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-zinc-300 mb-1.5">Email Address</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-zinc-500"><i class="fa-regular fa-envelope"></i></span>
                            <input type="email" name="email" required placeholder="athlete@example.com" class="w-full bg-zinc-900 border border-zinc-800 rounded-xl pl-10 pr-4 py-3 text-sm text-white placeholder-zinc-500 focus:outline-none focus:border-red-500 transition-colors">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-zinc-300 mb-1.5">Phone Number</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-zinc-500"><i class="fa-solid fa-phone"></i></span>
                            <input type="tel" name="phone" list="pk-phone-suggestions" placeholder="+92 300 1234567" class="w-full bg-zinc-900 border border-zinc-800 rounded-xl pl-10 pr-4 py-3 text-sm text-white placeholder-zinc-500 focus:outline-none focus:border-red-500 transition-colors">
                            <datalist id="pk-phone-suggestions">
                                <option value="+92 300 ">Jazz / Mobilink (+92 300)</option>
                                <option value="+92 301 ">Jazz (+92 301)</option>
                                <option value="+92 321 ">Warid (+92 321)</option>
                                <option value="+92 333 ">Ufone (+92 333)</option>
                                <option value="+92 345 ">Telenor (+92 345)</option>
                                <option value="+92 312 ">Zong (+92 312)</option>
                                <option value="+92 300 1234567">Sample (+92 300 1234567)</option>
                            </datalist>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-zinc-300 mb-1.5">Create Password (Min 6 chars)</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-zinc-500"><i class="fa-solid fa-lock"></i></span>
                        <input type="password" name="password" minlength="6" required placeholder="••••••••" class="w-full bg-zinc-900 border border-zinc-800 rounded-xl pl-10 pr-4 py-3 text-sm text-white placeholder-zinc-500 focus:outline-none focus:border-red-500 transition-colors">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-zinc-300 mb-1.5">Select Membership Package</label>
                    <select name="plan_id" class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-red-500 transition-colors">
                        <?php foreach ($plans as $p): ?>
                            <option value="<?= (int)$p['id'] ?>" <?= $selectedPlan === (int)$p['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['name']) ?> - PKR <?= number_format((float)$p['price']) ?> (<?= (int)$p['duration_days'] ?> Days<?= !empty($p['is_popular']) ? ' • Recommended' : '' ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="flex items-start gap-2 pt-2 text-xs text-zinc-400">
                    <input type="checkbox" required checked class="w-4 h-4 accent-red-600 rounded mt-0.5">
                    <span>I agree to the <a href="<?= url('terms.php') ?>" target="_blank" class="text-red-400 hover:underline">Terms of Service</a> and <a href="<?= url('privacy.php') ?>" target="_blank" class="text-red-400 hover:underline">Privacy Policy</a>.</span>
                </div>

                <button type="submit" class="btn-primary w-full py-4 rounded-xl font-bold uppercase tracking-wider text-xs shadow-lg shadow-red-600/30 flex items-center justify-center gap-2 mt-4">
                    <span>Activate Account & Membership</span>
                    <i class="fa-solid fa-arrow-right text-[11px]"></i>
                </button>
            </form>

            <div class="mt-6 pt-6 border-t border-zinc-800 text-center text-xs text-zinc-400">
                Already have an active account? 
                <a href="<?= url('login.php') ?>" class="text-red-400 font-bold hover:underline ml-1">Sign In Here</a>
            </div>

        </div>

    </div>
</main>

<?php
require_once __DIR__ . '/components/footer.php';
?>
