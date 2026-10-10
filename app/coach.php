<?php
/**
 * GymFlow Mobile App - Coach & Front Desk Direct Support
 * Premium Dark Native Mobile UI with Interactive Topics & WhatsApp Integration
 */
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

$isAuth = isLoggedIn();
$currentUser = $isAuth ? getCurrentUser() : null;
$userId = $currentUser ? (int)$currentUser['id'] : 0;
$db = Database::getConnection();

$successMsg = null;
$errorMsg = null;

// Handle Contact Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_coach_msg'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errorMsg = "Security session expired. Please refresh and try again.";
    } else {
        $fullName = trim($_POST['full_name'] ?? ($currentUser['name'] ?? 'Guest Member'));
        $email = trim($_POST['email'] ?? ($currentUser['email'] ?? 'member@gymflow.com'));
        $phone = trim($_POST['phone'] ?? ($currentUser['phone'] ?? ''));
        $topic = trim($_POST['topic'] ?? 'General Fitness Inquiry');
        $rawMessage = trim($_POST['message'] ?? '');

        if (empty($rawMessage) || empty($topic) || empty($fullName)) {
            $errorMsg = "Please enter your name, select a topic, and write your message.";
        } else {
            try {
                $stmt = $db->prepare("
                    INSERT INTO contact_messages (full_name, email, phone, program, message, status, created_at)
                    VALUES (:fn, :em, :ph, :topic, :msg, 'unread', NOW())
                ");
                $stmt->execute([
                    ':fn' => $fullName,
                    ':em' => $email,
                    ':ph' => $phone,
                    ':topic' => $topic,
                    ':msg' => $rawMessage
                ]);
                $successMsg = "Your message has been sent to Coach Marcus & Front Desk! We'll reply shortly.";
            } catch (Exception $e) {
                try {
                    $stmt = $db->prepare("
                        INSERT INTO contact_messages (full_name, email, phone, message, status, created_at)
                        VALUES (:fn, :em, :ph, :msg, 'unread', NOW())
                    ");
                    $stmt->execute([
                        ':fn' => $fullName,
                        ':em' => $email,
                        ':ph' => $phone,
                        ':msg' => "[{$topic}] " . $rawMessage
                    ]);
                    $successMsg = "Message dispatched successfully to the coaching team!";
                } catch (Exception $e2) {
                    $errorMsg = "Unable to send message right now. Please reach out via WhatsApp.";
                }
            }
        }
    }
}

// Fetch past inquiries for this user
$myMessages = [];
if ($isAuth && !empty($currentUser['email'])) {
    try {
        $msgQuery = $db->prepare("
            SELECT * FROM contact_messages 
            WHERE email = :email 
            ORDER BY id DESC 
            LIMIT 5
        ");
        $msgQuery->execute([':email' => $currentUser['email']]);
        $myMessages = $msgQuery->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $myMessages = [];
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Ask Coach & Support - GymFlow</title>

    <meta name="theme-color" content="#070709">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="manifest" href="manifest.json">
    <link rel="icon" type="image/png" sizes="192x192" href="icons/icon-192x192.png">
    <link rel="apple-touch-icon" sizes="180x180" href="icons/apple-touch-icon.png">
    <link rel="shortcut icon" href="favicon.ico">

    <!-- Google Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Teko:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Tailwind CSS -->
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
            background-color: #070709;
            color: #f3f4f6;
            font-family: 'Plus Jakarta Sans', sans-serif;
            overflow-x: hidden;
            overscroll-behavior-y: none;
            padding-bottom: env(safe-area-inset-bottom, 24px);
            padding-top: env(safe-area-inset-top, 0px);
        }
        .safe-top {
            padding-top: max(12px, env(safe-area-inset-top));
        }
        .font-heading {
            font-family: 'Teko', sans-serif;
            letter-spacing: 0.04em;
        }
        .topic-chip.active {
            background-color: rgba(255, 42, 42, 0.15);
            border-color: rgba(255, 42, 42, 0.6);
            color: #ff5e5e;
        }
    </style>
</head>
<body class="bg-[#070709] text-white min-h-screen flex flex-col justify-between antialiased">

    <!-- Header -->
    <header class="sticky top-0 z-40 bg-[#070709]/95 backdrop-blur-md border-b border-zinc-900 px-4 py-3.5 safe-top flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="index.php" class="w-9 h-9 rounded-xl bg-zinc-900 border border-zinc-800 text-zinc-300 hover:text-white flex items-center justify-center text-sm shadow-sm active:scale-95 transition-all" title="Back to App">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="font-heading text-2xl font-bold uppercase tracking-wide leading-none text-white">Ask Coach</h1>
                <p class="text-[11px] text-zinc-400">Direct Trainer & Front Desk Help</p>
            </div>
        </div>

        <div class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-[11px] font-bold">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            <span>Desk Online</span>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-1 max-w-md mx-auto w-full px-4 py-4 space-y-4">

        <!-- Coach Highlight Card -->
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-zinc-900 via-zinc-950 to-black p-4 border border-zinc-800/80 shadow-2xl flex items-center gap-3.5">
            <div class="relative shrink-0">
                <div class="w-14 h-14 rounded-2xl bg-zinc-800 border-2 border-red-500/40 flex items-center justify-center text-xl font-heading text-red-500 font-bold overflow-hidden shadow-lg">
                    <i class="fa-solid fa-dumbbell text-red-500 text-2xl"></i>
                </div>
                <span class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full bg-emerald-500 border-2 border-black" title="Online"></span>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                    <h3 class="text-sm font-bold text-white truncate">Coach Marcus Vance</h3>
                    <span class="px-2 py-0.5 rounded-md bg-red-950/80 text-red-400 border border-red-800/80 text-[9px] font-bold uppercase">Head Coach</span>
                </div>
                <p class="text-[11px] text-zinc-400 leading-snug mt-0.5">Strength & Conditioning • Diet Plans • Inquiries</p>
            </div>
        </div>

        <!-- Flash Notices -->
        <?php if ($successMsg): ?>
            <div class="p-4 rounded-2xl bg-emerald-950/80 border border-emerald-500/40 text-emerald-300 text-xs flex items-center justify-between shadow-xl animate-fade-in">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-circle-check text-emerald-400 text-lg shrink-0"></i>
                    <span><?= htmlspecialchars($successMsg) ?></span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-white px-2 py-1"><i class="fa-solid fa-xmark"></i></button>
            </div>
        <?php endif; ?>

        <?php if ($errorMsg): ?>
            <div class="p-4 rounded-2xl bg-red-950/80 border border-red-500/40 text-red-300 text-xs flex items-center justify-between shadow-xl animate-fade-in">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-triangle-exclamation text-red-400 text-lg shrink-0"></i>
                    <span><?= htmlspecialchars($errorMsg) ?></span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-red-400 hover:text-white px-2 py-1"><i class="fa-solid fa-xmark"></i></button>
            </div>
        <?php endif; ?>

        <!-- Quick One-Tap Contact Bar -->
        <div class="grid grid-cols-2 gap-2.5">
            <a href="https://wa.me/923001234567?text=Hi%20GymFlow%20Coach,%20I%20am%20a%20member%20and%20need%20assistance." target="_blank" class="p-3 rounded-2xl bg-emerald-950/40 border border-emerald-800/60 hover:bg-emerald-900/40 text-emerald-300 flex items-center justify-center gap-2 text-xs font-bold shadow-sm active:scale-95 transition-all">
                <i class="fa-brands fa-whatsapp text-base text-emerald-400"></i>
                <span>WhatsApp Coach</span>
            </a>
            <a href="tel:+923001234567" class="p-3 rounded-2xl bg-zinc-900/90 border border-zinc-800 hover:bg-zinc-800 text-zinc-200 flex items-center justify-center gap-2 text-xs font-bold shadow-sm active:scale-95 transition-all">
                <i class="fa-solid fa-phone text-xs text-red-400"></i>
                <span>Call Front Desk</span>
            </a>
        </div>

        <!-- Contact Form Card -->
        <div class="bg-zinc-950 rounded-3xl p-5 border border-zinc-900 shadow-2xl space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-xs font-bold uppercase tracking-wider text-zinc-300 flex items-center gap-2">
                    <i class="fa-regular fa-message text-red-500"></i>
                    <span>Send Message to Staff</span>
                </h2>
                <span class="text-[10px] text-zinc-500 font-mono">Response ~15 min</span>
            </div>

            <!-- Quick Topic Presets -->
            <div>
                <label class="block text-[11px] font-semibold text-zinc-400 mb-2">Select Topic</label>
                <div class="flex flex-wrap gap-1.5" id="topicChips">
                    <button type="button" onclick="selectTopic('Workout Plan Review', this)" class="topic-chip active text-[10px] font-semibold px-3 py-1.5 rounded-xl bg-zinc-900 border border-zinc-800 text-zinc-300 hover:text-white transition-all">Workout Plan</button>
                    <button type="button" onclick="selectTopic('Nutrition & Diet Advice', this)" class="topic-chip text-[10px] font-semibold px-3 py-1.5 rounded-xl bg-zinc-900 border border-zinc-800 text-zinc-300 hover:text-white transition-all">Diet & Nutrition</button>
                    <button type="button" onclick="selectTopic('Personal Training Inquiry', this)" class="topic-chip text-[10px] font-semibold px-3 py-1.5 rounded-xl bg-zinc-900 border border-zinc-800 text-zinc-300 hover:text-white transition-all">Personal Trainer</button>
                    <button type="button" onclick="selectTopic('Sauna & Facility Access', this)" class="topic-chip text-[10px] font-semibold px-3 py-1.5 rounded-xl bg-zinc-900 border border-zinc-800 text-zinc-300 hover:text-white transition-all">Sauna / Facilities</button>
                    <button type="button" onclick="selectTopic('Billing or Keycard Help', this)" class="topic-chip text-[10px] font-semibold px-3 py-1.5 rounded-xl bg-zinc-900 border border-zinc-800 text-zinc-300 hover:text-white transition-all">Keycard / Billing</button>
                </div>
            </div>

            <form method="POST" action="" class="space-y-3.5">
                <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                <input type="hidden" name="send_coach_msg" value="1">
                <input type="hidden" name="topic" id="topicField" value="Workout Plan Review">

                <!-- Member Name & Email Grid -->
                <div class="grid grid-cols-2 gap-2.5">
                    <div>
                        <label class="block text-[11px] font-semibold text-zinc-400 mb-1">Your Name</label>
                        <input type="text" name="full_name" required value="<?= htmlspecialchars($currentUser['name'] ?? '') ?>" placeholder="e.g. Alex" class="w-full bg-zinc-900 border border-zinc-800 rounded-2xl px-3.5 py-2.5 text-xs text-white placeholder-zinc-500 focus:outline-none focus:border-red-500 transition-all">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-zinc-400 mb-1">Phone / WhatsApp</label>
                        <input type="tel" name="phone" value="<?= htmlspecialchars($currentUser['phone'] ?? '') ?>" placeholder="+92 300 0000000" class="w-full bg-zinc-900 border border-zinc-800 rounded-2xl px-3.5 py-2.5 text-xs text-white placeholder-zinc-500 focus:outline-none focus:border-red-500 transition-all">
                    </div>
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-[11px] font-semibold text-zinc-400 mb-1">Email Address</label>
                    <input type="email" name="email" required value="<?= htmlspecialchars($currentUser['email'] ?? '') ?>" placeholder="member@gymflow.com" class="w-full bg-zinc-900 border border-zinc-800 rounded-2xl px-3.5 py-2.5 text-xs text-white placeholder-zinc-500 focus:outline-none focus:border-red-500 transition-all">
                </div>

                <!-- Message Textarea -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="text-[11px] font-semibold text-zinc-400">Your Message / Question</label>
                        <span id="charCount" class="text-[10px] text-zinc-500 font-mono">0/500</span>
                    </div>
                    <textarea name="message" id="messageInput" required rows="4" maxlength="500" oninput="updateCounter(this)" placeholder="Describe your question, goals, or gym assistance request..." class="w-full bg-zinc-900 border border-zinc-800 rounded-2xl p-3.5 text-xs text-white placeholder-zinc-500 focus:outline-none focus:border-red-500 transition-all resize-none"></textarea>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full py-3.5 rounded-2xl bg-red-600 hover:bg-red-500 text-white font-bold text-xs uppercase tracking-wider flex items-center justify-center gap-2 shadow-lg shadow-red-600/30 active:scale-95 transition-all cursor-pointer">
                    <i class="fa-regular fa-paper-plane text-xs"></i>
                    <span>Send to Coach</span>
                </button>
            </form>
        </div>

        <!-- Recent Inquiries History -->
        <?php if (!empty($myMessages)): ?>
            <div class="space-y-2 pt-1">
                <span class="text-[11px] font-bold text-zinc-400 uppercase tracking-wider block px-1 flex items-center justify-between">
                    <span>Recent Inquiries</span>
                    <span class="text-zinc-500 font-mono text-[10px]"><?= count($myMessages) ?> logged</span>
                </span>
                <div class="space-y-2">
                    <?php foreach ($myMessages as $msg): ?>
                        <div class="bg-zinc-950 p-4 rounded-2xl border border-zinc-900 space-y-1.5 shadow-sm">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-zinc-200"><?= htmlspecialchars($msg['program'] ?? $msg['subject'] ?? 'Inquiry') ?></span>
                                <span class="px-2 py-0.5 rounded-md text-[9px] font-bold uppercase <?= ($msg['status'] ?? 'unread') === 'read' ? 'bg-emerald-950 text-emerald-400 border border-emerald-800/60' : 'bg-amber-950 text-amber-400 border border-amber-800/60' ?>">
                                    <?= ($msg['status'] ?? 'unread') === 'read' ? 'Answered' : 'Received' ?>
                                </span>
                            </div>
                            <p class="text-xs text-zinc-400 line-clamp-2 leading-relaxed font-sans">
                                <?= htmlspecialchars($msg['message']) ?>
                            </p>
                            <span class="text-[10px] text-zinc-600 block"><?= date('M j, Y • h:i A', strtotime($msg['created_at'])) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

    </main>

    <!-- Bottom Navigation Bar -->
    <nav class="sticky bottom-0 z-40 bg-[#070709]/95 backdrop-blur-md border-t border-zinc-900 px-6 py-2.5 flex items-center justify-around text-zinc-400 text-xs">
        <a href="index.php" class="flex flex-col items-center gap-1 hover:text-white transition-colors">
            <i class="fa-solid fa-house text-sm"></i>
            <span class="text-[10px] font-medium">Home</span>
        </a>
        <a href="coach.php" class="flex flex-col items-center gap-1 text-red-500 font-semibold">
            <i class="fa-solid fa-comments text-sm"></i>
            <span class="text-[10px] font-medium">Coach</span>
        </a>
        <a href="logout.php" class="flex flex-col items-center gap-1 hover:text-red-400 transition-colors">
            <i class="fa-solid fa-arrow-right-from-bracket text-sm"></i>
            <span class="text-[10px] font-medium">Logout</span>
        </a>
    </nav>

    <script>
        function selectTopic(topic, btn) {
            document.getElementById('topicField').value = topic;
            document.querySelectorAll('.topic-chip').forEach(el => el.classList.remove('active'));
            btn.classList.add('active');
        }

        function updateCounter(textarea) {
            const count = document.getElementById('charCount');
            if (count) count.textContent = `${textarea.value.length}/500`;
        }
    </script>
</body>
</html>
