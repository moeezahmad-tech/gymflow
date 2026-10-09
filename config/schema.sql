-- ============================================================
-- GymFlow - Database Schema & Security Architecture
-- ============================================================

CREATE DATABASE IF NOT EXISTS `gym_flow` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `gym_flow`;

-- ------------------------------------------------------------
-- 1. Table: users
-- Roles: 'admin', 'staff', 'member'
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `member_code` VARCHAR(30) UNIQUE NOT NULL, -- e.g. GF-98234
    `full_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `phone` VARCHAR(30) NULL,
    `password` VARCHAR(255) NOT NULL, -- Bcrypt / Argon2 hashed
    `role` ENUM('admin', 'staff', 'member') NOT NULL DEFAULT 'member',
    `avatar` VARCHAR(255) NULL,
    `status` ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_email` (`email`),
    INDEX `idx_role` (`role`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB;

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
) ENGINE=InnoDB;

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
) ENGINE=InnoDB;

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
) ENGINE=InnoDB;

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
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 6. Table: contact_messages (Contact Form Inquiries)
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
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 7. Initial Seed Data
-- ------------------------------------------------------------
INSERT INTO `membership_plans` (`id`, `name`, `slug`, `description`, `price`, `duration_days`, `features`, `is_popular`) VALUES
(1, '1 Month Package', '1-month', 'Essential gym floor & strength access for 1 full month', 3000.00, 30, '["Standard Gym Floor Access (5AM - 11PM)", "Locker room & shower facilities", "1 Free Trainer Fitness Assessment", "GymFlow Mobile App Workout Tracker", "No Long-Term Commitment"]', 0),
(2, '3 Month Package', '3-months', 'Our most popular quarterly package with classes & training perks', 8000.00, 90, '["Full 24/7 Gym Access for 90 Days", "All Fitness Classes Included (Boxing, HIIT, Yoga)", "Monthly 1-on-1 Personal Trainer Session", "Custom Nutrition & Meal Blueprint", "Finnish Sauna & Cold Plunge Access", "Save PKR 1,000 vs Monthly"]', 1),
(3, '6 Month Package', '6-months', 'VIP semi-annual transformation package with maximum savings', 15000.00, 180, '["Full 24/7 VIP All-Facility Access for 180 Days", "Weekly 1-on-1 Dedicated Coaching Sessions", "Reserved Private Locker & Towel Service", "Monthly InBody Body Composition Scans", "6 Free VIP Guest Passes Included", "Save PKR 3,000 with Half-Year Pass"]', 0)
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `slug`=VALUES(`slug`), `description`=VALUES(`description`), `price`=VALUES(`price`), `duration_days`=VALUES(`duration_days`), `features`=VALUES(`features`), `is_popular`=VALUES(`is_popular`);

INSERT INTO `users` (`id`, `member_code`, `full_name`, `email`, `phone`, `password`, `role`, `status`) VALUES
(1, '2026-1', 'Admin Director', 'admin@gymflow.com', '+923001234567', '$2y$10$Jb3iCLyqnO92c5n99P78c.T.xugYpXiii6JdSpA4BiiOX6vai/eze', 'admin', 'active'),
(2, '2026-2', 'Coach Marcus Vance', 'staff@gymflow.com', '+923001234568', '$2y$10$03UpLq5aXu9RUe2ZnLyENuWaDSDbIyqTIH3kZKoj8VbYwui4spzai', 'staff', 'active'),
(3, '2026-3', 'Alex Johnson', 'member@gymflow.com', '+923001234569', '$2y$10$5mOn88rpDUn9hgMcIx9lfumBNPrxp.KhKC0pE.d9pXV6NSiUl301q', 'member', 'active'),
(4, '2026-4', 'Alex Johnson', 'alex@gymflow.com', '+923001234570', '$2y$10$42w7eT7jXhL0zC1G1pYy1.FpQYV1h8gM14y52X8q9d9y3n6f4Bw92', 'member', 'active')
ON DUPLICATE KEY UPDATE `email`=VALUES(`email`), `member_code`=VALUES(`member_code`), `status`=VALUES(`status`);

INSERT INTO `memberships` (`id`, `user_id`, `plan_id`, `start_date`, `end_date`, `status`, `auto_renew`) VALUES
(1, 3, 2, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 90 DAY), 'active', 1)
ON DUPLICATE KEY UPDATE `status`=VALUES(`status`);

INSERT INTO `payments` (`id`, `user_id`, `membership_id`, `amount`, `payment_method`, `transaction_id`, `status`, `due_date`, `paid_at`) VALUES
(1, 3, 1, 8000.00, 'Online Banking / JazzCash', 'TXN-98234-A101', 'paid', CURDATE(), NOW()),
(2, 3, 1, 8000.00, 'Online Banking / JazzCash', 'TXN-98234-A102', 'pending', DATE_ADD(CURDATE(), INTERVAL 90 DAY), NULL)
ON DUPLICATE KEY UPDATE `status`=VALUES(`status`);
