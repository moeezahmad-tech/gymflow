-- ============================================================
-- GymFlow - Comprehensive Database Schema & Seed Data
-- Generated: 2026-10-09 06:50:03
-- ============================================================

CREATE DATABASE IF NOT EXISTS `gymflow-35303339d352` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `gymflow-35303339d352`;

-- ------------------------------------------------------------
-- 1. Table: users
-- Roles: 'admin', 'staff', 'member'
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `member_code` VARCHAR(30) UNIQUE NOT NULL, -- e.g. 2026-1
    `full_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `phone` VARCHAR(30) NULL,
    `password` VARCHAR(255) NOT NULL, -- Bcrypt hashed
    `role` ENUM('admin', 'staff', 'member') NOT NULL DEFAULT 'member',
    `avatar` VARCHAR(255) NULL,
    `status` ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_email` (`email`),
    INDEX `idx_role` (`role`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 2. Table: membership_plans
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `membership_plans` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `description` TEXT NULL,
    `price` DECIMAL(10, 2) NOT NULL,
    `duration_days` INT NOT NULL DEFAULT 30,
    `features` TEXT NULL, -- JSON formatted feature bullets
    `is_popular` TINYINT(1) DEFAULT 0,
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 3. Table: memberships (Member Subscriptions)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `memberships` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `plan_id` INT NOT NULL,
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `status` ENUM('active', 'expired', 'pending', 'cancelled') NOT NULL DEFAULT 'active',
    `auto_renew` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`plan_id`) REFERENCES `membership_plans`(`id`) ON DELETE CASCADE,
    INDEX `idx_user_membership` (`user_id`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 4. Table: payments
-- Status: 'paid', 'pending', 'overdue', 'failed'
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `payments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `membership_id` INT NULL,
    `amount` DECIMAL(10, 2) NOT NULL,
    `payment_method` VARCHAR(50) NOT NULL DEFAULT 'Card',
    `transaction_id` VARCHAR(100) UNIQUE NULL,
    `status` ENUM('paid', 'pending', 'overdue', 'failed') NOT NULL DEFAULT 'paid',
    `due_date` DATE NULL,
    `paid_at` DATETIME NULL,
    `notes` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`membership_id`) REFERENCES `memberships`(`id`) ON DELETE SET NULL,
    INDEX `idx_payment_status` (`status`),
    INDEX `idx_due_date` (`due_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 5. Table: attendance (Turnstile & QR Access Logs)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `attendance` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `check_in_time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `check_out_time` TIMESTAMP NULL,
    `entry_type` ENUM('qr_scanner', 'rfid_keycard', 'manual') NOT NULL DEFAULT 'qr_scanner',
    `status` ENUM('granted', 'denied') NOT NULL DEFAULT 'granted',
    `notes` VARCHAR(255) NULL,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_user_attendance` (`user_id`, `check_in_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 6. Table: notifications_log (Automated SMS & WhatsApp Reminders)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `notifications_log` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `payment_id` INT NULL,
    `channel` ENUM('sms', 'whatsapp', 'email') NOT NULL DEFAULT 'sms',
    `recipient` VARCHAR(100) NOT NULL,
    `message` TEXT NOT NULL,
    `status` ENUM('sent', 'delivered', 'failed', 'queued') NOT NULL DEFAULT 'sent',
    `sent_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`payment_id`) REFERENCES `payments`(`id`) ON DELETE SET NULL,
    INDEX `idx_notif_sent` (`sent_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 7. Table: contact_messages (Contact Form Inquiries)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `contact_messages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `full_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL,
    `phone` VARCHAR(50) NULL,
    `program` VARCHAR(100) NULL,
    `message` TEXT NOT NULL,
    `status` ENUM('unread', 'read') NOT NULL DEFAULT 'unread',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `read_at` TIMESTAMP NULL DEFAULT NULL,
    INDEX `idx_msg_status` (`status`),
    INDEX `idx_msg_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 8. Table: newsletter_subscribers (Email Newsletter)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `newsletter_subscribers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `ip_address` VARCHAR(45) NULL,
    `status` ENUM('subscribed', 'unsubscribed') DEFAULT 'subscribed',
    `subscribed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_newsletter_email` (`email`),
    INDEX `idx_newsletter_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 9. Table: email_campaigns (Broadcasts & Promotional Campaigns)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `email_campaigns` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `subject` VARCHAR(255) NOT NULL,
    `category` VARCHAR(50) NOT NULL DEFAULT 'Promotion',
    `target_audience` VARCHAR(100) NOT NULL,
    `recipient_count` INT NOT NULL DEFAULT 0,
    `message` LONGTEXT NOT NULL,
    `status` ENUM('sent', 'draft', 'failed') NOT NULL DEFAULT 'sent',
    `sent_by` VARCHAR(100) NULL,
    `sent_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_camp_sent` (`sent_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Seed Data & Initial Records
-- Default Verified Credentials:
--   Admin:  admin@gymflow.com            / Admin123!
--   Staff:  staff@gymflow.com            / Staff123!
--   Member: member@gymflow.com           / Member123!
--   Member: moeezahmad.tech@gmail.com    / password123
--   Member: alex@gymflow.com             / password123
-- ============================================================

-- Table Data: membership_plans
INSERT INTO `membership_plans` (`id`, `name`, `slug`, `description`, `price`, `duration_days`, `features`, `is_popular`, `status`, `created_at`) VALUES
('1', '1 Month Package', '1-month', 'Essential gym floor & strength access for 1 full month', '3000.00', '30', '[\"Standard Gym Floor Access (5AM - 11PM)\", \"Locker room & shower facilities\", \"1 Free Trainer Fitness Assessment\", \"GymFlow Mobile App Workout Tracker\", \"No Long-Term Commitment\"]', '0', 'active', '2026-10-08 20:07:48'),
('2', '3 Month Package', '3-months', 'Our most popular quarterly package with classes & training perks', '8000.00', '90', '[\"Full 24/7 Gym Access for 90 Days\", \"All Fitness Classes Included (Boxing, HIIT, Yoga)\", \"Monthly 1-on-1 Personal Trainer Session\", \"Custom Nutrition & Meal Blueprint\", \"Finnish Sauna & Cold Plunge Access\", \"Save PKR 1,000 vs Monthly\"]', '1', 'active', '2026-10-08 20:07:48'),
('3', '6 Month Package', '6-months', 'VIP semi-annual transformation package with maximum savings', '15000.00', '180', '[\"Full 24/7 VIP All-Facility Access for 180 Days\", \"Weekly 1-on-1 Dedicated Coaching Sessions\", \"Reserved Private Locker & Towel Service\", \"Monthly InBody Body Composition Scans\", \"6 Free VIP Guest Passes Included\", \"Save PKR 3,000 with Half-Year Pass\"]', '0', 'active', '2026-10-08 20:07:48')
ON DUPLICATE KEY UPDATE `id`=VALUES(`id`);

-- Table Data: users
INSERT INTO `users` (`id`, `member_code`, `full_name`, `email`, `phone`, `password`, `role`, `avatar`, `status`, `created_at`, `updated_at`) VALUES
('1', '2026-1', 'Admin Director', 'admin@gymflow.com', '+923001234567', '$2y$10$aVHgwrRHaH.aoUl8dUwqhuUsc8h3BtgVXULnRd/ZZZ4Cc8aqvbwme', 'admin', NULL, 'active', '2026-10-08 20:07:48', '2026-10-09 11:49:29'),
('2', '2026-2', 'Coach Marcus Vance', 'staff@gymflow.com', '+923001234568', '$2y$10$rhRN8PY1NNAi687Jn2VNaesNut3AOWMQ8DkbVAJjdJ4wbp/1oC4hi', 'staff', NULL, 'active', '2026-10-08 20:07:48', '2026-10-09 11:49:29'),
('3', '2026-3', 'Alex Johnson', 'member@gymflow.com', '+923001234569', '$2y$10$xz9o8MSR1TLyRA4eGRNUPuM6btudgsVtJfmVCWNRj/BQgBheaKZ5e', 'member', NULL, 'active', '2026-10-08 20:07:48', '2026-10-09 11:49:29'),
('4', '2026-4', 'Moeez Ahmad', 'moeezahmad.tech@gmail.com', '+923266037125', '$2y$10$NN1YimVYCmT.kzublxhZLejA/L2GbkAvVrar0hK08vfCcEwnVqfTO', 'member', NULL, 'active', '2026-10-09 10:17:33', '2026-10-09 11:49:29'),
('5', '2026-5', 'Alex Johnson', 'alex@gymflow.com', '+923001234570', '$2y$10$VsAzBzYlEwsJ8VRRZnSnGeH5g7JaY4SagTZat2awG2V.G8UYEB.lq', 'member', NULL, 'active', '2026-10-09 10:20:38', '2026-10-09 11:49:29')
ON DUPLICATE KEY UPDATE `id`=VALUES(`id`);

-- Table Data: memberships
INSERT INTO `memberships` (`id`, `user_id`, `plan_id`, `start_date`, `end_date`, `status`, `auto_renew`, `created_at`, `updated_at`) VALUES
('1', '3', '2', '2026-10-08', '2027-01-07', 'active', '1', '2026-10-08 20:07:48', '2026-10-09 10:20:38'),
('2', '4', '1', '2026-10-09', '2027-01-07', 'active', '1', '2026-10-09 10:17:33', '2026-10-09 11:49:29'),
('3', '5', '2', '2026-10-09', '2027-01-07', 'active', '1', '2026-10-09 10:20:38', '2026-10-09 10:20:38')
ON DUPLICATE KEY UPDATE `id`=VALUES(`id`);

-- Table Data: payments
INSERT INTO `payments` (`id`, `user_id`, `membership_id`, `amount`, `payment_method`, `transaction_id`, `status`, `due_date`, `paid_at`, `notes`, `created_at`) VALUES
('1', '3', '1', '8000.00', 'Online Banking / JazzCash', 'TXN-98234-A101', 'paid', '2026-10-08', '2026-10-08 20:07:48', NULL, '2026-10-08 20:07:48'),
('2', '3', '1', '8000.00', 'Online Banking / JazzCash', 'TXN-98234-A102', 'pending', '2027-01-06', NULL, NULL, '2026-10-08 20:07:48'),
('3', '3', NULL, '8000.00', 'Cash / POS', 'TXN-FD-6AC7C27C59ADA', 'paid', '2026-10-08', '2026-10-08 21:19:08', '', '2026-10-08 21:19:08'),
('4', '3', NULL, '3000.00', 'Front Desk Renewal', 'TXN-TEST-6AC7C56A4C7A5', 'paid', '2026-10-08', '2026-10-08 21:31:38', 'Membership renewed for 30 days', '2026-10-08 21:31:38'),
('5', '3', NULL, '8000.00', 'Front Desk Renewal', 'TXN-RNW-6AC7C5EBA9BBF', 'paid', '2026-10-08', '2026-10-08 21:33:47', 'Membership renewed for 90 days', '2026-10-08 21:33:47'),
('6', '3', NULL, '8000.00', 'Front Desk Renewal', 'TXN-RNW-6AC7C5ED90CDF', 'paid', '2026-10-08', '2026-10-08 21:33:49', 'Membership renewed for 90 days', '2026-10-08 21:33:49'),
('7', '3', NULL, '8000.00', 'Front Desk Renewal', 'TXN-RNW-6AC7C5EF143FD', 'paid', '2026-10-08', '2026-10-08 21:33:51', 'Membership renewed for 90 days', '2026-10-08 21:33:51'),
('8', '3', NULL, '15000.00', 'Front Desk Renewal', 'TXN-RNW-6AC7C5F03CBE2', 'paid', '2026-10-08', '2026-10-08 21:33:52', 'Membership renewed for 180 days', '2026-10-08 21:33:52'),
('9', '3', NULL, '8000.00', 'Front Desk Renewal', 'TXN-RNW-6AC7C5F1344BD', 'paid', '2026-10-08', '2026-10-08 21:33:53', 'Membership renewed for 90 days', '2026-10-08 21:33:53'),
('10', '3', NULL, '8000.00', 'Front Desk Renewal', 'TXN-RNW-6AC7C5F316579', 'paid', '2026-10-08', '2026-10-08 21:33:55', 'Membership renewed for 90 days', '2026-10-08 21:33:55'),
('11', '3', NULL, '8000.00', 'Front Desk Renewal', 'TXN-RNW-6AC7C5F5EA7FD', 'paid', '2026-10-08', '2026-10-08 21:33:57', 'Membership renewed for 90 days', '2026-10-08 21:33:57'),
('12', '4', '2', '3000.00', 'Online Registration', 'TXN-6AC878ED5570C', 'paid', '2026-10-09', '2026-10-09 10:17:33', '1 Month Package Registration Payment', '2026-10-09 10:17:33')
ON DUPLICATE KEY UPDATE `id`=VALUES(`id`);

-- Table Data: attendance
INSERT INTO `attendance` (`id`, `user_id`, `check_in_time`, `check_out_time`, `entry_type`, `status`, `notes`) VALUES
('5', '4', '2026-10-09 10:33:12', NULL, 'qr_scanner', 'granted', NULL)
ON DUPLICATE KEY UPDATE `id`=VALUES(`id`);

-- Table Data: contact_messages
INSERT INTO `contact_messages` (`id`, `full_name`, `email`, `phone`, `program`, `message`, `status`, `created_at`, `read_at`) VALUES
('2', 'Newsletter Subscriber', 'testsubscriber@gymflow.com', '', 'Newsletter Subscription', 'Subscribed to Gym Flow Newsletter for weekly workout tips & VIP perks.', 'unread', '2026-10-09 11:12:30', NULL)
ON DUPLICATE KEY UPDATE `id`=VALUES(`id`);

-- Table Data: newsletter_subscribers
INSERT INTO `newsletter_subscribers` (`id`, `email`, `ip_address`, `status`, `subscribed_at`) VALUES
('1', 'testsubscriber@gymflow.com', '::1', 'subscribed', '2026-10-09 11:12:30')
ON DUPLICATE KEY UPDATE `id`=VALUES(`id`);

