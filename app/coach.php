<?php
/**
 * GymFlow Mobile App - Dedicated Coach & Front Desk Support Page
 */
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

if (!isLoggedIn()) {
    header('Location: ' . url('app/index.php'));
    exit;
}

$currentUser = getCurrentUser();
$userId = (int)$currentUser['id'];
$db = Database::getConnection();
$successMsg = null;
$errorMsg = null;

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_coach_msg'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errorMsg = "Session token expired. Please try again.";
    } else {
        $topic = trim($_POST['topic'] ?? 'General Consultation');
        $rawMessage = trim($_POST['message'] ?? '');
        $isUrgent = isset($_POST['is_urgent']);

        if (empty($rawMessage)) {
            $errorMsg = "Please enter a message for the coaching team.";
        } else {
            $formattedMsg = ($isUrgent ? '⚡ [AT GYM FLOOR - URGENT] ' : '') . "[{$topic}] " . $rawMessage;
            try {
                // Check columns in contact_messages
                $stmt = $db->prepare("
                    INSERT INTO contact_messages (full_name, email, phone, program, message, status, created_at)
                    VALUES (:fn, :em, :ph, :topic, :msg, 'unread', NOW())
                ");
                $stmt->execute([
                    ':fn' => $currentUser['name'] ?? 'Member',
                    ':em' => $currentUser['email'] ?? 'member@gymflow.com',
                    ':ph' => $currentUser['phone'] ?? '+923000000000',
                    ':topic' => $topic,
                    ':msg' => $formattedMsg
                ]);
                $successMsg = "Your message was sent to the coaching desk. A trainer will reply promptly!";
            } catch (Exception $e) {
                // Fallback without program column if subject is used
                try {
                    $stmt = $db->prepare("
                        INSERT INTO contact_messages (full_name, email, phone, message, status, created_at)
                        VALUES (:fn, :em, :ph, :msg, 'unread', NOW())
                    ");
                    $stmt->execute([
                        ':fn' => $currentUser['name'] ?? 'Member',
                        ':em' => $currentUser['email'] ?? 'member@gymflow.com',
                        ':ph' => $currentUser['phone'] ?? '+923000000000',
                        ':msg' => $formattedMsg
                    ]);
                    $successMsg = "Your message was sent to the coaching desk!";
                } catch (Exception $e2) {
                    $errorMsg = "Failed to send message: " . $e2->getMessage();
                }
            }
        }
    }
}

// Fetch past inquiries for this user
$myMessages = [];
try {
    $email = $currentUser['email'] ?? '';
    $msgQuery = $db->prepare("SELECT * FROM contact_messages WHERE email = :email ORDER BY id DESC LIMIT 5");
    $msgQuery->execute([':email' => $email]);
    $myMessages = $msgQuery->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $myMessages = [];
}
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Coach & Front Desk Support - GymFlow</title>

    <meta name="theme-color" content="#070709">
    <link rel="manifest" href="manifest.json">
    <link rel="icon" type="image/png" sizes="192x192" href="icons/icon-192x192.png">
    <link rel="apple-touch-icon" sizes="180x180" href="icons/apple-touch-icon.png">
    <link rel="shortcut icon" href="favicon.ico">

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Teko:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            red: '#ff2a2a',
                            dark: '#070709',
                            card: '#101116',
                            border: '#1e2129'
                        }
                    },
                    fontFamily: {
                        heading: ['Teko', 'sans-serif'],
                        sans: ['Plus Jakarta Sans', 'sans-serif']
                    }
                }
            }
        }
    </script>

    <style>
        * {
            -webkit-tap-highlight-color: transparent;
            user-select: none;
            box-sizing: border-box;
        }
        input, textarea {
            user-select: text;
        }
        body {
            background-color: #000000;
            color: #f3f4f6;
            font-family: 'Plus Jakarta Sans', sans-serif;
            overflow-x: hidden;
            overscroll-behavior-y: none;
            padding-bottom: env(safe-area-inset-bottom, 20px);
            padding-top: env(safe-area-inset-top, 0px);
        }
        .safe-top {
            padding-top: max(12px, env(safe-area-inset-top));
        }
        .safe-bottom {
            padding-bottom: max(16px, env(safe-area-inset-bottom));
        }
        .font-heading {
            font-family: 'Teko', sans-serif;
            letter-spacing: 0.04em;
        }
        .glass-card {
            background: linear-gradient(145deg, rgba(20, 21, 27, 0.85) 0%, rgba(10, 11, 15, 0.95) 100%);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
    </style>
</head>
<body class="bg-black text-white min-h-screen flex flex-col justify-between selection:bg-red-600 selection:text-white antialiased">

    <!-- Top App Navigation Bar -->
    <header class="sticky top-0 z-40 bg-[#070709]/95 backdrop-blur-xl border-b border-zinc-800/80 px-4 py-3 safe-top flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="index.php" class="w-9 h-9 rounded-xl bg-zinc-900 border border-zinc-800 text-zinc-300 hover:text-white flex items-center justify-center text-sm shadow-sm active:scale-95 transition-all">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="font-heading text-xl font-bold uppercase text-white tracking-wide leading-none">Coach & Desk</h1>
                <p class="text-[10px] text-zinc-400">Direct Member Support</p>
            </div>
        </div>

        <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-[10px] font-bold">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            <span>Online</span>
        </div>
    </header>

    <!-- Main Container -->
    <main class="flex-1 max-w-md mx-auto w-full px-4 py-4 space-y-4">
        
        <!-- Alerts -->
        <?php if ($successMsg): ?>
            <div class="p-3.5 rounded-2xl bg-emerald-950/80 border border-emerald-800/80 text-emerald-300 text-xs flex items-center gap-3">
                <i class="fa-solid fa-circle-check text-emerald-400 text-base shrink-0"></i>
                <span class="leading-relaxed"><?= htmlspecialchars($successMsg) ?></span>
            </div>
        <?php endif; ?>

        <?php if ($errorMsg): ?>
            <div class="p-3.5 rounded-2xl bg-red-950/80 border border-red-800/80 text-red-300 text-xs flex items-center gap-3">
                <i class="fa-solid fa-circle-exclamation text-red-400 text-base shrink-0"></i>
                <span class="leading-relaxed"><?= htmlspecialchars($errorMsg) ?></span>
            </div>
        <?php endif; ?>

        <!-- Hero Coach Card -->
        <div class="glass-card rounded-3xl p-5 border border-zinc-800/90 relative overflow-hidden">
            <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-red-600/10 rounded-full blur-2xl pointer-events-none"></div>
            
            <div class="flex items-start gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-red-600 to-red-800 flex items-center justify-center text-white text-lg shadow-xl shadow-red-600/30 border border-red-500/40 shrink-0">
                    <i class="fa-solid fa-headset"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <span class="text-[10px] uppercase font-bold text-red-400 tracking-widest block mb-0.5">Trainer Desk</span>
                    <h2 class="font-heading text-2xl font-bold uppercase text-white leading-tight">Head Coaching Staff</h2>
                    <p class="text-xs text-zinc-400 mt-0.5">Ask questions about your workout routines, meal advice, gate access, or equipment guidance.</p>
                </div>
            </div>

            <!-- Response Time & Floor Presence -->
            <div class="grid grid-cols-2 gap-2 mt-4 pt-3 border-t border-zinc-800/80">
                <div class="p-2.5 rounded-xl bg-zinc-900/80 border border-zinc-800 flex items-center gap-2">
                    <i class="fa-regular fa-clock text-amber-400 text-xs"></i>
                    <div>
                        <span class="text-[9px] uppercase font-bold text-zinc-500 block">Avg Response</span>
                        <span class="text-xs font-bold text-zinc-200">&lt; 5 Minutes</span>
                    </div>
                </div>
                <div class="p-2.5 rounded-xl bg-zinc-900/80 border border-zinc-800 flex items-center gap-2">
                    <i class="fa-solid fa-user-check text-emerald-400 text-xs"></i>
                    <div>
                        <span class="text-[9px] uppercase font-bold text-zinc-500 block">Duty Staff</span>
                        <span class="text-xs font-bold text-zinc-200">On Gym Floor</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Instant Actions -->
        <div class="grid grid-cols-2 gap-2.5">
            <a href="tel:+923001234567" class="glass-card p-3.5 rounded-2xl border border-zinc-800 hover:border-zinc-700 flex items-center gap-3 active:scale-95 transition-all">
                <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center text-base shrink-0 border border-blue-500/20">
                    <i class="fa-solid fa-phone"></i>
                </div>
                <div class="min-w-0">
                    <span class="block text-[9px] uppercase font-bold text-zinc-500">Call Directly</span>
                    <strong class="text-xs font-bold text-white block truncate">Front Desk</strong>
                </div>
            </a>

            <a href="https://wa.me/923001234567?text=Hi%20GymFlow%20Coach,%20I%20am%20a%20member%20and%20need%20assistance." target="_blank" class="glass-card p-3.5 rounded-2xl border border-zinc-800 hover:border-zinc-700 flex items-center gap-3 active:scale-95 transition-all">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center text-base shrink-0 border border-emerald-500/20">
                    <i class="fa-brands fa-whatsapp"></i>
                </div>
                <div class="min-w-0">
                    <span class="block text-[9px] uppercase font-bold text-zinc-500">Instant Chat</span>
                    <strong class="text-xs font-bold text-white block truncate">WhatsApp</strong>
                </div>
            </a>
        </div>

        <!-- Compose Message Card -->
        <div class="glass-card rounded-3xl p-5 border border-zinc-800/80 space-y-4">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-widest text-red-400 block mb-1">In-App Support</span>
                <h3 class="font-heading text-2xl font-bold uppercase text-white">Send Consultation Note</h3>
            </div>

            <form method="POST" action="" class="space-y-3.5">
                <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                <input type="hidden" name="send_coach_msg" value="1">
                <input type="hidden" name="topic" id="selectedTopicInput" value="Workout Routine & Form">

                <!-- Topic Selector Chips -->
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-zinc-400 mb-2">Select Topic</label>
                    <div class="grid grid-cols-2 gap-2" id="topicGrid">
                        <button type="button" onclick="chooseTopic(this, 'Workout Routine & Form')" class="topic-chip active p-2.5 rounded-xl bg-red-600/20 border border-red-500 text-red-400 text-xs font-semibold flex items-center gap-2 text-left transition-all">
                            <span>🏋️</span>
                            <span class="truncate">Workout Form</span>
                        </button>
                        <button type="button" onclick="chooseTopic(this, 'Nutrition & Diet Plan')" class="topic-chip p-2.5 rounded-xl bg-zinc-900 border border-zinc-800 text-zinc-300 hover:border-zinc-700 text-xs font-semibold flex items-center gap-2 text-left transition-all">
                            <span>🥗</span>
                            <span class="truncate">Diet Advice</span>
                        </button>
                        <button type="button" onclick="chooseTopic(this, 'Turnstile & Locker Access')" class="topic-chip p-2.5 rounded-xl bg-zinc-900 border border-zinc-800 text-zinc-300 hover:border-zinc-700 text-xs font-semibold flex items-center gap-2 text-left transition-all">
                            <span>🔑</span>
                            <span class="truncate">Locker / Gate</span>
                        </button>
                        <button type="button" onclick="chooseTopic(this, 'Membership & Pass Renewal')" class="topic-chip p-2.5 rounded-xl bg-zinc-900 border border-zinc-800 text-zinc-300 hover:border-zinc-700 text-xs font-semibold flex items-center gap-2 text-left transition-all">
                            <span>💳</span>
                            <span class="truncate">Pass Renewal</span>
                        </button>
                    </div>
                </div>

                <!-- Message Textarea -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="text-[10px] font-bold uppercase tracking-wider text-zinc-400">Your Inquiry</label>
                        <span id="charCount" class="text-[10px] text-zinc-500">0/300</span>
                    </div>
                    <textarea name="message" id="messageInput" required rows="4" maxlength="300" oninput="updateCharCount(this)" placeholder="Describe your question or requirement clearly for the coach..." class="w-full bg-zinc-900/90 border border-zinc-800 rounded-2xl p-3.5 text-xs text-white placeholder-zinc-500 focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500/40 transition-all resize-none"></textarea>
                </div>

                <!-- Priority Toggle -->
                <div class="flex items-center justify-between p-3 rounded-2xl bg-zinc-900/60 border border-zinc-800/80 text-xs">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-lg bg-amber-500/10 text-amber-400 flex items-center justify-center text-xs">
                            <i class="fa-solid fa-bolt"></i>
                        </div>
                        <div>
                            <span class="text-xs text-zinc-200 font-semibold block leading-tight">Currently on gym floor?</span>
                            <span class="text-[10px] text-zinc-500">Flags message as immediate attention</span>
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer shrink-0">
                        <input type="checkbox" name="is_urgent" class="sr-only peer">
                        <div class="w-9 h-5 bg-zinc-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-red-600"></div>
                    </label>
                </div>

                <button type="submit" class="w-full py-4 rounded-2xl bg-gradient-to-r from-red-600 to-red-700 hover:from-red-500 hover:to-red-600 text-white font-bold text-xs uppercase tracking-wider flex items-center justify-center gap-2 shadow-xl shadow-red-600/30 active:scale-95 transition-all">
                    <i class="fa-regular fa-paper-plane text-xs"></i>
                    <span>Submit Inquiry</span>
                </button>
            </form>
        </div>

        <!-- Recent Inquiries History -->
        <div class="glass-card rounded-3xl p-5 border border-zinc-800/80">
            <h4 class="font-heading text-lg font-bold uppercase text-white tracking-wide mb-3">Your Recent Inquiries</h4>
            <div class="space-y-2.5">
                <?php if (empty($myMessages)): ?>
                    <p class="text-xs text-zinc-500 text-center py-4">No previous inquiries sent yet.</p>
                <?php else: ?>
                    <?php foreach ($myMessages as $msg): ?>
                        <div class="p-3 rounded-2xl bg-zinc-900 border border-zinc-800/80 space-y-1.5">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-zinc-200"><?= htmlspecialchars($msg['program'] ?? $msg['subject'] ?? 'Consultation') ?></span>
                                <span class="text-[10px] font-bold uppercase <?= ($msg['status'] ?? 'unread') === 'read' ? 'text-emerald-400' : 'text-amber-400' ?>">
                                    <?= ($msg['status'] ?? 'unread') === 'read' ? 'Reviewed' : 'Pending' ?>
                                </span>
                            </div>
                            <p class="text-xs text-zinc-400 line-clamp-2 leading-relaxed">
                                <?= htmlspecialchars($msg['message']) ?>
                            </p>
                            <span class="text-[9px] text-zinc-500 block pt-0.5"><?= date('M j, Y • h:i A', strtotime($msg['created_at'])) ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </main>

    <script>
        function chooseTopic(btn, topic) {
            document.querySelectorAll('.topic-chip').forEach(el => {
                el.classList.remove('active', 'bg-red-600/20', 'border-red-500', 'text-red-400');
                el.classList.add('bg-zinc-900', 'border-zinc-800', 'text-zinc-300');
            });
            btn.classList.remove('bg-zinc-900', 'border-zinc-800', 'text-zinc-300');
            btn.classList.add('active', 'bg-red-600/20', 'border-red-500', 'text-red-400');

            document.getElementById('selectedTopicInput').value = topic;
            document.getElementById('messageInput').focus();
        }

        function updateCharCount(textarea) {
            const count = document.getElementById('charCount');
            if (count) count.textContent = `${textarea.value.length}/300`;
        }
    </script>
</body>
</html>
