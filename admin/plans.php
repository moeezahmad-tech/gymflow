<?php
/**
 * GymFlow - Admin Membership Plans & Packages Manager
 * View, Create, and Edit Gym Membership Plans, Prices (PKR), Durations, and Features
 */
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

// Enforce Administrator & Staff RBAC
requireAdmin();

$activeAdminTab = 'plans';
$currentUser = getCurrentUser();
$pageTitle = "Membership Plans & Packages - " . APP_NAME;

$message = null;
$error = null;

// ------------------------------------------------------------
// Handle Form Submissions (Create, Edit, Delete Plan)
// ------------------------------------------------------------
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Security token mismatch. Please try again.";
    } else {
        $action = $_POST['form_action'] ?? '';

        // EDIT PLAN
        if ($action === 'edit_plan') {
            $planId       = (int)($_POST['plan_id'] ?? 0);
            $name         = trim($_POST['name'] ?? '');
            $price        = (float)($_POST['price'] ?? 0);
            $durationDays = (int)($_POST['duration_days'] ?? 30);
            $description  = trim($_POST['description'] ?? '');
            $isPopular    = !empty($_POST['is_popular']) ? 1 : 0;
            $status       = in_array($_POST['status'] ?? '', ['active', 'inactive']) ? $_POST['status'] : 'active';
            
            // Parse features from multiline textarea
            $featuresRaw = trim($_POST['features'] ?? '');
            $featureLines = array_filter(array_map('trim', explode("\n", $featuresRaw)));
            $featuresJson = json_encode(array_values($featureLines));

            if ($planId <= 0 || empty($name) || $price <= 0 || $durationDays <= 0) {
                $error = "Please provide a valid plan name, positive price in PKR, and valid duration in days.";
            } else {
                try {
                    $db = getDB();
                    $stmt = $db->prepare("
                        UPDATE membership_plans 
                        SET name = :name,
                            price = :price,
                            duration_days = :duration,
                            description = :desc,
                            features = :features,
                            is_popular = :popular,
                            status = :status
                        WHERE id = :id
                    ");
                    $stmt->execute([
                        ':name'     => $name,
                        ':price'    => $price,
                        ':duration' => $durationDays,
                        ':desc'     => $description,
                        ':features' => $featuresJson,
                        ':popular'  => $isPopular,
                        ':status'   => $status,
                        ':id'       => $planId
                    ]);

                    $message = "Membership plan '{$name}' successfully updated!";
                } catch (Exception $e) {
                    $error = "Error updating plan: " . $e->getMessage();
                }
            }
        }

        // CREATE NEW PLAN
        elseif ($action === 'create_plan') {
            $name         = trim($_POST['name'] ?? '');
            $slug         = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name)) . '-' . rand(100, 999);
            $price        = (float)($_POST['price'] ?? 0);
            $durationDays = (int)($_POST['duration_days'] ?? 30);
            $description  = trim($_POST['description'] ?? '');
            $isPopular    = !empty($_POST['is_popular']) ? 1 : 0;
            $status       = in_array($_POST['status'] ?? '', ['active', 'inactive']) ? $_POST['status'] : 'active';

            // Parse features
            $featuresRaw = trim($_POST['features'] ?? '');
            $featureLines = array_filter(array_map('trim', explode("\n", $featuresRaw)));
            $featuresJson = json_encode(array_values($featureLines));

            if (empty($name) || $price <= 0 || $durationDays <= 0) {
                $error = "Please provide a plan name, price, and valid duration in days.";
            } else {
                try {
                    $db = getDB();
                    $stmt = $db->prepare("
                        INSERT INTO membership_plans (name, slug, description, price, duration_days, features, is_popular, status)
                        VALUES (:name, :slug, :desc, :price, :duration, :features, :popular, :status)
                    ");
                    $stmt->execute([
                        ':name'     => $name,
                        ':slug'     => $slug,
                        ':desc'     => $description,
                        ':price'    => $price,
                        ':duration' => $durationDays,
                        ':features' => $featuresJson,
                        ':popular'  => $isPopular,
                        ':status'   => $status
                    ]);

                    $message = "New membership plan '{$name}' successfully created!";
                } catch (Exception $e) {
                    $error = "Error creating plan: " . $e->getMessage();
                }
            }
        }

        // DELETE / ARCHIVE PLAN
        elseif ($action === 'delete_plan') {
            $planId = (int)($_POST['plan_id'] ?? 0);
            if ($planId > 0) {
                try {
                    $db = getDB();
                    // Check if active members are on this plan
                    $stmtCheck = $db->prepare("SELECT COUNT(*) FROM memberships WHERE plan_id = :id AND status = 'active'");
                    $stmtCheck->execute([':id' => $planId]);
                    $activeMembersCount = (int)$stmtCheck->fetchColumn();

                    if ($activeMembersCount > 0) {
                        // Just set inactive instead of hard deleting
                        $stmtDeact = $db->prepare("UPDATE membership_plans SET status = 'inactive' WHERE id = :id");
                        $stmtDeact->execute([':id' => $planId]);
                        $message = "Plan has active subscribers, so it was marked as INACTIVE instead of deleting.";
                    } else {
                        $stmtDel = $db->prepare("DELETE FROM membership_plans WHERE id = :id");
                        $stmtDel->execute([':id' => $planId]);
                        $message = "Plan deleted successfully!";
                    }
                } catch (Exception $e) {
                    $error = "Error deleting plan: " . $e->getMessage();
                }
            }
        }
    }
}

// ------------------------------------------------------------
// Fetch All Plans with Member Statistics
// ------------------------------------------------------------
$plans = [];
try {
    $db = getDB();
    $stmtPlans = $db->query("
        SELECT mp.*, 
               COUNT(m.id) AS total_subscribers,
               SUM(CASE WHEN m.status = 'active' THEN 1 ELSE 0 END) AS active_subscribers
        FROM membership_plans mp
        LEFT JOIN memberships m ON mp.id = m.plan_id
        GROUP BY mp.id
        ORDER BY mp.duration_days ASC
    ");
    $plans = $stmtPlans->fetchAll();
} catch (Exception $e) {
    $error = "Database Error: " . $e->getMessage();
}

$csrfToken = getCSRFToken();
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    
    <!-- Favicon Suite -->
    <link rel="icon" type="image/svg+xml" href="<?= asset('favicon.svg') ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= asset('favicon.png') ?>">
    <link rel="shortcut icon" href="<?= asset('favicon.ico') ?>">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&family=Teko:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome Pro Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.php" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            red: '#ff2a2a',
                            'red-hover': '#e01f1f',
                            glow: 'rgba(255, 42, 42, 0.35)'
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
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body class="bg-[#050507] text-zinc-100 min-h-screen flex flex-col md:flex-row antialiased">

    <!-- Modular Admin Sidebar -->
    <?php require_once __DIR__ . '/../components/admin-sidebar.php'; ?>

    <!-- Main Workspace -->
    <div class="flex-1 flex flex-col min-w-0">
        
        <!-- Unified Header -->
        <?php 
        $adminHeaderTitle = "MEMBERSHIP PACKAGES & PRICING";
        $adminHeaderSubtitle = "Configure gym packages, rates (PKR), durations, and perk offerings";
        require_once __DIR__ . '/../components/admin-header.php'; 
        ?>

        <!-- Main Content Area -->
        <main class="p-4 sm:p-6 lg:p-8 pb-24 md:pb-8 space-y-6 sm:space-y-8 flex-grow min-w-0">
            
            <!-- Alert Messages -->
            <?php if ($message): ?>
                <div class="p-4 rounded-2xl bg-emerald-950/80 border border-emerald-800 text-emerald-300 text-xs sm:text-sm flex items-center justify-between shadow-xl">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-circle-check text-emerald-400 text-lg"></i>
                        <span><?= htmlspecialchars($message) ?></span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-white px-2 py-1"><i class="fa-solid fa-xmark"></i></button>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="p-4 rounded-2xl bg-red-950/80 border border-red-800 text-red-300 text-xs sm:text-sm flex items-center justify-between shadow-xl">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-triangle-exclamation text-red-400 text-lg"></i>
                        <span><?= htmlspecialchars($error) ?></span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-red-400 hover:text-white px-2 py-1"><i class="fa-solid fa-xmark"></i></button>
                </div>
            <?php endif; ?>

            <!-- Top Actions & Overview -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h2 class="font-heading text-3xl font-bold text-white uppercase tracking-wide">Active Gym Packages</h2>
                    <p class="text-xs text-zinc-400">Manage rates, benefits, and durations displayed across public pricing & admin onboarding</p>
                </div>
                <button type="button" onclick="openCreateModal()" class="btn-primary text-xs uppercase font-bold tracking-wider px-5 py-3 rounded-xl flex items-center gap-2 shadow-lg shadow-red-600/30">
                    <i class="fa-solid fa-plus"></i>
                    <span>+ Add New Package</span>
                </button>
            </div>

            <!-- Packages Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($plans as $p): 
                    $featuresArray = json_decode($p['features'] ?? '[]', true) ?: [];
                    $isPop = (bool)$p['is_popular'];
                ?>
                    <div class="glass-card rounded-3xl p-6 sm:p-7 border <?= $isPop ? 'border-red-600/60 shadow-2xl shadow-red-600/20 bg-gradient-to-b from-zinc-900/90 to-black' : 'border-zinc-800' ?> flex flex-col justify-between relative group hover:border-zinc-700 transition-all">
                        
                        <?php if ($isPop): ?>
                            <div class="absolute -top-3 left-6 bg-red-600 text-white text-[10px] font-extrabold uppercase tracking-widest px-3 py-0.5 rounded-full shadow-lg flex items-center gap-1">
                                <i class="fa-solid fa-fire text-[9px]"></i>
                                <span>Most Popular</span>
                            </div>
                        <?php endif; ?>

                        <div>
                            <!-- Header & Status -->
                            <div class="flex items-center justify-between mt-1">
                                <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-full <?= ($p['status'] ?? 'active') === 'active' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-zinc-800 text-zinc-500' ?>">
                                    <?= strtoupper($p['status'] ?? 'active') ?>
                                </span>
                                <span class="text-xs text-zinc-400 font-mono font-semibold">
                                    <i class="fa-regular fa-clock mr-1 text-red-500"></i><?= (int)$p['duration_days'] ?> Days
                                </span>
                            </div>

                            <!-- Title & Pricing -->
                            <h3 class="font-heading text-3xl font-bold text-white uppercase mt-3"><?= htmlspecialchars($p['name']) ?></h3>
                            <p class="text-xs text-zinc-400 mt-1 min-h-[36px]"><?= htmlspecialchars($p['description'] ?? 'Standard gym membership package') ?></p>

                            <div class="my-5 p-4 rounded-2xl bg-zinc-900/80 border border-zinc-800/80 flex items-baseline justify-between">
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-zinc-500 block">Package Rate</span>
                                    <span class="font-heading text-4xl font-extrabold text-white">PKR <?= number_format((float)$p['price']) ?></span>
                                </div>
                                <span class="text-xs text-emerald-400 font-semibold bg-emerald-500/10 px-2.5 py-1 rounded-lg border border-emerald-500/20">
                                    <?= (int)$p['active_subscribers'] ?> Active Members
                                </span>
                            </div>

                            <!-- Feature Bullets -->
                            <div class="space-y-2 mb-6">
                                <span class="text-[10px] uppercase tracking-wider font-bold text-zinc-500 block">Included Features:</span>
                                <ul class="space-y-2 text-xs text-zinc-300">
                                    <?php foreach (array_slice($featuresArray, 0, 5) as $f): ?>
                                        <li class="flex items-start gap-2">
                                            <i class="fa-solid fa-check text-red-500 text-xs mt-0.5 shrink-0"></i>
                                            <span class="leading-tight"><?= htmlspecialchars($f) ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                    <?php if (count($featuresArray) > 5): ?>
                                        <li class="text-[11px] text-zinc-500 italic pl-4">+ <?= count($featuresArray) - 5 ?> more perks</li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>

                        <!-- Card Action Buttons -->
                        <div class="pt-4 border-t border-zinc-800/80 flex items-center gap-3">
                            <button type="button" 
                                    onclick='openEditModal(<?= json_encode($p) ?>)'
                                    class="flex-1 btn-primary text-xs uppercase font-bold tracking-wider py-2.5 rounded-xl flex items-center justify-center gap-2">
                                <i class="fa-solid fa-pen-to-square"></i>
                                <span>Edit Plan</span>
                            </button>
                            <form method="POST" onsubmit="return confirm('Are you sure you want to remove or archive this plan?');" class="inline">
                                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                <input type="hidden" name="form_action" value="delete_plan">
                                <input type="hidden" name="plan_id" value="<?= (int)$p['id'] ?>">
                                <button type="submit" class="p-2.5 rounded-xl bg-zinc-900 border border-zinc-800 text-zinc-400 hover:text-red-400 hover:border-red-900 transition-colors" title="Delete or Archive Plan">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                </button>
                            </form>
                        </div>

                    </div>
                <?php endforeach; ?>
            </div>

        </main>
    </div>

    <!-- ============================================================
         EDIT PLAN MODAL
         ============================================================ -->
    <div id="editModal" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm hidden flex items-center justify-center p-4">
        <div class="bg-[#0b0b0f] border border-zinc-800 w-full max-w-xl rounded-3xl p-6 sm:p-8 shadow-2xl relative max-h-[90vh] overflow-y-auto">
            <button onclick="closeEditModal()" class="absolute top-6 right-6 text-zinc-400 hover:text-white text-lg">
                <i class="fa-solid fa-xmark"></i>
            </button>

            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-xl bg-red-600/10 text-red-500 flex items-center justify-center text-lg border border-red-500/20">
                    <i class="fa-solid fa-pen-to-square"></i>
                </div>
                <div>
                    <h3 class="font-heading text-2xl font-bold text-white uppercase">EDIT MEMBERSHIP PLAN</h3>
                    <p class="text-xs text-zinc-400">Update rates, validity period, and perks</p>
                </div>
            </div>

            <form method="POST" action="" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="form_action" value="edit_plan">
                <input type="hidden" name="plan_id" id="edit_plan_id" value="">

                <div>
                    <label class="block text-xs uppercase tracking-wider font-bold text-zinc-400 mb-1.5">Plan Name</label>
                    <input type="text" name="name" id="edit_name" required class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-red-500">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs uppercase tracking-wider font-bold text-zinc-400 mb-1.5">Price in PKR</label>
                        <input type="number" step="50" name="price" id="edit_price" required class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-red-500 font-mono">
                    </div>
                    <div>
                        <label class="block text-xs uppercase tracking-wider font-bold text-zinc-400 mb-1.5">Duration (in Days)</label>
                        <input type="number" name="duration_days" id="edit_duration_days" required class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-red-500 font-mono">
                    </div>
                </div>

                <div>
                    <label class="block text-xs uppercase tracking-wider font-bold text-zinc-400 mb-1.5">Description / Subtitle</label>
                    <input type="text" name="description" id="edit_description" class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-red-500">
                </div>

                <div>
                    <label class="block text-xs uppercase tracking-wider font-bold text-zinc-400 mb-1.5">Features & Perks (One perk per line)</label>
                    <textarea name="features" id="edit_features" rows="5" class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-4 py-2.5 text-xs text-zinc-200 focus:outline-none focus:border-red-500 font-mono leading-relaxed" placeholder="24/7 Gym Access&#10;Sauna Included&#10;Locker & Towel Service"></textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                    <div>
                        <label class="block text-xs uppercase tracking-wider font-bold text-zinc-400 mb-1.5">Status</label>
                        <select name="status" id="edit_status" class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-red-500">
                            <option value="active">Active (Available)</option>
                            <option value="inactive">Inactive (Hidden)</option>
                        </select>
                    </div>
                    <div class="flex items-center pt-6">
                        <label class="flex items-center gap-2.5 cursor-pointer text-xs font-bold text-zinc-300">
                            <input type="checkbox" name="is_popular" id="edit_is_popular" value="1" class="w-4 h-4 rounded border-zinc-700 bg-zinc-900 text-red-600 focus:ring-0">
                            <span>Badge as "Most Popular"</span>
                        </label>
                    </div>
                </div>

                <div class="pt-4 flex items-center justify-end gap-3 border-t border-zinc-800 mt-6">
                    <button type="button" onclick="closeEditModal()" class="px-5 py-2.5 rounded-xl bg-zinc-900 text-zinc-400 hover:text-white text-xs font-bold uppercase">Cancel</button>
                    <button type="submit" class="btn-primary px-6 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider shadow-lg shadow-red-600/30">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================
         CREATE NEW PLAN MODAL
         ============================================================ -->
    <div id="createModal" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm hidden flex items-center justify-center p-4">
        <div class="bg-[#0b0b0f] border border-zinc-800 w-full max-w-xl rounded-3xl p-6 sm:p-8 shadow-2xl relative max-h-[90vh] overflow-y-auto">
            <button onclick="closeCreateModal()" class="absolute top-6 right-6 text-zinc-400 hover:text-white text-lg">
                <i class="fa-solid fa-xmark"></i>
            </button>

            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-xl bg-red-600/10 text-red-500 flex items-center justify-center text-lg border border-red-500/20">
                    <i class="fa-solid fa-plus"></i>
                </div>
                <div>
                    <h3 class="font-heading text-2xl font-bold text-white uppercase">ADD NEW MEMBERSHIP PACKAGE</h3>
                    <p class="text-xs text-zinc-400">Create a new gym package tier and pricing</p>
                </div>
            </div>

            <form method="POST" action="" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="form_action" value="create_plan">

                <div>
                    <label class="block text-xs uppercase tracking-wider font-bold text-zinc-400 mb-1.5">Package Name</label>
                    <input type="text" name="name" required placeholder="e.g. 1 Year VIP Package" class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-red-500">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs uppercase tracking-wider font-bold text-zinc-400 mb-1.5">Price in PKR</label>
                        <input type="number" step="50" name="price" required placeholder="28000" class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-red-500 font-mono">
                    </div>
                    <div>
                        <label class="block text-xs uppercase tracking-wider font-bold text-zinc-400 mb-1.5">Duration (in Days)</label>
                        <input type="number" name="duration_days" value="365" required class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-red-500 font-mono">
                    </div>
                </div>

                <div>
                    <label class="block text-xs uppercase tracking-wider font-bold text-zinc-400 mb-1.5">Description / Tagline</label>
                    <input type="text" name="description" placeholder="Full year unlimited VIP access with maximum savings" class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-red-500">
                </div>

                <div>
                    <label class="block text-xs uppercase tracking-wider font-bold text-zinc-400 mb-1.5">Features & Perks (One perk per line)</label>
                    <textarea name="features" rows="5" class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-4 py-2.5 text-xs text-zinc-200 focus:outline-none focus:border-red-500 font-mono leading-relaxed" placeholder="Full 24/7 Gym Access&#10;All Fitness Classes Included&#10;Personal Trainer Consultations&#10;Finnish Sauna & Cold Plunge&#10;Free VIP Guest Passes"></textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                    <div>
                        <label class="block text-xs uppercase tracking-wider font-bold text-zinc-400 mb-1.5">Status</label>
                        <select name="status" class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-red-500">
                            <option value="active">Active (Available)</option>
                            <option value="inactive">Inactive (Hidden)</option>
                        </select>
                    </div>
                    <div class="flex items-center pt-6">
                        <label class="flex items-center gap-2.5 cursor-pointer text-xs font-bold text-zinc-300">
                            <input type="checkbox" name="is_popular" value="1" class="w-4 h-4 rounded border-zinc-700 bg-zinc-900 text-red-600 focus:ring-0">
                            <span>Badge as "Most Popular"</span>
                        </label>
                    </div>
                </div>

                <div class="pt-4 flex items-center justify-end gap-3 border-t border-zinc-800 mt-6">
                    <button type="button" onclick="closeCreateModal()" class="px-5 py-2.5 rounded-xl bg-zinc-900 text-zinc-400 hover:text-white text-xs font-bold uppercase">Cancel</button>
                    <button type="submit" class="btn-primary px-6 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider shadow-lg shadow-red-600/30">Create Package</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Interactivity Script -->
    <script>
        function openEditModal(plan) {
            document.getElementById('edit_plan_id').value = plan.id;
            document.getElementById('edit_name').value = plan.name || '';
            document.getElementById('edit_price').value = plan.price || '';
            document.getElementById('edit_duration_days').value = plan.duration_days || 30;
            document.getElementById('edit_description').value = plan.description || '';
            document.getElementById('edit_status').value = plan.status || 'active';
            document.getElementById('edit_is_popular').checked = (plan.is_popular == 1);

            let featuresList = [];
            try {
                featuresList = JSON.parse(plan.features) || [];
            } catch(e) {
                featuresList = [];
            }
            document.getElementById('edit_features').value = featuresList.join('\n');

            document.getElementById('editModal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }

        function openCreateModal() {
            document.getElementById('createModal').classList.remove('hidden');
        }

        function closeCreateModal() {
            document.getElementById('createModal').classList.add('hidden');
        }

        // Close on backdrop click
        window.onclick = function(e) {
            if (e.target === document.getElementById('editModal')) closeEditModal();
            if (e.target === document.getElementById('createModal')) closeCreateModal();
        }
    </script>
</body>
</html>
