-- =====================================================
-- TPMS - Saved Jobs Table Patch
-- =====================================================

USE `team1`;

-- Create saved_jobs table
CREATE TABLE IF NOT EXISTS `saved_jobs` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `student_id` INT NOT NULL,
    `job_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`job_id`) REFERENCES `jobs`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_saved_student_job` (`student_id`, `job_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Synchronize existing bookmarks to saved_jobs if any
INSERT IGNORE INTO `saved_jobs` (student_id, job_id, created_at)
SELECT student_id, job_id, created_at FROM `bookmarks`;
