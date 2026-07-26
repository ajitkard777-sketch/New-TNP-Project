-- =====================================================
-- TPMS Database Patch - Fix Schema Mismatches
-- Run this against the live `team1` database
-- =====================================================

SET FOREIGN_KEY_CHECKS = 0;
USE `team1`;

-- =====================================================
-- 1. CREATE password_resets TABLE
-- Referenced by: User.php (createPasswordReset, verifyPasswordReset, usePasswordReset)
-- =====================================================
CREATE TABLE IF NOT EXISTS `password_resets` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `token` VARCHAR(255) NOT NULL,
    `used` TINYINT(1) NOT NULL DEFAULT 0,
    `expires_at` DATETIME NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_token` (`token`),
    INDEX `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 2. CREATE courses TABLE
-- Referenced by: StudentController.php (higherStudies - subquery + JOIN)
-- =====================================================
CREATE TABLE IF NOT EXISTS `courses` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `university_id` INT NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `duration` VARCHAR(100) NULL,
    `degree_type` VARCHAR(100) NULL,
    `description` TEXT NULL,
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`university_id`) REFERENCES `universities`(`id`) ON DELETE CASCADE,
    INDEX `idx_university` (`university_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 3. RENAME users.otp_expiry → otp_expires_at
-- Referenced by: User.php (setOTP, verifyOTP - 3 references)
-- =====================================================
ALTER TABLE `users` CHANGE COLUMN `otp_expiry` `otp_expires_at` DATETIME NULL;

-- =====================================================
-- 4. ADD missing columns to documents
-- Referenced by: StudentController.php (uploadDocument)
-- =====================================================
ALTER TABLE `documents` ADD COLUMN `mime_type` VARCHAR(100) NULL AFTER `file_size`;
ALTER TABLE `documents` ADD COLUMN `description` TEXT NULL AFTER `mime_type`;

-- =====================================================
-- 5. ADD resume_snapshot to applications
-- Referenced by: StudentController.php (applyJob)
-- =====================================================
ALTER TABLE `applications` ADD COLUMN `resume_snapshot` VARCHAR(255) NULL AFTER `cover_letter`;

-- =====================================================
-- 6. ADD github_url to student_projects
-- Referenced by: Student.php (addProject)
-- =====================================================
ALTER TABLE `student_projects` ADD COLUMN `github_url` VARCHAR(500) NULL AFTER `project_url`;

-- =====================================================
-- 7. ADD expiry_date to student_certifications
-- Referenced by: Student.php (addCertification)
-- =====================================================
ALTER TABLE `student_certifications` ADD COLUMN `expiry_date` DATE NULL AFTER `issue_date`;

-- =====================================================
-- 8. ADD course_id, exam_score, notes to higher_study_applications
-- Referenced by: StudentController.php (higherStudies, registerHigherStudy)
-- =====================================================
ALTER TABLE `higher_study_applications` ADD COLUMN `course_id` INT NULL AFTER `university_id`;
ALTER TABLE `higher_study_applications` ADD COLUMN `exam_score` VARCHAR(50) NULL AFTER `course_name`;
ALTER TABLE `higher_study_applications` ADD COLUMN `notes` TEXT NULL AFTER `status`;
ALTER TABLE `higher_study_applications` ADD CONSTRAINT `fk_hsa_course` FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE SET NULL;

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- PATCH COMPLETE
-- Fixes: 2 missing tables, 1 column rename, 8 missing columns
-- =====================================================
