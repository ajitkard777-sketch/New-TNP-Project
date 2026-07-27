-- =====================================================
-- TPMS - AI Job Recommendation Engine Schema Patch
-- =====================================================

USE `team1`;

-- 1. Add missing student profile columns if not present
SET @dbname = DATABASE();
SET @tablename = "students";
SET @columnname = "preferred_location";
SET @preparedStatement = (SELECT IF(
    (
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
        WHERE
            TABLE_SCHEMA = @dbname
            AND TABLE_NAME = @tablename
            AND COLUMN_NAME = @columnname
    ) > 0,
    "SELECT 1",
    "ALTER TABLE `students` ADD COLUMN `preferred_location` VARCHAR(255) NULL AFTER `city`;"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @columnname = "experience_years";
SET @preparedStatement = (SELECT IF(
    (
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
        WHERE
            TABLE_SCHEMA = @dbname
            AND TABLE_NAME = @tablename
            AND COLUMN_NAME = @columnname
    ) > 0,
    "SELECT 1",
    "ALTER TABLE `students` ADD COLUMN `experience_years` DECIMAL(3,1) DEFAULT 0.0 AFTER `active_backlogs`;"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- 2. Create job_recommendations table
CREATE TABLE IF NOT EXISTS `job_recommendations` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `student_id` INT NOT NULL,
    `job_id` INT NOT NULL,
    `recommendation_score` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    `matched_skills` TEXT NULL,
    `missing_skills` TEXT NULL,
    `recommendation_level` VARCHAR(50) NOT NULL,
    `reasons_json` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`job_id`) REFERENCES `jobs`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_student_job_rec` (`student_id`, `job_id`),
    INDEX `idx_score` (`recommendation_score`),
    INDEX `idx_student` (`student_id`),
    INDEX `idx_job` (`job_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
