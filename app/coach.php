<?php
/**
 * GymFlow Mobile App - Coach & Front Desk Support
 * Clean, Minimal & Modern Native UI
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
        $errorMsg = "Session expired. Please try again.";
    } else {
        $topic = trim($_POST['topic'] ?? 'General Support');
        $rawMessage = trim($_POST['message'] ?? '');

        if (empty($rawMessage)) {
            $errorMsg = "Please type a message before sending.";
        } else {
            $formattedMsg = "[{$topic}] " . $rawMessage;
            try {
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
                $successMsg = "Message sent! Our coaching team will respond shortly.";
            } catch (Exception $e) {
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
                    $successMsg = "Message sent to coaching team!";
                } catch (Exception $e2) {
                    $errorMsg = "Unable to send message right now.";
                }
            }
        }
    }
}

// Fetch past inquiries for this user
$myMessages = [];
try {
    $email = $currentUser['email'] ?? '';
    $msgQuery = $db->prepare("SELECT * FROM contact_messages WHERE email = :email ORDER BY id DESC LIMIT 3");
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
    <title>Coach Support - GymFlow</title>

    <meta name="theme-color" content="#070709">
    <link rel="manifest" href="manifest.json">
    <link rel="icon" type="image/png" sizes="192x192" href="icons/icon-192x192.png">
    <link rel="apple-touch-icon" sizes="180x180" href="icons/apple-touch-icon.png">
    <link rel="shortcut icon" href="favicon.ico">

    <!-- Fonts & Icons -->
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
            padding-bottom: env(safe-area-inset-bottom, 20px);
            padding-top: env(safe-area-inset-top, 0px);
        }
        .safe-top {
            padding-top: max(12px, env(safe-area-inset-top));
        }
        .font-heading {
            font-family: 'Teko', sans-serif;
            letter-spacing: 0.04em;
        }
    </style>
</head>
<body class="bg-[#070709] text-white min-h-screen flex flex-col justify-between antialiased">

    <!-- Clean Header -->
    <header class="sticky top-0 z-40 bg-[#070709]/90 backdrop-blur-md border-b border-zinc-900 px-4 py-3 safe-top flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="index.php" class="w-9 h-9 rounded-xl bg-zinc-900 border border-zinc-800 text-zinc-300 hover:text-white flex items-center justify-center text-sm shadow-sm active:scale-95 transition-all">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="font-heading text-2xl font-bold uppercase tracking-wide leading-none text-white">Ask Coach</h1>
                <p class="text-[11px] text-zinc-400">Desk & Trainer Support</p>
            </div>
        </div>

        <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-[10px] font-semibold">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
            <span>Online</span>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 max-w-md mx-auto w-full px-4 py-4 space-y-4">

        <!-- Flash Notices -->
        <?php if ($successMsg): ?>
            <div class="p-3.5 rounded-2xl bg-emerald-950/60 border border-emerald-800/60 text-emerald-300 text-xs flex items-center gap-3">
                <i class="fa-solid fa-circle-check text-emerald-400 text-sm shrink-0"></i>
                <span><?= htmlspecialchars($successMsg) ?></span>
            </div>
        <?php endif; ?>

        <?php if ($errorMsg): ?>
            <div class="p-3.5 rounded-2xl bg-red-950/60 border border-red-800/60 text-red-300 text-xs flex items-center gap-3">
                <i class="fa-solid fa-circle-exclamation text-red-400 text-sm shrink-0"></i>
                <span><?= htmlspecialchars($errorMsg) ?></span>
            </div>
        <?php endif; ?>

        <!-- Main Minimal Composer Form Card -->
        <div class="bg-zinc-950 rounded-3xl p-5 border border-zinc-800/80 shadow-xl space-y-4">
            
            <form method="POST" action="" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                <input type="hidden" name="send_coach_msg" value="1">
                <input type="hidden" name="topic" id="selectedTopicInput" value="Workout Plan">

                <!-- Clean Topic Selector Pills -->
                <div>
                    <span class="block text-[11px] font-semibold text-zinc-400 mb-2">Topic</span>
                    <div class="flex flex-wrap gap-2" id="topicList">
                        <button type="button" onclick="setTopic(this, 'Workout Plan')" class="topic-pill active px-3.5 py-2 rounded-xl bg-red-600/15 border border-red-500 text-red-400 text-xs font-semibold transition-all">
                            Workout Plan
                        </button>
                        <button type="button" onclick="setTopic(this, 'Nutrition')" class="topic-pill px-3.5 py-2 rounded-xl bg-zinc-900 border border-zinc-800 text-zinc-400 hover:text-zinc-200 text-xs font-semibold transition-all">
                            Nutrition
                        </button>
                        <button type="button" onclick="setTopic(this, 'Gate / Locker')" class="topic-pill px-3.5 py-2 rounded-xl bg-zinc-900 border border-zinc-800 text-zinc-400 hover:text-zinc-200 text-xs font-semibold transition-all">
                            Gate / Locker
                        </button>
                        <button type="button" onclick="setTopic(this, 'Membership')" class="topic-pill px-3.5 py-2 rounded-xl bg-zinc-900 border border-zinc-800 text-zinc-400 hover:text-zinc-200 text-xs font-semibold transition-all">
                            Membership
                        </button>
                    </div>
                </div>

                <!-- Message Input -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-[11px] font-semibold text-zinc-400">Message</span>
                        <span id="charCount" class="text-[10px] text-zinc-500 font-mono">0/300</span>
                    </div>
                    <textarea name="message" id="messageInput" required rows="4" maxlength="300" oninput="updateCounter(this)" placeholder="How can our coaching team help you today?..." class="w-full bg-zinc-900/90 border border-zinc-800 rounded-2xl p-3.5 text-xs text-white placeholder-zinc-500 focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500/30 transition-all resize-none"></textarea>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full py-3.5 rounded-2xl bg-red-600 hover:bg-red-500 text-white font-bold text-xs uppercase tracking-wider flex items-center justify-center gap-2 shadow-lg shadow-red-600/30 active:scale-95 transition-all">
                    <i class="fa-regular fa-paper-plane text-xs"></i>
                    <span>Send Message</span>
                </button>
            </form>

            <!-- Minimal Direct Contact Strip -->
            <div class="pt-3 border-t border-zinc-900 flex items-center justify-between text-xs text-zinc-400">
                <span class="text-[11px] text-zinc-500">Need instant reach?</span>
                <div class="flex items-center gap-3">
                    <a href="https://wa.me/923001234567?text=Hi%20GymFlow%20Coach,%20I%20am%20a%20member%20and%20need%20assistance." target="_blank" class="text-emerald-400 hover:text-emerald-300 font-semibold flex items-center gap-1">
                        <i class="fa-brands fa-whatsapp text-sm"></i>
                        <span>WhatsApp</span>
                    </a>
                    <span class="text-zinc-700">•</span>
                    <a href="tel:+923001234567" class="text-zinc-300 hover:text-white font-semibold flex items-center gap-1">
                        <i class="fa-solid fa-phone text-xs"></i>
                        <span>Call Desk</span>
                    </a>
                </div>
            </div>

        </div>

        <!-- Recent Inquiries (Minimal List) -->
        <?php if (!empty($myMessages)): ?>
            <div class="space-y-2 pt-2">
                <span class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wider block px-1">Recent Messages</span>
                <div class="space-y-2">
                    <?php foreach ($myMessages as $msg): ?>
                        <div class="bg-zinc-950 p-3.5 rounded-2xl border border-zinc-900 space-y-1">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-semibold text-zinc-200"><?= htmlspecialchars($msg['program'] ?? $msg['subject'] ?? 'Inquiry') ?></span>
                                <span class="text-[10px] font-bold uppercase <?= ($msg['status'] ?? 'unread') === 'read' ? 'text-emerald-400' : 'text-zinc-500' ?>">
                                    <?= ($msg['status'] ?? 'unread') === 'read' ? 'Replied' : 'Pending' ?>
                                </span>
                            </div>
                            <p class="text-xs text-zinc-400 line-clamp-2 leading-relaxed">
                                <?= htmlspecialchars($msg['message']) ?>
                            </p>
                            <span class="text-[10px] text-zinc-600 block"><?= date('M j, Y • h:i A', strtotime($msg['created_at'])) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

    </main>

    <script>
        function setTopic(btn, topic) {
            document.querySelectorAll('.topic-pill').forEach(el => {
                el.classList.remove('active', 'bg-red-600/15', 'border-red-500', 'text-red-400');
                el.classList.add('bg-zinc-900', 'border-zinc-800', 'text-zinc-400');
            });
            btn.classList.remove('bg-zinc-900', 'border-zinc-800', 'text-zinc-400');
            btn.classList.add('active', 'bg-red-600/15', 'border-red-500', 'text-red-400');

            document.getElementById('selectedTopicInput').value = topic;
            document.getElementById('messageInput').focus();
        }

        function updateCounter(textarea) {
            const count = document.getElementById('charCount');
            if (count) count.textContent = `${textarea.value.length}/300`;
        }
    </script>
</body>
</html>
