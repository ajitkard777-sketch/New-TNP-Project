-- =====================================================
-- TPMS - AI Admin Assistant & Shortlist Schema Patch
-- =====================================================

USE `team1`;

-- 1. Create admin_chat_history table
CREATE TABLE IF NOT EXISTS `admin_chat_history` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `message` TEXT NOT NULL,
    `response` TEXT NOT NULL,
    `metadata_json` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_admin_user` (`user_id`),
    INDEX `idx_admin_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Create student_shortlists table
CREATE TABLE IF NOT EXISTS `student_shortlists` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `job_id` INT NOT NULL,
    `student_id` INT NOT NULL,
    `eligibility_score` DECIMAL(5,2) DEFAULT 0.00,
    `status` ENUM('shortlisted', 'invited', 'rejected') DEFAULT 'shortlisted',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`job_id`) REFERENCES `jobs`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_job_student_shortlist` (`job_id`, `student_id`),
    INDEX `idx_job_shortlist` (`job_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
