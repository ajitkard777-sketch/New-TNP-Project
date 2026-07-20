-- ============================================================
--  Training & Placement Management System (TPMS)
--  File    : sql/schema.sql
--  Purpose : Complete MySQL schema for TPMS
--  Run via : setup.php (web) OR mysql -u root tnp_db < schema.sql
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';

-- ── Users (master auth table for all roles) ──────────────────
CREATE TABLE IF NOT EXISTS `users` (
    `id`            INT          AUTO_INCREMENT PRIMARY KEY,
    `uid`           VARCHAR(25)  NOT NULL UNIQUE COMMENT 'e.g. STU2026001, COMP001, ADMIN001',
    `name`          VARCHAR(120) NOT NULL,
    `email`         VARCHAR(160) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `role`          ENUM('student','company','admin') NOT NULL,
    `avatar`        VARCHAR(500) DEFAULT NULL,
    `created_at`    TIMESTAMP   DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP   DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_email` (`email`),
    INDEX `idx_role`  (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Students (extended profile) ──────────────────────────────
CREATE TABLE IF NOT EXISTS `students` (
    `id`                  INT           AUTO_INCREMENT PRIMARY KEY,
    `user_id`             INT           NOT NULL,
    `student_uid`         VARCHAR(25)   NOT NULL UNIQUE,
    `branch`              VARCHAR(120)  DEFAULT NULL,
    `cgpa`                DECIMAL(4,2)  DEFAULT 0.00,
    `backlogs`            INT           DEFAULT 0,
    `phone`               VARCHAR(20)   DEFAULT NULL,
    `placement_status`    VARCHAR(30)   DEFAULT 'In Progress',
    `profile_completion`  INT           DEFAULT 0,
    `resume_name`         VARCHAR(255)  DEFAULT NULL,
    `resume_path`         VARCHAR(500)  DEFAULT NULL,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_student_uid` (`student_uid`),
    INDEX `idx_branch`      (`branch`),
    INDEX `idx_placement`   (`placement_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Student Skills ────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `student_skills` (
    `id`         INT         AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT         NOT NULL,
    `skill`      VARCHAR(100) NOT NULL,
    FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
    INDEX `idx_student_skill` (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Companies ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `companies` (
    `id`              INT           AUTO_INCREMENT PRIMARY KEY,
    `comp_uid`        VARCHAR(25)   NOT NULL UNIQUE,
    `user_id`         INT           DEFAULT NULL,
    `name`            VARCHAR(160)  NOT NULL,
    `website`         VARCHAR(255)  DEFAULT NULL,
    `industry`        VARCHAR(120)  DEFAULT NULL,
    `contact`         VARCHAR(160)  DEFAULT NULL,
    `registered_date` DATE          DEFAULT NULL,
    `job_count`       INT           DEFAULT 0,
    `logo_url`        VARCHAR(500)  DEFAULT NULL,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_comp_uid`  (`comp_uid`),
    INDEX `idx_comp_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Jobs ─────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `jobs` (
    `id`           INT           AUTO_INCREMENT PRIMARY KEY,
    `job_uid`      VARCHAR(25)   NOT NULL UNIQUE,
    `company_id`   INT           NOT NULL,
    `title`        VARCHAR(200)  NOT NULL,
    `package`      VARCHAR(60)   DEFAULT NULL,
    `location`     VARCHAR(160)  DEFAULT NULL,
    `eligibility`  VARCHAR(500)  DEFAULT NULL,
    `deadline`     DATE          DEFAULT NULL,
    `status`       VARCHAR(20)   DEFAULT 'Active',
    `description`  TEXT          DEFAULT NULL,
    `company_logo` VARCHAR(500)  DEFAULT NULL,
    `created_at`   TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE,
    INDEX `idx_job_uid`    (`job_uid`),
    INDEX `idx_job_status` (`status`),
    INDEX `idx_company_id` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Job Skills ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `job_skills` (
    `id`     INT          AUTO_INCREMENT PRIMARY KEY,
    `job_id` INT          NOT NULL,
    `skill`  VARCHAR(100) NOT NULL,
    FOREIGN KEY (`job_id`) REFERENCES `jobs`(`id`) ON DELETE CASCADE,
    INDEX `idx_job_skill` (`job_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Applications ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `applications` (
    `id`           INT         AUTO_INCREMENT PRIMARY KEY,
    `app_uid`      VARCHAR(25) NOT NULL UNIQUE,
    `student_id`   INT         NOT NULL,
    `job_id`       INT         NOT NULL,
    `applied_date` DATE        DEFAULT NULL,
    `status`       VARCHAR(30) DEFAULT 'Applied',
    `created_at`   TIMESTAMP   DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`job_id`)     REFERENCES `jobs`(`id`)     ON DELETE CASCADE,
    UNIQUE KEY `unique_application` (`student_id`, `job_id`),
    INDEX `idx_app_uid`    (`app_uid`),
    INDEX `idx_app_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Application Timeline ──────────────────────────────────────
CREATE TABLE IF NOT EXISTS `app_timeline` (
    `id`             INT         AUTO_INCREMENT PRIMARY KEY,
    `application_id` INT         NOT NULL,
    `stage`          VARCHAR(50) NOT NULL,
    `stage_date`     VARCHAR(120) DEFAULT 'Pending',
    `done`           TINYINT(1)  DEFAULT 0,
    `sort_order`     INT         DEFAULT 0,
    FOREIGN KEY (`application_id`) REFERENCES `applications`(`id`) ON DELETE CASCADE,
    INDEX `idx_timeline_app` (`application_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Training Programs ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `training` (
    `id`          INT           AUTO_INCREMENT PRIMARY KEY,
    `trn_uid`     VARCHAR(25)   NOT NULL UNIQUE,
    `title`       VARCHAR(220)  NOT NULL,
    `trainer`     VARCHAR(200)  DEFAULT NULL,
    `trn_date`    VARCHAR(160)  DEFAULT NULL,
    `duration`    VARCHAR(60)   DEFAULT NULL,
    `status`      VARCHAR(20)   DEFAULT 'Upcoming',
    `description` TEXT          DEFAULT NULL,
    `created_at`  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_trn_uid`    (`trn_uid`),
    INDEX `idx_trn_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Student Training Enrollment ───────────────────────────────
CREATE TABLE IF NOT EXISTS `student_training` (
    `id`            INT       AUTO_INCREMENT PRIMARY KEY,
    `student_id`    INT       NOT NULL,
    `training_id`   INT       NOT NULL,
    `registered_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`student_id`)  REFERENCES `students`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`training_id`) REFERENCES `training`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_enrollment` (`student_id`, `training_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Universities ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `universities` (
    `id`          INT           AUTO_INCREMENT PRIMARY KEY,
    `uni_uid`     VARCHAR(25)   NOT NULL UNIQUE,
    `name`        VARCHAR(220)  NOT NULL,
    `country`     VARCHAR(100)  DEFAULT NULL,
    `courses`     TEXT          DEFAULT NULL,
    `deadline`    DATE          DEFAULT NULL,
    `scholarship` VARCHAR(220)  DEFAULT NULL,
    `fees`        VARCHAR(100)  DEFAULT NULL,
    `ranking`     VARCHAR(60)   DEFAULT NULL,
    `min_cgpa`    DECIMAL(4,2)  DEFAULT 0.00,
    `website`     VARCHAR(255)  DEFAULT NULL,
    `logo`        VARCHAR(500)  DEFAULT NULL,
    INDEX `idx_uni_uid` (`uni_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── University Applications ───────────────────────────────────
CREATE TABLE IF NOT EXISTS `university_apps` (
    `id`            INT       AUTO_INCREMENT PRIMARY KEY,
    `student_id`    INT       NOT NULL,
    `university_id` INT       NOT NULL,
    `applied_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`student_id`)    REFERENCES `students`(`id`)     ON DELETE CASCADE,
    FOREIGN KEY (`university_id`) REFERENCES `universities`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_uni_app` (`student_id`, `university_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Bookmarked Jobs ───────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `bookmarked_jobs` (
    `id`            INT       AUTO_INCREMENT PRIMARY KEY,
    `student_id`    INT       NOT NULL,
    `job_id`        INT       NOT NULL,
    `bookmarked_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`job_id`)     REFERENCES `jobs`(`id`)     ON DELETE CASCADE,
    UNIQUE KEY `unique_bookmark` (`student_id`, `job_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Activity Feed ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `activities` (
    `id`         INT           AUTO_INCREMENT PRIMARY KEY,
    `type`       VARCHAR(50)   DEFAULT NULL,
    `text`       VARCHAR(500)  NOT NULL,
    `icon`       VARCHAR(50)   DEFAULT 'bell',
    `created_at` TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_activity_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
