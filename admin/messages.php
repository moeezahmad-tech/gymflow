<?php
/**
 * GymFlow - Admin Inquiries & Contact Messages Manager
 * View, Manage, and Process Contact Form Inquiries with Mark as Read functionality
 */
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

// Enforce Administrator & Staff RBAC
requireAdmin();

$activeAdminTab = 'messages';
$currentUser = getCurrentUser();
$pageTitle = "Client Inquiries & Messages - " . APP_NAME;
$adminHeaderTitle = "Inquiries & Messages";
$adminHeaderSubtitle = "Review customer contact form submissions and mark inquiries as resolved";

$message = null;
$error = null;
$db = Database::getConnection();

// ------------------------------------------------------------
// Handle Actions (Mark Read, Mark Unread, Delete, Mark All Read)
// ------------------------------------------------------------
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Security token mismatch. Please try again.";
    } else {
        $action = $_POST['form_action'] ?? '';

        // 1. MARK SINGLE AS READ
        if ($action === 'mark_read') {
            $msgId = (int)($_POST['message_id'] ?? 0);
            if ($msgId > 0) {
                try {
                    $stmt = $db->prepare("UPDATE contact_messages SET status = 'read', read_at = NOW() WHERE id = :id");
                    $stmt->execute([':id' => $msgId]);
                    $message = "Inquiry marked as Read.";
                } catch (Exception $e) {
                    $error = "Failed to update inquiry: " . $e->getMessage();
                }
            }
        }

        // 2. MARK SINGLE AS UNREAD
        elseif ($action === 'mark_unread') {
            $msgId = (int)($_POST['message_id'] ?? 0);
            if ($msgId > 0) {
                try {
                    $stmt = $db->prepare("UPDATE contact_messages SET status = 'unread', read_at = NULL WHERE id = :id");
                    $stmt->execute([':id' => $msgId]);
                    $message = "Inquiry marked as Unread.";
                } catch (Exception $e) {
                    $error = "Failed to update inquiry: " . $e->getMessage();
                }
            }
        }

        // 3. MARK ALL AS READ
        elseif ($action === 'mark_all_read') {
            try {
                $stmt = $db->prepare("UPDATE contact_messages SET status = 'read', read_at = NOW() WHERE status = 'unread'");
                $stmt->execute();
                $count = $stmt->rowCount();
                $message = "Successfully marked {$count} unread inquiry(ies) as Read.";
            } catch (Exception $e) {
                $error = "Failed to update inquiries: " . $e->getMessage();
            }
        }

        // 4. DELETE MESSAGE
        elseif ($action === 'delete_message') {
            $msgId = (int)($_POST['message_id'] ?? 0);
            if ($msgId > 0) {
                try {
                    $stmt = $db->prepare("DELETE FROM contact_messages WHERE id = :id");
                    $stmt->execute([':id' => $msgId]);
                    $message = "Inquiry deleted successfully.";
                } catch (Exception $e) {
                    $error = "Failed to delete inquiry: " . $e->getMessage();
                }
            }
        }
    }
}

// ------------------------------------------------------------
// Fetch Messages & Metrics
// ------------------------------------------------------------
$filter = $_GET['filter'] ?? 'all';
$search = trim($_GET['q'] ?? '');

$sql = "SELECT * FROM contact_messages WHERE 1=1";
$params = [];

if ($filter === 'unread') {
    $sql .= " AND status = 'unread'";
} elseif ($filter === 'read') {
    $sql .= " AND status = 'read'";
}

if (!empty($search)) {
    $sql .= " AND (full_name LIKE :q1 OR email LIKE :q2 OR phone LIKE :q3 OR message LIKE :q4 OR program LIKE :q5)";
    $qWildcard = "%{$search}%";
    $params[':q1'] = $qWildcard;
    $params[':q2'] = $qWildcard;
    $params[':q3'] = $qWildcard;
    $params[':q4'] = $qWildcard;
    $params[':q5'] = $qWildcard;
}

$sql .= " ORDER BY id DESC";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$inquiries = $stmt->fetchAll();

// Counts for Metrics Cards
$totalCount = (int)$db->query("SELECT COUNT(*) FROM contact_messages")->fetchColumn();
$unreadCount = (int)$db->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'unread'")->fetchColumn();
$readCount = (int)$db->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'read'")->fetchColumn();

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
                <div class="p-4 rounded-2xl bg-emerald-950/80 border border-emerald-800 text-emerald-300 text-sm flex items-center justify-between shadow-xl">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-circle-check text-emerald-400 text-xl flex-shrink-0"></i>
                        <span><?= htmlspecialchars($message) ?></span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-white px-2 py-1"><i class="fa-solid fa-xmark"></i></button>
                </div>
            <?php elseif ($error): ?>
                <div class="p-4 rounded-2xl bg-red-950/80 border border-red-800 text-red-300 text-sm flex items-center justify-between shadow-xl">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-triangle-exclamation text-red-400 text-xl flex-shrink-0"></i>
                        <span><?= htmlspecialchars($error) ?></span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-red-400 hover:text-white px-2 py-1"><i class="fa-solid fa-xmark"></i></button>
                </div>
            <?php endif; ?>

            <!-- ============================================================
                 1. METRIC COUNTERS
                 ============================================================ -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <!-- Total Inquiries -->
                <div class="glass-card rounded-3xl p-6 border border-zinc-800 shadow-xl relative overflow-hidden">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-zinc-400 block">Total Inquiries</span>
                            <span class="font-heading text-4xl font-bold text-white mt-1 block"><?= $totalCount ?></span>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-zinc-800 text-zinc-300 flex items-center justify-center text-xl">
                            <i class="fa-solid fa-inbox"></i>
                        </div>
                    </div>
                    <p class="text-[11px] text-zinc-500 mt-3">All public website contact submissions</p>
                </div>

                <!-- Unread Inquiries -->
                <div class="glass-card rounded-3xl p-6 border border-zinc-800 shadow-xl relative overflow-hidden bg-gradient-to-br from-zinc-950 via-red-950/20 to-zinc-950">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-red-400 block flex items-center gap-1.5">
                                <?php if ($unreadCount > 0): ?>
                                    <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                                <?php endif; ?>
                                Unread / Pending
                            </span>
                            <span class="font-heading text-4xl font-bold text-red-400 mt-1 block"><?= $unreadCount ?></span>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-red-600/10 text-red-500 border border-red-500/20 flex items-center justify-center text-xl">
                            <i class="fa-solid fa-envelope-open-text"></i>
                        </div>
                    </div>
                    <p class="text-[11px] text-zinc-400 mt-3"><?= $unreadCount > 0 ? 'Awaiting staff follow-up' : 'All inquiries resolved' ?></p>
                </div>

                <!-- Read Inquiries -->
                <div class="glass-card rounded-3xl p-6 border border-zinc-800 shadow-xl relative overflow-hidden">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-emerald-400 block">Handled & Resolved</span>
                            <span class="font-heading text-4xl font-bold text-emerald-400 mt-1 block"><?= $readCount ?></span>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex items-center justify-center text-xl">
                            <i class="fa-solid fa-check-double"></i>
                        </div>
                    </div>
                    <p class="text-[11px] text-zinc-500 mt-3">Marked as read and processed</p>
                </div>
            </div>

            <!-- ============================================================
                 2. SEARCH & FILTER TOOLBAR
                 ============================================================ -->
            <div class="glass-card rounded-3xl p-6 border border-zinc-800 shadow-2xl space-y-5">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    
                    <!-- Filter Tabs -->
                    <div class="flex flex-wrap items-center gap-2">
                        <a href="<?= url('admin/messages.php?filter=all' . ($search ? '&q=' . urlencode($search) : '')) ?>" class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition-all <?= $filter === 'all' ? 'bg-red-600 text-white shadow-lg shadow-red-600/30' : 'bg-zinc-900 text-zinc-400 hover:text-white border border-zinc-800' ?>">
                            All Messages (<?= $totalCount ?>)
                        </a>
                        <a href="<?= url('admin/messages.php?filter=unread' . ($search ? '&q=' . urlencode($search) : '')) ?>" class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition-all <?= $filter === 'unread' ? 'bg-red-600 text-white shadow-lg shadow-red-600/30' : 'bg-zinc-900 text-zinc-400 hover:text-white border border-zinc-800' ?>">
                            Unread (<?= $unreadCount ?>)
                        </a>
                        <a href="<?= url('admin/messages.php?filter=read' . ($search ? '&q=' . urlencode($search) : '')) ?>" class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition-all <?= $filter === 'read' ? 'bg-red-600 text-white shadow-lg shadow-red-600/30' : 'bg-zinc-900 text-zinc-400 hover:text-white border border-zinc-800' ?>">
                            Read (<?= $readCount ?>)
                        </a>
                    </div>

                    <!-- Search Form & Bulk Action -->
                    <div class="flex flex-wrap items-center gap-3">
                        <form method="GET" action="<?= url('admin/messages.php') ?>" class="flex items-center gap-2">
                            <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
                            <div class="relative">
                                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-xs text-zinc-500"></i>
                                <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Search inquiries..." class="bg-zinc-900 border border-zinc-800 rounded-xl pl-9 pr-4 py-2 text-xs text-white placeholder-zinc-500 focus:outline-none focus:border-red-500 w-48 sm:w-64">
                            </div>
                            <?php if (!empty($search)): ?>
                                <a href="<?= url('admin/messages.php?filter=' . urlencode($filter)) ?>" class="p-2 text-xs text-zinc-400 hover:text-white" title="Clear Search">
                                    <i class="fa-solid fa-xmark"></i>
                                </a>
                            <?php endif; ?>
                        </form>

                        <?php if ($unreadCount > 0): ?>
                            <form method="POST" onsubmit="return confirm('Are you sure you want to mark all unread inquiries as Read?');">
                                <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                                <input type="hidden" name="form_action" value="mark_all_read">
                                <button type="submit" class="btn-outline px-3.5 py-2 rounded-xl text-xs uppercase font-bold tracking-wider flex items-center gap-1.5 border-zinc-700 hover:border-emerald-500 text-emerald-400 hover:text-white">
                                    <i class="fa-solid fa-check-double text-xs"></i>
                                    <span>Mark All Read</span>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>

                </div>

                <!-- ============================================================
                     3. INQUIRIES LIST / TABLE
                     ============================================================ -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs sm:text-sm text-zinc-300">
                        <thead class="bg-zinc-900 text-zinc-400 uppercase font-heading text-xs tracking-wider border-b border-zinc-800">
                            <tr>
                                <th class="py-3.5 px-4">Status</th>
                                <th class="py-3.5 px-4">Sender & Contact</th>
                                <th class="py-3.5 px-4">Program Interest</th>
                                <th class="py-3.5 px-4">Message Snippet</th>
                                <th class="py-3.5 px-4">Date Received</th>
                                <th class="py-3.5 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-800/60 font-sans text-xs">
                            <?php if (!empty($inquiries)): ?>
                                <?php foreach ($inquiries as $inq): 
                                    $isUnread = ($inq['status'] === 'unread');
                                ?>
                                    <tr class="transition-colors <?= $isUnread ? 'bg-red-950/10 hover:bg-red-950/20 font-medium' : 'hover:bg-zinc-900/40 opacity-80' ?>">
                                        
                                        <!-- Status Badge -->
                                        <td class="py-4 px-4 whitespace-nowrap">
                                            <?php if ($isUnread): ?>
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-red-500/10 text-red-400 border border-red-500/20 text-[10px] font-bold uppercase">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></span>
                                                    Unread
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-zinc-800 text-zinc-400 border border-zinc-700 text-[10px] font-bold uppercase">
                                                    <i class="fa-solid fa-check text-[9px]"></i>
                                                    Read
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Sender Info -->
                                        <td class="py-4 px-4">
                                            <span class="font-bold text-white block <?= $isUnread ? 'text-sm font-heading tracking-wide' : '' ?>">
                                                <?= htmlspecialchars($inq['full_name']) ?>
                                            </span>
                                            <a href="mailto:<?= htmlspecialchars($inq['email']) ?>" class="text-zinc-400 hover:text-red-400 text-[11px] block transition-colors">
                                                <?= htmlspecialchars($inq['email']) ?>
                                            </a>
                                            <?php if (!empty($inq['phone'])): ?>
                                                <a href="tel:<?= htmlspecialchars($inq['phone']) ?>" class="text-zinc-500 hover:text-zinc-300 text-[11px] block font-mono">
                                                    <?= htmlspecialchars($inq['phone']) ?>
                                                </a>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Program -->
                                        <td class="py-4 px-4 whitespace-nowrap">
                                            <span class="px-2.5 py-1 rounded-lg bg-zinc-900 border border-zinc-800 text-zinc-300 text-[11px] font-semibold">
                                                <?= htmlspecialchars($inq['program'] ?? 'General Inquiry') ?>
                                            </span>
                                        </td>

                                        <!-- Message Preview -->
                                        <td class="py-4 px-4 max-w-xs">
                                            <p class="text-zinc-300 truncate cursor-pointer hover:text-white transition-colors" onclick="openMessageModal(<?= htmlspecialchars(json_encode($inq)) ?>)">
                                                <?= htmlspecialchars($inq['message']) ?>
                                            </p>
                                        </td>

                                        <!-- Date -->
                                        <td class="py-4 px-4 whitespace-nowrap font-mono text-[11px] text-zinc-400">
                                            <span><?= date('M d, Y', strtotime($inq['created_at'])) ?></span>
                                            <span class="block text-zinc-500 text-[10px]"><?= date('h:i A', strtotime($inq['created_at'])) ?></span>
                                        </td>

                                        <!-- Action Buttons -->
                                        <td class="py-4 px-4 text-right whitespace-nowrap">
                                            <div class="flex items-center justify-end gap-1.5">
                                                
                                                <!-- View Modal -->
                                                <button type="button" onclick="openMessageModal(<?= htmlspecialchars(json_encode($inq)) ?>)" class="p-2 rounded-lg bg-zinc-900 border border-zinc-800 hover:border-zinc-700 text-zinc-300 hover:text-white transition-colors" title="View Full Message">
                                                    <i class="fa-solid fa-eye text-xs"></i>
                                                </button>

                                                <!-- Toggle Read/Unread Form -->
                                                <form method="POST" class="inline">
                                                    <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                                                    <input type="hidden" name="message_id" value="<?= (int)$inq['id'] ?>">
                                                    <?php if ($isUnread): ?>
                                                        <input type="hidden" name="form_action" value="mark_read">
                                                        <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-emerald-950/40 border border-emerald-800/80 hover:bg-emerald-900/60 text-emerald-400 hover:text-emerald-200 text-[11px] font-bold transition-colors flex items-center gap-1" title="Mark as Read">
                                                            <i class="fa-solid fa-check text-xs"></i>
                                                            <span>Mark Read</span>
                                                        </button>
                                                    <?php else: ?>
                                                        <input type="hidden" name="form_action" value="mark_unread">
                                                        <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-zinc-900 border border-zinc-800 hover:border-zinc-700 text-zinc-400 hover:text-white text-[11px] font-bold transition-colors flex items-center gap-1" title="Mark as Unread">
                                                            <i class="fa-solid fa-envelope text-xs"></i>
                                                            <span>Unread</span>
                                                        </button>
                                                    <?php endif; ?>
                                                </form>

                                                <!-- Delete Form -->
                                                <form method="POST" class="inline" onsubmit="return confirm('Delete this inquiry permanently?');">
                                                    <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                                                    <input type="hidden" name="message_id" value="<?= (int)$inq['id'] ?>">
                                                    <input type="hidden" name="form_action" value="delete_message">
                                                    <button type="submit" class="p-2 rounded-lg bg-zinc-900 border border-zinc-800 hover:border-red-500 text-zinc-400 hover:text-red-400 transition-colors" title="Delete Inquiry">
                                                        <i class="fa-solid fa-trash text-xs"></i>
                                                    </button>
                                                </form>

                                            </div>
                                        </td>

                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-zinc-500">
                                        <div class="w-16 h-16 rounded-3xl bg-zinc-900 flex items-center justify-center text-2xl mx-auto mb-3 text-zinc-600">
                                            <i class="fa-solid fa-envelope-open"></i>
                                        </div>
                                        <p class="text-sm font-bold text-zinc-400 uppercase font-heading tracking-wider">No Inquiries Found</p>
                                        <p class="text-xs text-zinc-600 mt-1"><?= !empty($search) ? 'No inquiries matching "' . htmlspecialchars($search) . '"' : 'When users submit the website contact form, their messages will appear here.' ?></p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>

        </main>
    </div>
</div>

<!-- ============================================================
     VIEW MESSAGE MODAL
     ============================================================ -->
<div id="viewMsgModal" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-[#0b0b0f] border border-zinc-800 w-full max-w-lg rounded-3xl p-6 sm:p-8 shadow-2xl relative space-y-6">
        <button onclick="closeMessageModal()" class="absolute top-6 right-6 text-zinc-400 hover:text-white text-lg">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <div class="flex items-center gap-3 pb-4 border-b border-zinc-800">
            <div class="w-10 h-10 rounded-xl bg-red-600/10 text-red-500 flex items-center justify-center text-lg border border-red-500/20">
                <i class="fa-solid fa-envelope-open-text"></i>
            </div>
            <div>
                <h3 class="font-heading text-2xl font-bold text-white uppercase" id="modalSenderName">Inquiry Details</h3>
                <p class="text-xs text-zinc-400" id="modalDate">Received timestamp</p>
            </div>
        </div>

        <div class="space-y-3.5 text-xs bg-zinc-900/60 p-5 rounded-2xl border border-zinc-800">
            <div class="flex justify-between py-1 border-b border-zinc-800/80">
                <span class="text-zinc-400 font-bold uppercase tracking-wider text-[10px]">Email Address:</span>
                <a id="modalEmail" href="#" class="text-red-400 hover:underline font-medium"></a>
            </div>
            <div class="flex justify-between py-1 border-b border-zinc-800/80">
                <span class="text-zinc-400 font-bold uppercase tracking-wider text-[10px]">Phone Number:</span>
                <span id="modalPhone" class="text-white font-mono"></span>
            </div>
            <div class="flex justify-between py-1 border-b border-zinc-800/80">
                <span class="text-zinc-400 font-bold uppercase tracking-wider text-[10px]">Interested Program:</span>
                <span id="modalProgram" class="text-emerald-400 font-semibold"></span>
            </div>
            <div class="pt-2">
                <span class="text-zinc-400 font-bold uppercase tracking-wider text-[10px] block mb-2">Message Content:</span>
                <div id="modalMessageContent" class="text-zinc-200 text-xs leading-relaxed bg-black/50 p-4 rounded-xl border border-zinc-800 whitespace-pre-wrap font-sans"></div>
            </div>
        </div>

        <div class="flex items-center justify-between gap-3 pt-2">
            <a id="modalReplyBtn" href="#" class="btn-primary text-xs uppercase font-bold tracking-wider px-5 py-2.5 rounded-xl shadow-lg shadow-red-600/30 flex items-center gap-2">
                <i class="fa-solid fa-reply text-xs"></i>
                <span>Reply via Email</span>
            </a>

            <div class="flex items-center gap-2">
                <form id="modalMarkReadForm" method="POST" class="inline">
                    <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                    <input type="hidden" name="message_id" id="modalMsgId" value="">
                    <input type="hidden" name="form_action" value="mark_read">
                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-emerald-950/60 border border-emerald-800 text-emerald-400 hover:text-white text-xs font-bold uppercase flex items-center gap-1.5">
                        <i class="fa-solid fa-check"></i>
                        <span>Mark Read</span>
                    </button>
                </form>
                <button onclick="closeMessageModal()" class="px-4 py-2.5 rounded-xl bg-zinc-900 text-zinc-400 hover:text-white text-xs font-bold uppercase">Close</button>
            </div>
        </div>

    </div>
</div>

<script>
function openMessageModal(inq) {
    document.getElementById('modalSenderName').textContent = inq.full_name;
    document.getElementById('modalDate').textContent = 'Received: ' + inq.created_at;
    
    const emailElem = document.getElementById('modalEmail');
    emailElem.textContent = inq.email;
    emailElem.href = 'mailto:' + inq.email;

    document.getElementById('modalPhone').textContent = inq.phone || 'Not provided';
    document.getElementById('modalProgram').textContent = inq.program || 'General Inquiry';
    document.getElementById('modalMessageContent').textContent = inq.message;
    
    document.getElementById('modalReplyBtn').href = 'mailto:' + inq.email + '?subject=' + encodeURIComponent('Response to your inquiry at GymFlow') + '&body=' + encodeURIComponent('Hi ' + inq.full_name + ',\n\nThank you for reaching out to GymFlow.\n\n');
    
    document.getElementById('modalMsgId').value = inq.id;
    
    // Hide mark read button if already read
    const markReadForm = document.getElementById('modalMarkReadForm');
    if (inq.status === 'read') {
        markReadForm.classList.add('hidden');
    } else {
        markReadForm.classList.remove('hidden');
    }

    document.getElementById('viewMsgModal').classList.remove('hidden');
}

function closeMessageModal() {
    document.getElementById('viewMsgModal').classList.add('hidden');
}
</script>

<?php
require_once __DIR__ . '/../components/footer.php';
?>
