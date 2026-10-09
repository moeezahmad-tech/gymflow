<?php
/**
 * GymFlow - Member Authentication Portal
 */
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';

$pageTitle = "Member Login | " . APP_NAME;
$pageDescription = "Secure login portal for Gym Flow members. Access workout schedules, billing, QR entry codes, and fitness metrics.";

$error = null;

// Handle login submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Session expired or invalid security token. Please try again.";
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $authResult = authenticateUser($email, $password);

        if ($authResult['success']) {
            if ($authResult['role'] === 'admin' || $authResult['role'] === 'staff') {
                header('Location: ' . url('admin/index.php'));
                exit;
            } else {
                header('Location: ' . url('user/index.php'));
                exit;
            }
        } else {
            $error = $authResult['message'] ?? 'Invalid email or password.';
        }
    }
}

// Modular Head
require_once __DIR__ . '/components/head.php';
// Global Header
require_once __DIR__ . '/components/header.php';
?>

<main class="flex-grow flex items-center justify-center py-16 px-4 bg-[#050507] relative overflow-hidden">
    <!-- Background Ambient Glow -->
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-red-600/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-md relative z-10">
        
        <!-- Brand Header in Card -->
        <div class="text-center mb-8">
            <a href="<?= url('index.php') ?>" class="inline-flex items-center gap-3">
                <img src="<?= asset('images/logo.png') ?>" alt="Gym Flow" class="h-10 w-auto object-contain">
            </a>
            <h1 class="font-heading text-3xl sm:text-4xl font-bold text-white uppercase tracking-wider mt-4">MEMBER LOGIN</h1>
            <p class="text-xs text-zinc-400 mt-1">Sign in to access your workout schedules, check-in QR & portal</p>
        </div>

        <!-- Login Card -->
        <div class="glass-card rounded-3xl p-8 border border-zinc-800 shadow-2xl">
            
            <?php if ($error): ?>
                <div class="mb-6 p-4 rounded-xl bg-red-950/80 border border-red-800/80 text-red-300 text-xs flex items-center gap-3">
                    <i class="fa-solid fa-triangle-exclamation text-base text-red-500"></i>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form method="POST" action="" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-zinc-300 mb-2">Email or Member ID</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-zinc-500"><i class="fa-regular fa-envelope"></i></span>
                        <input type="text" name="email" id="emailInput" required value="member@gymflow.com" placeholder="member@gymflow.com" class="w-full bg-zinc-900 border border-zinc-800 rounded-xl pl-10 pr-4 py-3 text-sm text-white placeholder-zinc-500 focus:outline-none focus:border-red-500 transition-colors">
                    </div>
                </div>

                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-zinc-300">Password</label>
                        <a href="#" onclick="showToast('Password reset link has been dispatched to your email address.', 'info', 'Password Reset'); return false;" class="text-xs text-red-400 hover:underline">Forgot?</a>
                    </div>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-zinc-500"><i class="fa-solid fa-lock"></i></span>
                        <input type="password" name="password" id="passwordInput" required value="Member123!" placeholder="••••••••" class="w-full bg-zinc-900 border border-zinc-800 rounded-xl pl-10 pr-4 py-3 text-sm text-white placeholder-zinc-500 focus:outline-none focus:border-red-500 transition-colors">
                    </div>
                </div>

                <div class="flex items-center text-xs text-zinc-400 pt-1">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" checked class="w-4 h-4 accent-red-600 rounded">
                        <span>Remember credentials</span>
                    </label>
                </div>

                <button type="submit" id="submitBtn" class="btn-primary w-full py-3.5 rounded-xl font-bold uppercase tracking-wider text-xs shadow-lg shadow-red-600/30 flex items-center justify-center gap-2 mt-4">
                    <span>Enter Member Dashboard</span>
                    <i class="fa-solid fa-arrow-right text-[11px]"></i>
                </button>
            </form>

            <!-- Registration Link -->
            <div class="mt-6 pt-6 border-t border-zinc-800 text-center text-xs text-zinc-400">
                Don't have a gym membership yet? 
                <a href="<?= url('register.php') ?>" class="text-red-400 font-bold hover:underline ml-1">Create Account</a>
            </div>

            <!-- Demo Member Credentials Reference -->
            <div class="mt-4 p-3 rounded-xl bg-zinc-950 border border-zinc-800/80 text-[11px] text-zinc-500 space-y-1">
                <div class="text-zinc-400 font-bold uppercase text-[10px]">Demo Member Account:</div>
                <div class="flex justify-between"><span>Member:</span> <code class="text-zinc-300 font-mono">member@gymflow.com / Member123!</code></div>
            </div>

        </div>

    </div>
</main>

<?php
require_once __DIR__ . '/components/footer.php';
?>
