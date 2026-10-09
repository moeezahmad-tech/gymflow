<?php
/**
 * GymFlow - Admin Newsletter & Promotional Email Campaign Manager
 * Complete broadcast engine with audience filters (Subscribers, Members, 1-2 week absentees, etc.)
 */
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/notifications.php';

// Enforce Administrator & Staff RBAC
requireAdmin();

$activeAdminTab = 'newsletter';
$currentUser = getCurrentUser();
$pageTitle = "Promotional Broadcasts & Newsletter - " . APP_NAME;
$adminHeaderTitle = "Newsletter & Email Broadcasts";
$adminHeaderSubtitle = "Send promotional offers, re-engagement emails to absent members, and manage subscribers";

$message = null;
$error = null;
$db = Database::getConnection();

// ------------------------------------------------------------
// Ensure DB Tables Exist
// ------------------------------------------------------------
try {
    $db->exec("
        CREATE TABLE IF NOT EXISTS newsletter_subscribers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL UNIQUE,
            ip_address VARCHAR(45) NULL,
            status ENUM('subscribed', 'unsubscribed') DEFAULT 'subscribed',
            subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_n_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    $db->exec("
        CREATE TABLE IF NOT EXISTS email_campaigns (
            id INT AUTO_INCREMENT PRIMARY KEY,
            subject VARCHAR(255) NOT NULL,
            category VARCHAR(50) NOT NULL DEFAULT 'Promotion',
            target_audience VARCHAR(100) NOT NULL,
            recipient_count INT NOT NULL DEFAULT 0,
            message LONGTEXT NOT NULL,
            status ENUM('sent', 'draft', 'failed') NOT NULL DEFAULT 'sent',
            sent_by VARCHAR(100) NULL,
            sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_camp_sent (sent_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
} catch (Exception $e) {
    // Continue
}

// ------------------------------------------------------------
// Helper: Fetch Recipients based on Filter
// ------------------------------------------------------------
function getRecipientsByAudience(PDO $db, string $audience): array {
    $recipients = [];

    switch ($audience) {
        case 'all_subscribers':
            $stmt = $db->query("
                SELECT email, 'Subscriber' as full_name, '' as member_code, NULL as last_seen, NULL as days_absent, NULL as end_date, NULL as id
                FROM newsletter_subscribers 
                WHERE status = 'subscribed'
                ORDER BY id DESC
            ");
            $recipients = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;

        case 'all_members':
            $stmt = $db->query("
                SELECT u.id, u.email, u.full_name, u.member_code, NULL as last_seen, NULL as days_absent, MAX(m.end_date) as end_date
                FROM users u
                LEFT JOIN memberships m ON u.id = m.user_id AND m.status = 'active'
                WHERE u.role = 'member' AND u.status = 'active'
                GROUP BY u.id, u.email, u.full_name, u.member_code
                ORDER BY u.id DESC
            ");
            $recipients = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;

        case 'absent_7_days':
            // Members who haven't checked in for 7+ days (or never checked in)
            $stmt = $db->query("
                SELECT u.id, u.email, u.full_name, u.member_code, 
                       MAX(a.check_in_time) as last_seen,
                       COALESCE(DATEDIFF(NOW(), MAX(a.check_in_time)), 999) as days_absent,
                       MAX(m.end_date) as end_date
                FROM users u
                LEFT JOIN attendance a ON u.id = a.user_id AND a.status = 'granted'
                LEFT JOIN memberships m ON u.id = m.user_id AND m.status = 'active'
                WHERE u.role = 'member' AND u.status = 'active'
                GROUP BY u.id, u.email, u.full_name, u.member_code
                HAVING last_seen IS NULL OR last_seen <= DATE_SUB(NOW(), INTERVAL 7 DAY)
                ORDER BY days_absent DESC
            ");
            $recipients = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;

        case 'absent_14_days':
            // Members who haven't checked in for 14+ days (2 weeks)
            $stmt = $db->query("
                SELECT u.id, u.email, u.full_name, u.member_code, 
                       MAX(a.check_in_time) as last_seen,
                       COALESCE(DATEDIFF(NOW(), MAX(a.check_in_time)), 999) as days_absent,
                       MAX(m.end_date) as end_date
                FROM users u
                LEFT JOIN attendance a ON u.id = a.user_id AND a.status = 'granted'
                LEFT JOIN memberships m ON u.id = m.user_id AND m.status = 'active'
                WHERE u.role = 'member' AND u.status = 'active'
                GROUP BY u.id, u.email, u.full_name, u.member_code
                HAVING last_seen IS NULL OR last_seen <= DATE_SUB(NOW(), INTERVAL 14 DAY)
                ORDER BY days_absent DESC
            ");
            $recipients = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;

        case 'expiring_soon':
            // Active members whose membership expires within next 7 days
            $stmt = $db->query("
                SELECT u.id, u.email, u.full_name, u.member_code, NULL as last_seen, NULL as days_absent, m.end_date
                FROM users u
                JOIN memberships m ON u.id = m.user_id
                WHERE u.role = 'member' AND u.status = 'active' 
                  AND m.status = 'active'
                  AND m.end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                ORDER BY m.end_date ASC
            ");
            $recipients = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;

        case 'pending_dues':
            // Members with pending or overdue payment records
            $stmt = $db->query("
                SELECT DISTINCT u.id, u.email, u.full_name, u.member_code, NULL as last_seen, NULL as days_absent, NULL as end_date
                FROM users u
                JOIN payments p ON u.id = p.user_id
                WHERE u.role = 'member' AND p.status IN ('pending', 'overdue')
            ");
            $recipients = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;

        case 'all_contacts':
        default:
            // Deduplicated list of subscribers + members
            $stmt = $db->query("
                SELECT email, 'Subscriber' as full_name, '' as member_code, NULL as last_seen, NULL as days_absent, NULL as end_date, NULL as id
                FROM newsletter_subscribers WHERE status = 'subscribed'
                UNION
                SELECT email, full_name, member_code, NULL as last_seen, NULL as days_absent, NULL as end_date, id
                FROM users WHERE role = 'member' AND status = 'active'
            ");
            $recipients = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;
    }

    return $recipients;
}

// ------------------------------------------------------------
// Handle POST Requests (Broadcast, Test Email, Subscriber Actions)
// ------------------------------------------------------------
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Security token validation failed. Please reload and try again.";
    } else {
        $action = $_POST['form_action'] ?? '';

        // 1. BROADCAST CAMPAIGN TO AUDIENCE
        if ($action === 'send_broadcast') {
            $subject = trim($_POST['subject'] ?? '');
            $category = trim($_POST['category'] ?? 'Promotion');
            $audience = trim($_POST['target_audience'] ?? 'all_subscribers');
            $messageBody = trim($_POST['message_body'] ?? '');

            if (empty($subject) || empty($messageBody)) {
                $error = "Subject and Message Body are required to send a broadcast.";
            } else {
                $recipients = getRecipientsByAudience($db, $audience);
                $recipientCount = count($recipients);

                if ($recipientCount === 0) {
                    $error = "No recipients matched the selected audience filter ({$audience}).";
                } else {
                    $successCount = 0;
                    $appUrl = BASE_URL;

                    foreach ($recipients as $rec) {
                        $recEmail = $rec['email'];
                        if (!filter_var($recEmail, FILTER_VALIDATE_EMAIL)) continue;

                        $recName = !empty($rec['full_name']) && $rec['full_name'] !== 'Subscriber' ? $rec['full_name'] : 'Valued Fitness Fan';
                        $recCode = $rec['member_code'] ?? 'MEMBER';
                        $daysAbsent = !empty($rec['days_absent']) && (int)$rec['days_absent'] < 900 ? $rec['days_absent'] . ' days' : 'a while';
                        $endDate = !empty($rec['end_date']) ? date('M d, Y', strtotime($rec['end_date'])) : 'Soon';

                        // Replace personalization tokens
                        $customSubject = str_replace(
                            ['{NAME}', '{EMAIL}', '{MEMBER_CODE}', '{GYM_NAME}', '{DAYS_ABSENT}', '{EXPIRY_DATE}'],
                            [$recName, $recEmail, $recCode, APP_NAME, $daysAbsent, $endDate],
                            $subject
                        );

                        $customBody = str_replace(
                            ['{NAME}', '{EMAIL}', '{MEMBER_CODE}', '{GYM_NAME}', '{DAYS_ABSENT}', '{EXPIRY_DATE}', '{LOGIN_URL}'],
                            [$recName, $recEmail, $recCode, APP_NAME, $daysAbsent, $endDate, $appUrl . '/login.php'],
                            $messageBody
                        );

                        // Dispatch email
                        NotificationEngine::sendEmail($recEmail, $customSubject, $customBody, $recName, $rec['id'] ?? null);
                        $successCount++;
                    }

                    // Log campaign into email_campaigns
                    try {
                        $campStmt = $db->prepare("
                            INSERT INTO email_campaigns (subject, category, target_audience, recipient_count, message, status, sent_by, sent_at)
                            VALUES (:subj, :cat, :aud, :cnt, :msg, 'sent', :by, NOW())
                        ");
                        $campStmt->execute([
                            ':subj' => $subject,
                            ':cat'  => $category,
                            ':aud'  => $audience,
                            ':cnt'  => $successCount,
                            ':msg'  => $messageBody,
                            ':by'   => $currentUser['name'] ?? 'Admin'
                        ]);
                    } catch (Exception $e) {
                        // ignore log error
                    }

                    $message = "Broadcast successfully delivered to {$successCount} recipient(s) in category '{$category}'!";
                }
            }
        }

        // 2. SEND TEST EMAIL TO ADMIN
        elseif ($action === 'send_test_email') {
            $testEmail = trim($_POST['test_email'] ?? ($currentUser['email'] ?? 'admin@gymflow.com'));
            $subject = "[TEST PREVIEW] " . trim($_POST['subject'] ?? 'GymFlow Promotional Offer');
            $messageBody = trim($_POST['message_body'] ?? '<p>This is a test broadcast email preview.</p>');

            if (!filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
                $error = "Invalid test email address entered.";
            } else {
                $testBody = str_replace(
                    ['{NAME}', '{EMAIL}', '{MEMBER_CODE}', '{GYM_NAME}', '{DAYS_ABSENT}', '{EXPIRY_DATE}', '{LOGIN_URL}'],
                    [$currentUser['name'] ?? 'Admin Tester', $testEmail, 'TEST-001', APP_NAME, '7 days', date('M d, Y', strtotime('+30 days')), BASE_URL . '/login.php'],
                    $messageBody
                );

                NotificationEngine::sendEmail($testEmail, $subject, $testBody, $currentUser['name'] ?? 'Admin', $currentUser['id'] ?? null);
                $message = "Test email preview sent to <strong>" . htmlspecialchars($testEmail) . "</strong>!";
            }
        }

        // 3. SUBSCRIBER ACTIONS (Toggle Status / Delete / Manual Add)
        elseif ($action === 'toggle_subscriber_status') {
            $subId = (int)($_POST['subscriber_id'] ?? 0);
            $newStatus = ($_POST['current_status'] ?? '') === 'subscribed' ? 'unsubscribed' : 'subscribed';
            if ($subId > 0) {
                $stmt = $db->prepare("UPDATE newsletter_subscribers SET status = :stat WHERE id = :id");
                $stmt->execute([':stat' => $newStatus, ':id' => $subId]);
                $message = "Subscriber status updated to " . ucfirst($newStatus) . ".";
            }
        }

        elseif ($action === 'delete_subscriber') {
            $subId = (int)($_POST['subscriber_id'] ?? 0);
            if ($subId > 0) {
                $stmt = $db->prepare("DELETE FROM newsletter_subscribers WHERE id = :id");
                $stmt->execute([':id' => $subId]);
                $message = "Subscriber successfully deleted.";
            }
        }

        elseif ($action === 'add_manual_subscriber') {
            $newEmail = trim($_POST['new_email'] ?? '');
            if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
                $error = "Please provide a valid email address.";
            } else {
                try {
                    $stmt = $db->prepare("
                        INSERT INTO newsletter_subscribers (email, ip_address, status, subscribed_at)
                        VALUES (:email, 'Admin Manual Entry', 'subscribed', NOW())
                        ON DUPLICATE KEY UPDATE status = 'subscribed'
                    ");
                    $stmt->execute([':email' => $newEmail]);
                    $message = "Subscriber '{$newEmail}' added successfully!";
                } catch (Exception $e) {
                    $error = "Failed to add subscriber: " . $e->getMessage();
                }
            }
        }
    }
}

// ------------------------------------------------------------
// Fetch Audience Counts for Dynamic Badge Previews
// ------------------------------------------------------------
$audienceCounts = [
    'all_subscribers' => (int)$db->query("SELECT COUNT(*) FROM newsletter_subscribers WHERE status = 'subscribed'")->fetchColumn(),
    'all_members'     => (int)$db->query("SELECT COUNT(*) FROM users WHERE role = 'member' AND status = 'active'")->fetchColumn(),
    'absent_7_days'   => count(getRecipientsByAudience($db, 'absent_7_days')),
    'absent_14_days'  => count(getRecipientsByAudience($db, 'absent_14_days')),
    'expiring_soon'   => count(getRecipientsByAudience($db, 'expiring_soon')),
    'pending_dues'    => count(getRecipientsByAudience($db, 'pending_dues')),
    'all_contacts'    => count(getRecipientsByAudience($db, 'all_contacts'))
];

// Fetch Subscribers list
$subSearch = trim($_GET['sub_q'] ?? '');
$subFilter = $_GET['sub_status'] ?? 'all';
$subSql = "SELECT * FROM newsletter_subscribers WHERE 1=1";
$subParams = [];

if ($subFilter === 'subscribed' || $subFilter === 'unsubscribed') {
    $subSql .= " AND status = :stat";
    $subParams[':stat'] = $subFilter;
}
if (!empty($subSearch)) {
    $subSql .= " AND (email LIKE :sq OR ip_address LIKE :sq)";
    $subParams[':sq'] = "%{$subSearch}%";
}
$subSql .= " ORDER BY id DESC";
$subStmt = $db->prepare($subSql);
$subStmt->execute($subParams);
$subscribers = $subStmt->fetchAll();

// Fetch Campaigns history
$campaigns = $db->query("SELECT * FROM email_campaigns ORDER BY id DESC LIMIT 50")->fetchAll();

// Modular Head
require_once __DIR__ . '/../components/head.php';
?>

<div class="min-h-screen bg-[#050507] flex flex-col md:flex-row text-zinc-100 font-sans antialiased">
    <!-- Sidebar -->
    <?php require_once __DIR__ . '/../components/admin-sidebar.php'; ?>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        <!-- Admin Top Navigation -->
        <?php require_once __DIR__ . '/../components/admin-header.php'; ?>

        <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 pb-24 md:pb-8 space-y-8">

            <!-- Alerts -->
            <?php if ($message): ?>
                <div class="p-4 rounded-2xl bg-emerald-950/80 border border-emerald-800 text-emerald-300 text-sm flex items-center justify-between shadow-xl animate-fade-in">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-circle-check text-emerald-400 text-xl shrink-0"></i>
                        <span><?= $message ?></span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-white px-2 py-1"><i class="fa-solid fa-xmark"></i></button>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="p-4 rounded-2xl bg-red-950/80 border border-red-800 text-red-300 text-sm flex items-center justify-between shadow-xl animate-fade-in">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-circle-exclamation text-red-400 text-xl shrink-0"></i>
                        <span><?= htmlspecialchars($error) ?></span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-red-400 hover:text-white px-2 py-1"><i class="fa-solid fa-xmark"></i></button>
                </div>
            <?php endif; ?>

            <!-- Metric Header Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Stat 1: Total Subscribers -->
                <div class="p-5 rounded-2xl bg-zinc-950/80 border border-zinc-900 flex items-center justify-between shadow-sm relative overflow-hidden group hover:border-red-500/30 transition-all">
                    <div class="space-y-1">
                        <span class="text-xs uppercase font-bold text-zinc-400 tracking-wider">Active Subscribers</span>
                        <div class="text-3xl font-heading font-extrabold text-white">
                            <?= number_format($audienceCounts['all_subscribers']) ?>
                        </div>
                        <span class="text-[11px] text-emerald-400 flex items-center gap-1">
                            <i class="fa-solid fa-envelope-circle-check"></i> Ready for Broadcast
                        </span>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-red-500/10 border border-red-500/20 flex items-center justify-center text-red-500 text-xl group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-users-viewfinder"></i>
                    </div>
                </div>

                <!-- Stat 2: Absent 1-2 Weeks Members -->
                <div class="p-5 rounded-2xl bg-zinc-950/80 border border-zinc-900 flex items-center justify-between shadow-sm relative overflow-hidden group hover:border-amber-500/30 transition-all">
                    <div class="space-y-1">
                        <span class="text-xs uppercase font-bold text-zinc-400 tracking-wider">Absent 1-2 Weeks</span>
                        <div class="text-3xl font-heading font-extrabold text-amber-400">
                            <?= number_format($audienceCounts['absent_7_days']) ?>
                        </div>
                        <span class="text-[11px] text-zinc-400">
                            <?= number_format($audienceCounts['absent_14_days']) ?> absent for 14+ days
                        </span>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-500 text-xl group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-user-clock"></i>
                    </div>
                </div>

                <!-- Stat 3: Registered Gym Members -->
                <div class="p-5 rounded-2xl bg-zinc-950/80 border border-zinc-900 flex items-center justify-between shadow-sm relative overflow-hidden group hover:border-blue-500/30 transition-all">
                    <div class="space-y-1">
                        <span class="text-xs uppercase font-bold text-zinc-400 tracking-wider">Active Members</span>
                        <div class="text-3xl font-heading font-extrabold text-white">
                            <?= number_format($audienceCounts['all_members']) ?>
                        </div>
                        <span class="text-[11px] text-zinc-400">Registered Portal Users</span>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-500 text-xl group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-address-card"></i>
                    </div>
                </div>

                <!-- Stat 4: Campaigns Sent -->
                <div class="p-5 rounded-2xl bg-zinc-950/80 border border-zinc-900 flex items-center justify-between shadow-sm relative overflow-hidden group hover:border-emerald-500/30 transition-all">
                    <div class="space-y-1">
                        <span class="text-xs uppercase font-bold text-zinc-400 tracking-wider">Campaigns Sent</span>
                        <div class="text-3xl font-heading font-extrabold text-white">
                            <?= count($campaigns) ?>
                        </div>
                        <span class="text-[11px] text-emerald-400">Tracked in History</span>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-500 text-xl group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-paper-plane"></i>
                    </div>
                </div>
            </div>

            <!-- Tab Navigation (Broadcast Composer, Subscriber List, Campaign History) -->
            <div class="flex border-b border-zinc-800 gap-2 sm:gap-6 overflow-x-auto text-xs uppercase font-bold tracking-wider pb-px">
                <button onclick="switchTab('composer')" id="tabBtn-composer" class="tab-btn px-4 py-3 border-b-2 border-red-500 text-white flex items-center gap-2 transition-all cursor-pointer">
                    <i class="fa-solid fa-bullhorn text-red-500"></i> Broadcast Composer
                </button>
                <button onclick="switchTab('subscribers')" id="tabBtn-subscribers" class="tab-btn px-4 py-3 border-b-2 border-transparent text-zinc-400 hover:text-white flex items-center gap-2 transition-all cursor-pointer">
                    <i class="fa-solid fa-list-check"></i> Subscribers Directory (<?= count($subscribers) ?>)
                </button>
                <button onclick="switchTab('history')" id="tabBtn-history" class="tab-btn px-4 py-3 border-b-2 border-transparent text-zinc-400 hover:text-white flex items-center gap-2 transition-all cursor-pointer">
                    <i class="fa-solid fa-clock-rotate-left"></i> Campaign History (<?= count($campaigns) ?>)
                </button>
            </div>

            <!-- ============================================================ -->
            <!-- TAB 1: BROADCAST COMPOSER -->
            <!-- ============================================================ -->
            <div id="tab-composer" class="tab-pane space-y-8">
                
                <!-- Quick Template Selectors -->
                <div class="bg-zinc-950/80 border border-zinc-900 rounded-3xl p-6 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <h3 class="font-heading text-base font-bold text-white uppercase tracking-wider flex items-center gap-2">
                                <i class="fa-solid fa-wand-magic-sparkles text-red-500"></i> Pre-Built High-Converting Templates
                            </h3>
                            <p class="text-xs text-zinc-400">Click a template below to auto-fill subject, filter, and marketing copy.</p>
                        </div>
                        <span class="text-[10px] uppercase font-bold px-2.5 py-1 rounded-full bg-red-500/10 border border-red-500/20 text-red-400">
                            Instant 1-Click Load
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                        <!-- Template 1: Absent 1-2 Weeks "We Miss You" -->
                        <button type="button" onclick="loadTemplate('absent_miss_you')" class="p-4 rounded-2xl bg-zinc-900/90 border border-zinc-800 hover:border-amber-500/50 text-left transition-all group hover:bg-zinc-900 flex flex-col justify-between cursor-pointer">
                            <div class="space-y-1">
                                <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase bg-amber-500/20 text-amber-400 border border-amber-500/30">Absent 1-2 Weeks</span>
                                <h4 class="font-heading text-sm font-bold text-white group-hover:text-amber-400 transition-colors">"We Miss You at GymFlow!"</h4>
                                <p class="text-[11px] text-zinc-400 line-clamp-2">Re-engage inactive members with a 20% discount or free personal coaching session.</p>
                            </div>
                            <div class="mt-3 text-[10px] text-amber-400 font-bold flex items-center gap-1">
                                Load Template <i class="fa-solid fa-arrow-right text-[9px] group-hover:translate-x-1 transition-transform"></i>
                            </div>
                        </button>

                        <!-- Template 2: Flash Promo Deal -->
                        <button type="button" onclick="loadTemplate('flash_promo')" class="p-4 rounded-2xl bg-zinc-900/90 border border-zinc-800 hover:border-red-500/50 text-left transition-all group hover:bg-zinc-900 flex flex-col justify-between cursor-pointer">
                            <div class="space-y-1">
                                <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase bg-red-500/20 text-red-400 border border-red-500/30">All Subscribers</span>
                                <h4 class="font-heading text-sm font-bold text-white group-hover:text-red-400 transition-colors">Weekend VIP Flash Sale</h4>
                                <p class="text-[11px] text-zinc-400 line-clamp-2">Promote 30% off upgrades, sauna access, and guest passes with 48h urgency.</p>
                            </div>
                            <div class="mt-3 text-[10px] text-red-400 font-bold flex items-center gap-1">
                                Load Template <i class="fa-solid fa-arrow-right text-[9px] group-hover:translate-x-1 transition-transform"></i>
                            </div>
                        </button>

                        <!-- Template 3: New Fitness Classes / Announcement -->
                        <button type="button" onclick="loadTemplate('new_classes')" class="p-4 rounded-2xl bg-zinc-900/90 border border-zinc-800 hover:border-blue-500/50 text-left transition-all group hover:bg-zinc-900 flex flex-col justify-between cursor-pointer">
                            <div class="space-y-1">
                                <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase bg-blue-500/20 text-blue-400 border border-blue-500/30">All Contacts</span>
                                <h4 class="font-heading text-sm font-bold text-white group-hover:text-blue-400 transition-colors">New Classes & Equipment</h4>
                                <p class="text-[11px] text-zinc-400 line-clamp-2">Announce newly added HYROX, Boxing, and Olympic Lifting workshops.</p>
                            </div>
                            <div class="mt-3 text-[10px] text-blue-400 font-bold flex items-center gap-1">
                                Load Template <i class="fa-solid fa-arrow-right text-[9px] group-hover:translate-x-1 transition-transform"></i>
                            </div>
                        </button>

                        <!-- Template 4: Membership Renewal Reminder -->
                        <button type="button" onclick="loadTemplate('renewal_alert')" class="p-4 rounded-2xl bg-zinc-900/90 border border-zinc-800 hover:border-emerald-500/50 text-left transition-all group hover:bg-zinc-900 flex flex-col justify-between cursor-pointer">
                            <div class="space-y-1">
                                <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">Expiring Members</span>
                                <h4 class="font-heading text-sm font-bold text-white group-hover:text-emerald-400 transition-colors">Renewal & Uninterrupted Pass</h4>
                                <p class="text-[11px] text-zinc-400 line-clamp-2">Encourage expiring members to lock in low rates without turnstile pause.</p>
                            </div>
                            <div class="mt-3 text-[10px] text-emerald-400 font-bold flex items-center gap-1">
                                Load Template <i class="fa-solid fa-arrow-right text-[9px] group-hover:translate-x-1 transition-transform"></i>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Main Broadcast Form & Live Preview Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                    
                    <!-- Form Column -->
                    <div class="lg:col-span-7 bg-zinc-950/80 border border-zinc-900 rounded-3xl p-6 sm:p-8 space-y-6">
                        <form id="broadcastForm" action="<?= url('admin/newsletter.php') ?>" method="POST" class="space-y-6">
                            <?= getCSRFTokenInput() ?>
                            <input type="hidden" name="form_action" value="send_broadcast">

                            <!-- Target Audience Filter -->
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <label for="target_audience" class="text-xs uppercase font-bold text-zinc-300 tracking-wider flex items-center gap-2">
                                        <i class="fa-solid fa-filter text-red-500"></i> Target Recipient Filter
                                    </label>
                                    <span id="recipientCountBadge" class="text-[11px] font-bold px-3 py-0.5 rounded-full bg-red-500/10 border border-red-500/20 text-red-400">
                                        <?= $audienceCounts['all_subscribers'] ?> recipients selected
                                    </span>
                                </div>
                                <select id="target_audience" name="target_audience" onchange="updateAudienceBadge()" class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-red-500 transition-colors">
                                    <option value="all_subscribers" data-count="<?= $audienceCounts['all_subscribers'] ?>">All Newsletter Subscribers (<?= $audienceCounts['all_subscribers'] ?>)</option>
                                    <option value="absent_7_days" data-count="<?= $audienceCounts['absent_7_days'] ?>">Members Absent 1+ Week (7+ Days Inactive) (<?= $audienceCounts['absent_7_days'] ?>)</option>
                                    <option value="absent_14_days" data-count="<?= $audienceCounts['absent_14_days'] ?>">Members Absent 2+ Weeks (14+ Days Inactive) (<?= $audienceCounts['absent_14_days'] ?>)</option>
                                    <option value="all_members" data-count="<?= $audienceCounts['all_members'] ?>">All Active Registered Members (<?= $audienceCounts['all_members'] ?>)</option>
                                    <option value="expiring_soon" data-count="<?= $audienceCounts['expiring_soon'] ?>">Expiring Memberships (Next 7 Days) (<?= $audienceCounts['expiring_soon'] ?>)</option>
                                    <option value="pending_dues" data-count="<?= $audienceCounts['pending_dues'] ?>">Members with Pending / Overdue Dues (<?= $audienceCounts['pending_dues'] ?>)</option>
                                    <option value="all_contacts" data-count="<?= $audienceCounts['all_contacts'] ?>">All Combined Contacts (Subscribers + Members) (<?= $audienceCounts['all_contacts'] ?>)</option>
                                </select>
                                <p class="text-[11px] text-zinc-500">Filters dynamically scan real-time member check-ins and newsletter database.</p>
                            </div>

                            <!-- Campaign Category / Tag -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <label for="category" class="text-xs uppercase font-bold text-zinc-300 tracking-wider">Campaign Category</label>
                                    <select id="category" name="category" class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-red-500 transition-colors">
                                        <option value="Promotional Deal">Promotional Deal / Offer</option>
                                        <option value="Re-engagement (Absent)">We Miss You / Re-engagement</option>
                                        <option value="Gym Announcement">Gym Announcement / News</option>
                                        <option value="Weekly Newsletter">Weekly Fitness Newsletter</option>
                                        <option value="Renewal Alert">Membership Renewal Notice</option>
                                    </select>
                                </div>

                                <div class="space-y-2">
                                    <label class="text-xs uppercase font-bold text-zinc-300 tracking-wider">Dynamic Placeholders</label>
                                    <div class="flex flex-wrap gap-1.5 pt-1">
                                        <button type="button" onclick="insertToken('{NAME}')" class="px-2 py-1 rounded bg-zinc-900 border border-zinc-800 hover:border-red-500 text-[10px] text-zinc-300 font-mono cursor-pointer">{NAME}</button>
                                        <button type="button" onclick="insertToken('{MEMBER_CODE}')" class="px-2 py-1 rounded bg-zinc-900 border border-zinc-800 hover:border-red-500 text-[10px] text-zinc-300 font-mono cursor-pointer">{MEMBER_CODE}</button>
                                        <button type="button" onclick="insertToken('{DAYS_ABSENT}')" class="px-2 py-1 rounded bg-zinc-900 border border-zinc-800 hover:border-red-500 text-[10px] text-zinc-300 font-mono cursor-pointer">{DAYS_ABSENT}</button>
                                        <button type="button" onclick="insertToken('{LOGIN_URL}')" class="px-2 py-1 rounded bg-zinc-900 border border-zinc-800 hover:border-red-500 text-[10px] text-zinc-300 font-mono cursor-pointer">{LOGIN_URL}</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Subject Line -->
                            <div class="space-y-2">
                                <label for="subject" class="text-xs uppercase font-bold text-zinc-300 tracking-wider flex items-center justify-between">
                                    <span>Subject Line</span>
                                    <span class="text-[10px] text-zinc-500 font-normal">Supports {NAME} token</span>
                                </label>
                                <input type="text" id="subject" name="subject" required oninput="renderLivePreview()" placeholder="e.g. We Miss You at GymFlow, {NAME}! Here is 20% Off" class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-red-500 transition-colors">
                            </div>

                            <!-- Message Content (HTML Supported) -->
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <label for="message_body" class="text-xs uppercase font-bold text-zinc-300 tracking-wider">Email HTML Message Body</label>
                                    <span class="text-[11px] text-zinc-500">HTML & Inline styles supported</span>
                                </div>
                                <textarea id="message_body" name="message_body" rows="10" required oninput="renderLivePreview()" placeholder="Enter your email message here..." class="w-full bg-zinc-900 border border-zinc-800 rounded-xl p-4 text-sm text-white font-mono leading-relaxed focus:outline-none focus:border-red-500 transition-colors"></textarea>
                            </div>

                            <!-- Action Buttons -->
                            <div class="pt-2 flex flex-col sm:flex-row items-center gap-4">
                                <button type="submit" onclick="return confirmBroadcast()" class="w-full sm:w-auto flex-1 btn-primary py-3.5 px-6 rounded-xl font-bold uppercase tracking-wider text-sm flex items-center justify-center gap-2 shadow-lg shadow-red-600/30 cursor-pointer">
                                    <i class="fa-solid fa-paper-plane"></i> Send Broadcast to Filtered Audience
                                </button>
                            </div>
                        </form>

                        <!-- Send Test Email Tool -->
                        <div class="pt-6 border-t border-zinc-900">
                            <form action="<?= url('admin/newsletter.php') ?>" method="POST" class="flex flex-col sm:flex-row gap-3">
                                <?= getCSRFTokenInput() ?>
                                <input type="hidden" name="form_action" value="send_test_email">
                                <input type="hidden" id="test_subject_sync" name="subject" value="">
                                <input type="hidden" id="test_body_sync" name="message_body" value="">
                                
                                <input type="email" name="test_email" value="<?= htmlspecialchars($currentUser['email'] ?? 'admin@gymflow.com') ?>" placeholder="Enter test recipient email" class="flex-1 bg-zinc-900 border border-zinc-800 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-zinc-700">
                                <button type="submit" onclick="syncTestEmailData()" class="px-5 py-2.5 rounded-xl border border-zinc-800 bg-zinc-900 hover:bg-zinc-800 text-zinc-300 hover:text-white text-xs font-bold uppercase tracking-wider flex items-center justify-center gap-2 transition-all cursor-pointer">
                                    <i class="fa-solid fa-vial"></i> Send Test Preview
                                </button>
                            </form>
                            <p class="text-[10px] text-zinc-500 mt-2">Sends 1 test copy using the current subject and body before sending the full broadcast.</p>
                        </div>
                    </div>

                    <!-- Live Email Preview Column -->
                    <div class="lg:col-span-5 space-y-4">
                        <div class="bg-zinc-950/80 border border-zinc-900 rounded-3xl p-5 space-y-4 sticky top-6">
                            <div class="flex items-center justify-between pb-3 border-b border-zinc-900">
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-display text-red-500 text-sm"></i>
                                    <span class="text-xs uppercase font-bold text-white tracking-wider">Live Email Render Preview</span>
                                </div>
                                <span class="text-[10px] uppercase font-bold text-zinc-500">Dark Sanctuary Theme</span>
                            </div>

                            <!-- Subject Header Preview -->
                            <div class="bg-zinc-900/60 p-3 rounded-xl border border-zinc-900 text-xs">
                                <span class="text-zinc-500">Subject:</span>
                                <span id="previewSubjectText" class="font-bold text-white ml-1">We Miss You at GymFlow, Alex!</span>
                            </div>

                            <!-- Styled Email Canvas Mockup -->
                            <div class="border border-zinc-800 rounded-2xl overflow-hidden bg-[#09090b] shadow-2xl">
                                <!-- GymFlow Email Header -->
                                <div class="bg-gradient-to-r from-zinc-900 to-black p-4 text-center border-b-2 border-red-600">
                                    <div class="font-heading text-xl font-extrabold text-white tracking-wider">
                                        GYM<span class="text-red-500">FLOW</span>
                                    </div>
                                    <span class="text-[9px] uppercase tracking-[0.25em] text-zinc-400 font-bold block">Premium Fitness Sanctuary</span>
                                </div>

                                <!-- Dynamic Email Body Content -->
                                <div id="previewEmailBody" class="p-5 text-xs text-zinc-300 space-y-4 leading-relaxed max-h-[380px] overflow-y-auto">
                                    <!-- Rendered dynamically via JS -->
                                </div>

                                <!-- Email Footer -->
                                <div class="bg-[#0a0a0c] p-4 text-center text-[10px] text-zinc-500 border-t border-zinc-900 space-y-1">
                                    <p>You received this email because you are a registered member or subscriber of <strong>GymFlow</strong>.</p>
                                    <p>Lahore Gym HQ • 24/7 Access Sanctuary • +92 300 1234567</p>
                                    <div class="text-zinc-600 text-[9px] pt-1">Privacy Policy | Unsubscribe</div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- ============================================================ -->
            <!-- TAB 2: SUBSCRIBERS DIRECTORY -->
            <!-- ============================================================ -->
            <div id="tab-subscribers" class="tab-pane hidden space-y-6">
                
                <!-- Filter Bar & Add Subscriber -->
                <div class="bg-zinc-950/80 border border-zinc-900 rounded-3xl p-6 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    <!-- Search & Status filter -->
                    <form method="GET" action="<?= url('admin/newsletter.php') ?>" class="flex flex-wrap items-center gap-3 flex-1">
                        <input type="hidden" name="tab" value="subscribers">
                        <div class="relative flex-1 min-w-[220px]">
                            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-zinc-500 text-xs"></i>
                            <input type="text" name="sub_q" value="<?= htmlspecialchars($subSearch) ?>" placeholder="Search subscriber email or IP..." class="w-full bg-zinc-900 border border-zinc-800 rounded-xl pl-9 pr-4 py-2.5 text-xs text-white focus:outline-none focus:border-red-500">
                        </div>

                        <select name="sub_status" class="bg-zinc-900 border border-zinc-800 rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none focus:border-red-500">
                            <option value="all" <?= $subFilter === 'all' ? 'selected' : '' ?>>All Statuses</option>
                            <option value="subscribed" <?= $subFilter === 'subscribed' ? 'selected' : '' ?>>Subscribed</option>
                            <option value="unsubscribed" <?= $subFilter === 'unsubscribed' ? 'selected' : '' ?>>Unsubscribed</option>
                        </select>

                        <button type="submit" class="px-4 py-2.5 rounded-xl bg-zinc-900 border border-zinc-800 hover:bg-zinc-800 text-white text-xs font-bold uppercase tracking-wider transition-colors">
                            Filter
                        </button>
                    </form>

                    <!-- Add Subscriber Manually -->
                    <form method="POST" action="<?= url('admin/newsletter.php') ?>" class="flex items-center gap-2">
                        <?= getCSRFTokenInput() ?>
                        <input type="hidden" name="form_action" value="add_manual_subscriber">
                        <input type="email" name="new_email" required placeholder="Add new subscriber email" class="bg-zinc-900 border border-zinc-800 rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none focus:border-red-500 min-w-[200px]">
                        <button type="submit" class="px-4 py-2.5 rounded-xl btn-primary text-xs font-bold uppercase tracking-wider flex items-center gap-1.5 shrink-0 cursor-pointer">
                            <i class="fa-solid fa-plus"></i> Add
                        </button>
                    </form>
                </div>

                <!-- Subscribers Table -->
                <div class="bg-zinc-950/80 border border-zinc-900 rounded-3xl overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-zinc-300">
                            <thead class="bg-zinc-900/60 text-[10px] uppercase font-bold text-zinc-400 tracking-wider border-b border-zinc-800">
                                <tr>
                                    <th class="p-4"># ID</th>
                                    <th class="p-4">Subscriber Email</th>
                                    <th class="p-4">IP Address</th>
                                    <th class="p-4">Subscribed Date</th>
                                    <th class="p-4">Status</th>
                                    <th class="p-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-900">
                                <?php if (empty($subscribers)): ?>
                                    <tr>
                                        <td colspan="6" class="p-8 text-center text-zinc-500">
                                            <i class="fa-regular fa-envelope-open text-3xl mb-2 block"></i>
                                            No subscribers found matching your criteria.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($subscribers as $sub): ?>
                                        <tr class="hover:bg-zinc-900/40 transition-colors">
                                            <td class="p-4 font-mono text-zinc-500">#<?= $sub['id'] ?></td>
                                            <td class="p-4 font-bold text-white">
                                                <div class="flex items-center gap-2">
                                                    <i class="fa-regular fa-envelope text-red-400"></i>
                                                    <?= htmlspecialchars($sub['email']) ?>
                                                </div>
                                            </td>
                                            <td class="p-4 text-zinc-400 font-mono text-[11px]"><?= htmlspecialchars($sub['ip_address'] ?? 'Unknown') ?></td>
                                            <td class="p-4 text-zinc-400"><?= date('M d, Y H:i', strtotime($sub['subscribed_at'])) ?></td>
                                            <td class="p-4">
                                                <?php if ($sub['status'] === 'subscribed'): ?>
                                                    <span class="px-2.5 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 font-bold text-[10px] uppercase">
                                                        Subscribed
                                                    </span>
                                                <?php else: ?>
                                                    <span class="px-2.5 py-1 rounded-full bg-zinc-800 border border-zinc-700 text-zinc-400 font-bold text-[10px] uppercase">
                                                        Unsubscribed
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="p-4 text-right">
                                                <div class="flex items-center justify-end gap-2">
                                                    <!-- Toggle Status -->
                                                    <form method="POST" action="<?= url('admin/newsletter.php') ?>" class="inline">
                                                        <?= getCSRFTokenInput() ?>
                                                        <input type="hidden" name="form_action" value="toggle_subscriber_status">
                                                        <input type="hidden" name="subscriber_id" value="<?= $sub['id'] ?>">
                                                        <input type="hidden" name="current_status" value="<?= $sub['status'] ?>">
                                                        <button type="submit" title="Toggle Status" class="p-2 rounded-lg bg-zinc-900 border border-zinc-800 hover:text-white text-zinc-400 hover:border-zinc-700 transition-colors cursor-pointer">
                                                            <i class="fa-solid fa-power-off text-xs"></i>
                                                        </button>
                                                    </form>

                                                    <!-- Delete -->
                                                    <form method="POST" action="<?= url('admin/newsletter.php') ?>" onsubmit="return confirm('Delete this subscriber?');" class="inline">
                                                        <?= getCSRFTokenInput() ?>
                                                        <input type="hidden" name="form_action" value="delete_subscriber">
                                                        <input type="hidden" name="subscriber_id" value="<?= $sub['id'] ?>">
                                                        <button type="submit" title="Delete" class="p-2 rounded-lg bg-zinc-900 border border-zinc-800 hover:text-red-400 hover:border-red-500/50 text-zinc-400 transition-colors cursor-pointer">
                                                            <i class="fa-solid fa-trash text-xs"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- TAB 3: CAMPAIGN HISTORY -->
            <!-- ============================================================ -->
            <div id="tab-history" class="tab-pane hidden space-y-6">
                <div class="bg-zinc-950/80 border border-zinc-900 rounded-3xl overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-zinc-300">
                            <thead class="bg-zinc-900/60 text-[10px] uppercase font-bold text-zinc-400 tracking-wider border-b border-zinc-800">
                                <tr>
                                    <th class="p-4"># ID</th>
                                    <th class="p-4">Subject</th>
                                    <th class="p-4">Category</th>
                                    <th class="p-4">Target Audience</th>
                                    <th class="p-4">Recipients</th>
                                    <th class="p-4">Sent At</th>
                                    <th class="p-4 text-right">View Copy</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-900">
                                <?php if (empty($campaigns)): ?>
                                    <tr>
                                        <td colspan="7" class="p-8 text-center text-zinc-500">
                                            <i class="fa-solid fa-bullhorn text-3xl mb-2 block"></i>
                                            No email campaigns sent yet. Compose your first broadcast in the tab above.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($campaigns as $camp): ?>
                                        <tr class="hover:bg-zinc-900/40 transition-colors">
                                            <td class="p-4 font-mono text-zinc-500">#<?= $camp['id'] ?></td>
                                            <td class="p-4 font-bold text-white"><?= htmlspecialchars($camp['subject']) ?></td>
                                            <td class="p-4">
                                                <span class="px-2.5 py-1 rounded-full bg-red-500/10 border border-red-500/20 text-red-400 font-bold text-[10px] uppercase">
                                                    <?= htmlspecialchars($camp['category']) ?>
                                                </span>
                                            </td>
                                            <td class="p-4 text-zinc-300 font-mono text-[11px]">
                                                <?= htmlspecialchars($camp['target_audience']) ?>
                                            </td>
                                            <td class="p-4 font-bold text-emerald-400">
                                                <i class="fa-solid fa-paper-plane mr-1 text-[10px]"></i> <?= number_format($camp['recipient_count']) ?> members
                                            </td>
                                            <td class="p-4 text-zinc-400"><?= date('M d, Y H:i', strtotime($camp['sent_at'])) ?></td>
                                            <td class="p-4 text-right">
                                                <button type="button" onclick='previewHistoricalCampaign(<?= json_encode($camp['subject']) ?>, <?= json_encode($camp['message']) ?>)' class="px-3 py-1.5 rounded-lg bg-zinc-900 border border-zinc-800 hover:bg-zinc-800 text-zinc-300 hover:text-white text-xs font-bold transition-all cursor-pointer">
                                                    <i class="fa-regular fa-eye mr-1"></i> Preview
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </main>
    </div>
</div>

<!-- Modal for Viewing Historical Message -->
<div id="historyModal" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-zinc-950 border border-zinc-800 rounded-3xl max-w-2xl w-full p-6 space-y-4 shadow-2xl">
        <div class="flex items-center justify-between pb-3 border-b border-zinc-900">
            <h3 id="modalSubject" class="font-heading text-lg font-bold text-white"></h3>
            <button onclick="document.getElementById('historyModal').classList.add('hidden')" class="text-zinc-400 hover:text-white"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        <div id="modalContent" class="bg-[#09090b] border border-zinc-900 rounded-2xl p-5 text-sm text-zinc-300 max-h-[400px] overflow-y-auto leading-relaxed"></div>
        <div class="flex justify-end">
            <button onclick="document.getElementById('historyModal').classList.add('hidden')" class="px-5 py-2.5 rounded-xl bg-zinc-900 border border-zinc-800 text-white text-xs font-bold uppercase tracking-wider hover:bg-zinc-800">Close</button>
        </div>
    </div>
</div>

<script>
// Templates Dictionary
const emailTemplates = {
    absent_miss_you: {
        audience: 'absent_7_days',
        category: 'Re-engagement (Absent)',
        subject: 'We Miss You at GymFlow, {NAME}! Here is 20% Off to Get You Back in Flow 🔥',
        body: `<h2>Hey {NAME}, Your GymFlow Sanctuary Awaits!</h2>
<p>We noticed that it's been {DAYS_ABSENT} since your last training session at GymFlow. Life gets busy, but your fitness goals and energy shouldn't take a back seat!</p>

<div class="highlight-box">
    <h3 style="color:#ef4444; margin-bottom:8px;">🔥 Special Welcome-Back Gift</h3>
    <p style="margin:0;">Use VIP Code <strong style="color:#ffffff; font-family:monospace; background:#27272a; padding:3px 8px; border-radius:4px;">REFLOW20</strong> for <strong>20% OFF</strong> your next package renewal or a complimentary 1-on-1 Personal Trainer assessment.</p>
</div>

<p>Our locker rooms are refreshed, Finnish saunas are heated to perfection, and our coaches are ready to help you hit new PRs.</p>

<div class="btn-container">
    <a href="{LOGIN_URL}" class="btn">Log In & Book Your Return Session</a>
</div>

<p style="font-size:13px; color:#a1a1aa; text-align:center;">Member Code: <strong>{MEMBER_CODE}</strong> &bull; Turnstiles Active 24/7</p>`
    },

    flash_promo: {
        audience: 'all_subscribers',
        category: 'Promotional Deal',
        subject: '⚡ 48-Hour Flash VIP Deal: 30% Off Semi-Annual & Annual Memberships!',
        body: `<h2>Level Up Your Training with 30% Off!</h2>
<p>For the next 48 hours only, GymFlow is unlocking our highest savings tier of the season. Upgrade your membership or lock in a multi-month package at unmatched rates.</p>

<div class="highlight-box">
    <h3 style="color:#ef4444; margin-bottom:8px;">✨ What's Included with VIP Access:</h3>
    <ul style="margin:0; padding-left:20px; color:#d4d4d8;">
        <li>24/7 All-Sanctuary Access with biometric keycard</li>
        <li>Unlimited Boxing, HIIT, and Power Yoga classes</li>
        <li>Sauna & Cold Plunge hydrotherapy recovery suite</li>
        <li>Free Monthly InBody Body Composition scan</li>
    </ul>
</div>

<div class="btn-container">
    <a href="{LOGIN_URL}" class="btn">Claim 30% Discount Online</a>
</div>

<p style="font-size:13px; color:#a1a1aa; text-align:center;">Offer valid until Sunday midnight. Terms & Conditions apply.</p>`
    },

    new_classes: {
        audience: 'all_contacts',
        category: 'Gym Announcement',
        subject: '🔥 Exciting News: HYROX Training, Boxing Ring & New Recovery Suite at GymFlow',
        body: `<h2>New Classes & Facilities Are Live!</h2>
<p>Dear {NAME}, we are thrilled to announce brand new additions to the GymFlow training floor starting this week.</p>

<div class="highlight-box">
    <h3 style="color:#ef4444; margin-bottom:8px;">🥊 What's New This Month:</h3>
    <ul style="margin:0; padding-left:20px; color:#d4d4d8;">
        <li><strong>HYROX Race Conditioning:</strong> High-intensity strength & endurance sessions.</li>
        <li><strong>Pro Boxing Ring:</strong> Technique sparring and bag drills with Coach Marcus.</li>
        <li><strong>Hydration Bar:</strong> Cold pressed protein shakes & electrolytes.</li>
    </ul>
</div>

<div class="btn-container">
    <a href="{LOGIN_URL}" class="btn">View Class Schedule & Reserve</a>
</div>`
    },

    renewal_alert: {
        audience: 'expiring_soon',
        category: 'Renewal Alert',
        subject: '⏰ Action Required: Your GymFlow Access Expires on {EXPIRY_DATE}',
        body: `<h2>Keep Your Fitness Momentum Going!</h2>
<p>Dear {NAME}, your current GymFlow membership pass is scheduled to conclude on <strong>{EXPIRY_DATE}</strong>.</p>

<p>To prevent any disruption to your 24/7 keycard turnstile access, sauna bookings, and locker privileges, you can renew your pass online in under 60 seconds.</p>

<div class="btn-container">
    <a href="{LOGIN_URL}" class="btn">Renew Membership Online</a>
</div>

<p style="font-size:13px; color:#a1a1aa; text-align:center;">Need help with renewal? Reply to this email or visit the front desk desk anytime.</p>`
    }
};

function loadTemplate(key) {
    const tpl = emailTemplates[key];
    if (!tpl) return;

    document.getElementById('target_audience').value = tpl.audience;
    document.getElementById('category').value = tpl.category;
    document.getElementById('subject').value = tpl.subject;
    document.getElementById('message_body').value = tpl.body;

    updateAudienceBadge();
    renderLivePreview();
}

function updateAudienceBadge() {
    const select = document.getElementById('target_audience');
    const selectedOption = select.options[select.selectedIndex];
    const count = selectedOption.getAttribute('data-count') || '0';
    document.getElementById('recipientCountBadge').textContent = `${count} recipients selected`;
}

function insertToken(token) {
    const textarea = document.getElementById('message_body');
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const text = textarea.value;
    textarea.value = text.substring(0, start) + token + text.substring(end);
    textarea.focus();
    textarea.selectionStart = textarea.selectionEnd = start + token.length;
    renderLivePreview();
}

function renderLivePreview() {
    const subject = document.getElementById('subject').value || 'Subject Line Preview';
    const body = document.getElementById('message_body').value || '<p>Type in the composer to see live preview...</p>';

    // Mock token replacement for preview
    const renderedSubject = subject.replace(/\{NAME\}/g, 'Alex Johnson');
    const renderedBody = body
        .replace(/\{NAME\}/g, 'Alex Johnson')
        .replace(/\{DAYS_ABSENT\}/g, '12 days')
        .replace(/\{MEMBER_CODE\}/g, 'GF-2026-3')
        .replace(/\{EXPIRY_DATE\}/g, 'Nov 15, 2026')
        .replace(/\{LOGIN_URL\}/g, '#');

    document.getElementById('previewSubjectText').textContent = renderedSubject;
    document.getElementById('previewEmailBody').innerHTML = renderedBody;
}

function syncTestEmailData() {
    document.getElementById('test_subject_sync').value = document.getElementById('subject').value;
    document.getElementById('test_body_sync').value = document.getElementById('message_body').value;
}

function confirmBroadcast() {
    const select = document.getElementById('target_audience');
    const audienceText = select.options[select.selectedIndex].text;
    return confirm(`Are you sure you want to broadcast this promotional email to:\n\n👉 ${audienceText}\n\nThis will send real emails to all matched members/subscribers.`);
}

function switchTab(tabId) {
    document.querySelectorAll('.tab-pane').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('.tab-btn').forEach(el => {
        el.classList.remove('border-red-500', 'text-white');
        el.classList.add('border-transparent', 'text-zinc-400');
    });

    const activePane = document.getElementById(`tab-${tabId}`);
    const activeBtn = document.getElementById(`tabBtn-${tabId}`);
    if (activePane) activePane.classList.remove('hidden');
    if (activeBtn) {
        activeBtn.classList.remove('border-transparent', 'text-zinc-400');
        activeBtn.classList.add('border-red-500', 'text-white');
    }
}

function previewHistoricalCampaign(subject, body) {
    document.getElementById('modalSubject').textContent = subject;
    document.getElementById('modalContent').innerHTML = body;
    document.getElementById('historyModal').classList.remove('hidden');
}

// Initial default load on page ready
window.addEventListener('DOMContentLoaded', () => {
    // Check if URL specifies a tab
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('tab') === 'subscribers') {
        switchTab('subscribers');
    } else {
        // Load default "We miss you" template for quick demonstration
        loadTemplate('absent_miss_you');
    }
});
</script>

<?php require_once __DIR__ . '/../components/footer.php'; ?>
