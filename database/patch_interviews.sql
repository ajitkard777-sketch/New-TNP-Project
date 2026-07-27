-- =====================================================
-- TPMS - Company Interview Scheduling & Feedback Schema Patch
-- =====================================================

USE `team1`;

-- 1. Create interviews table
CREATE TABLE IF NOT EXISTS `interviews` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `company_id` INT NOT NULL,
    `job_id` INT NOT NULL,
    `student_id` INT NOT NULL,
    `interview_title` VARCHAR(255) NOT NULL,
    `interview_type` ENUM('online', 'offline', 'hybrid') DEFAULT 'online',
    `interview_round` ENUM('aptitude', 'technical', 'hr', 'managerial', 'final') DEFAULT 'technical',
    `interview_date` DATE NOT NULL,
    `start_time` TIME NOT NULL,
    `end_time` TIME NOT NULL,
    `timezone` VARCHAR(50) DEFAULT 'IST (UTC+05:30)',
    `venue` TEXT NULL,
    `meeting_link` VARCHAR(500) NULL,
    `interviewer_name` VARCHAR(150) NULL,
    `interviewer_email` VARCHAR(150) NULL,
    `interviewer_phone` VARCHAR(50) NULL,
    `instructions` TEXT NULL,
    `required_documents` TEXT NULL,
    `dress_code` VARCHAR(100) NULL,
    `status` ENUM('scheduled', 'upcoming', 'completed', 'cancelled', 'rescheduled', 'selected', 'rejected', 'on_hold') DEFAULT 'scheduled',
    `cancellation_reason` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`job_id`) REFERENCES `jobs`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
    INDEX `idx_company_interview` (`company_id`),
    INDEX `idx_student_interview` (`student_id`),
    INDEX `idx_interview_date` (`interview_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Create interview_feedback table
CREATE TABLE IF NOT EXISTS `interview_feedback` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `interview_id` INT NOT NULL,
    `student_id` INT NOT NULL,
    `technical_rating` INT DEFAULT 5,
    `communication_rating` INT DEFAULT 5,
    `problem_solving_rating` INT DEFAULT 5,
    `overall_rating` INT DEFAULT 5,
    `comments` TEXT NULL,
    `result` ENUM('selected', 'rejected', 'next_round') DEFAULT 'next_round',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`interview_id`) REFERENCES `interviews`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_interview_feedback` (`interview_id`, `student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
