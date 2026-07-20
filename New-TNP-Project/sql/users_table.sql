-- ============================================================
--  TPMS — sql/users_table.sql
--  Reference SQL for the authentication users table.
--  NOTE: This is auto-executed by includes/auth.php (seedDefaultUsers).
--        You do NOT need to run this manually — it is included here
--        for reference and manual inspection only.
-- ============================================================

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS `tnp_db`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `tnp_db`;

-- ── Users Table ───────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `tpms_users` (
    `id`         INT          UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`       VARCHAR(120) NOT NULL,
    `email`      VARCHAR(180) NOT NULL UNIQUE,
    `password`   VARCHAR(255) NOT NULL,    -- bcrypt hash (cost 12)
    `role`       ENUM('admin','student','company') NOT NULL,
    `status`     TINYINT(1)   NOT NULL DEFAULT 1, -- 1=active, 0=disabled
    `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Default Accounts (passwords hashed by PHP seedDefaultUsers()) ─
-- These are inserted automatically by login.php on first load.
-- Shown here for documentation purposes only.
--
-- Admin   → admin@tpms.com   / Admin@123
-- Student → student@tpms.com / Student@123
-- Company → company@tpms.com / Company@123
--
-- If you need to insert them manually (bcrypt hashes must be generated
-- via PHP — the values below are PLACEHOLDERS):
--
-- INSERT IGNORE INTO `tpms_users` (name, email, password, role) VALUES
-- ('TPO Administrator', 'admin@tpms.com',   '<bcrypt_hash>', 'admin'),
-- ('Demo Student',      'student@tpms.com', '<bcrypt_hash>', 'student'),
-- ('Demo Company',      'company@tpms.com', '<bcrypt_hash>', 'company');
--
-- Use seed_users.php to generate and insert real hashes automatically.
