<?php
/**
 * Migration 001 — Create all core tables
 *
 * Uses CREATE TABLE IF NOT EXISTS so it is completely idempotent.
 * All tables referenced by the application are created here.
 * Foreign-key checks are disabled for the duration of this migration.
 */
return function (Database $db): void {

    $db->query("SET FOREIGN_KEY_CHECKS = 0");

    // ── users ──────────────────────────────────────────────────────────────
    $db->query("
        CREATE TABLE IF NOT EXISTS `users` (
            `id`                       INT          PRIMARY KEY AUTO_INCREMENT,
            `email`                    VARCHAR(255) NOT NULL UNIQUE,
            `password`                 VARCHAR(255) NOT NULL,
            `role`                     ENUM('admin','student','company') NOT NULL DEFAULT 'student',
            `status`                   ENUM('active','inactive','pending','banned','blocked') NOT NULL DEFAULT 'active',
            `email_verified`           TINYINT(1)   NOT NULL DEFAULT 0,
            `email_verification_token` VARCHAR(255) NULL,
            `password_reset_token`     VARCHAR(255) NULL,
            `password_reset_expiry`    DATETIME     NULL,
            `otp`                      VARCHAR(10)  NULL,
            `otp_expires_at`           DATETIME     NULL,
            `remember_token`           VARCHAR(255) NULL,
            `theme_preference`         VARCHAR(50)  NOT NULL DEFAULT 'light',
            `last_login`               DATETIME     NULL,
            `login_attempts`           INT          NOT NULL DEFAULT 0,
            `locked_until`             DATETIME     NULL,
            `created_at`               TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
            `updated_at`               TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_email`  (`email`),
            INDEX `idx_role`   (`role`),
            INDEX `idx_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // ── students ───────────────────────────────────────────────────────────
    $db->query("
        CREATE TABLE IF NOT EXISTS `students` (
            `id`                  INT           PRIMARY KEY AUTO_INCREMENT,
            `user_id`             INT           NOT NULL,
            `first_name`          VARCHAR(100)  NOT NULL,
            `last_name`           VARCHAR(100)  NOT NULL,
            `phone`               VARCHAR(15)   NULL,
            `dob`                 DATE          NULL,
            `gender`              ENUM('male','female','other') NULL,
            `address`             TEXT          NULL,
            `city`                VARCHAR(100)  NULL,
            `state`               VARCHAR(100)  NULL,
            `pincode`             VARCHAR(10)   NULL,
            `profile_photo`       VARCHAR(255)  NULL,
            `enrollment_no`       VARCHAR(50)   NULL,
            `branch`              VARCHAR(100)  NULL,
            `degree`              VARCHAR(50)   DEFAULT 'B.Tech',
            `admission_year`      INT           NULL,
            `passing_year`        INT           NULL,
            `cgpa`                DECIMAL(4,2)  NULL,
            `tenth_percentage`    DECIMAL(5,2)  NULL,
            `twelfth_percentage`  DECIMAL(5,2)  NULL,
            `diploma_percentage`  DECIMAL(5,2)  NULL,
            `backlogs`            INT           DEFAULT 0,
            `active_backlogs`     INT           DEFAULT 0,
            `skills`              TEXT          NULL,
            `bio`                 TEXT          NULL,
            `resume_path`         VARCHAR(255)  NULL,
            `resume_original_name`VARCHAR(255)  NULL,
            `linkedin`            VARCHAR(255)  NULL,
            `github`              VARCHAR(255)  NULL,
            `portfolio`           VARCHAR(255)  NULL,
            `is_placed`           TINYINT(1)    DEFAULT 0,
            `placed_company`      VARCHAR(255)  NULL,
            `placed_package`      DECIMAL(10,2) NULL,
            `placed_date`         DATE          NULL,
            `profile_completion`  INT           DEFAULT 0,
            `created_at`          TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
            `updated_at`          TIMESTAMP     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
            INDEX `idx_branch`       (`branch`),
            INDEX `idx_placed`       (`is_placed`),
            INDEX `idx_cgpa`         (`cgpa`),
            INDEX `idx_passing_year` (`passing_year`),
            INDEX `idx_enrollment`   (`enrollment_no`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // ── companies ──────────────────────────────────────────────────────────
    $db->query("
        CREATE TABLE IF NOT EXISTS `companies` (
            `id`               INT           PRIMARY KEY AUTO_INCREMENT,
            `user_id`          INT           NOT NULL,
            `company_name`     VARCHAR(255)  NOT NULL,
            `industry`         VARCHAR(100)  NULL,
            `company_type`     ENUM('product','service','startup','mnc','government','other') NULL,
            `employee_count`   VARCHAR(50)   NULL,
            `established_year` INT           NULL,
            `website`          VARCHAR(255)  NULL,
            `logo`             VARCHAR(255)  NULL,
            `description`      TEXT          NULL,
            `address`          TEXT          NULL,
            `city`             VARCHAR(100)  NULL,
            `state`            VARCHAR(100)  NULL,
            `country`          VARCHAR(100)  DEFAULT 'India',
            `contact_person`   VARCHAR(150)  NULL,
            `contact_email`    VARCHAR(255)  NULL,
            `contact_phone`    VARCHAR(15)   NULL,
            `is_approved`      TINYINT(1)    DEFAULT 0,
            `registered_count` INT           DEFAULT 0,
            `created_at`       TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
            `updated_at`       TIMESTAMP     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
            INDEX `idx_approved` (`is_approved`),
            INDEX `idx_industry` (`industry`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // ── jobs ───────────────────────────────────────────────────────────────
    $db->query("
        CREATE TABLE IF NOT EXISTS `jobs` (
            `id`                   INT           PRIMARY KEY AUTO_INCREMENT,
            `company_id`           INT           NOT NULL,
            `title`                VARCHAR(255)  NOT NULL,
            `description`          TEXT          NULL,
            `job_type`             ENUM('full-time','internship','part-time','contract') DEFAULT 'full-time',
            `work_mode`            ENUM('onsite','remote','hybrid') DEFAULT 'onsite',
            `location`             VARCHAR(150)  NULL,
            `salary_min`           DECIMAL(10,2) NULL DEFAULT 0,
            `salary_max`           DECIMAL(10,2) NULL DEFAULT 0,
            `openings`             INT           DEFAULT 1,
            `skills_required`      TEXT          NULL,
            `experience_required`  VARCHAR(100)  NULL,
            `eligibility_cgpa`     DECIMAL(4,2)  DEFAULT 0,
            `eligibility_branches` TEXT          NULL,
            `eligibility_backlogs` INT           DEFAULT 0,
            `application_deadline` DATE          NULL,
            `status`               ENUM('pending','active','closed','expired') DEFAULT 'pending',
            `created_at`           TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
            `updated_at`           TIMESTAMP     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE,
            INDEX `idx_status`   (`status`),
            INDEX `idx_type`     (`job_type`),
            INDEX `idx_deadline` (`application_deadline`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // ── applications ───────────────────────────────────────────────────────
    $db->query("
        CREATE TABLE IF NOT EXISTS `applications` (
            `id`              INT  PRIMARY KEY AUTO_INCREMENT,
            `student_id`      INT  NOT NULL,
            `job_id`          INT  NOT NULL,
            `status`          ENUM('applied','shortlisted','interview','selected','rejected','withdrawn') DEFAULT 'applied',
            `cover_letter`    TEXT NULL,
            `resume_snapshot` VARCHAR(255) NULL,
            `applied_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`job_id`)     REFERENCES `jobs`(`id`)     ON DELETE CASCADE,
            UNIQUE KEY `unique_application` (`student_id`, `job_id`),
            INDEX `idx_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // ── interviews ─────────────────────────────────────────────────────────
    $db->query("
        CREATE TABLE IF NOT EXISTS `interviews` (
            `id`             INT          PRIMARY KEY AUTO_INCREMENT,
            `student_id`     INT          NOT NULL,
            `company_id`     INT          NOT NULL,
            `job_id`         INT          NOT NULL,
            `round`          VARCHAR(100) DEFAULT 'Round 1',
            `interview_date` DATE         NOT NULL,
            `interview_time` TIME         NOT NULL,
            `mode`           ENUM('online','offline') DEFAULT 'offline',
            `venue`          VARCHAR(255) NULL,
            `meeting_link`   VARCHAR(500) NULL,
            `instructions`   TEXT         NULL,
            `status`         ENUM('scheduled','completed','cancelled','rescheduled') DEFAULT 'scheduled',
            `result`         ENUM('pending','passed','failed') DEFAULT 'pending',
            `feedback`       TEXT         NULL,
            `created_at`     TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
            `updated_at`     TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`job_id`)     REFERENCES `jobs`(`id`)     ON DELETE CASCADE,
            INDEX `idx_date`   (`interview_date`),
            INDEX `idx_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // ── placements ─────────────────────────────────────────────────────────
    $db->query("
        CREATE TABLE IF NOT EXISTS `placements` (
            `id`             INT           PRIMARY KEY AUTO_INCREMENT,
            `student_id`     INT           NOT NULL,
            `company_id`     INT           NULL,
            `job_id`         INT           NULL,
            `package`        DECIMAL(10,2) NULL,
            `placement_date` DATE          NULL,
            `offer_letter`   VARCHAR(255)  NULL,
            `status`         ENUM('confirmed','pending','revoked') DEFAULT 'confirmed',
            `remarks`        TEXT          NULL,
            `created_at`     TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
            `updated_at`     TIMESTAMP     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`student_id`) REFERENCES `students`(`id`)  ON DELETE CASCADE,
            FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE SET NULL,
            FOREIGN KEY (`job_id`)     REFERENCES `jobs`(`id`)      ON DELETE SET NULL,
            INDEX `idx_date` (`placement_date`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // ── trainings ──────────────────────────────────────────────────────────
    $db->query("
        CREATE TABLE IF NOT EXISTS `trainings` (
            `id`                INT          PRIMARY KEY AUTO_INCREMENT,
            `title`             VARCHAR(255) NOT NULL,
            `description`       TEXT         NULL,
            `training_type`     ENUM('technical','soft-skills','aptitude','workshop','seminar') DEFAULT 'technical',
            `mode`              ENUM('online','offline','hybrid') DEFAULT 'offline',
            `venue`             VARCHAR(255) NULL,
            `trainer_name`      VARCHAR(150) NULL,
            `start_date`        DATE         NOT NULL,
            `end_date`          DATE         NOT NULL,
            `start_time`        TIME         NULL,
            `end_time`          TIME         NULL,
            `capacity`          INT          DEFAULT 50,
            `registered_count`  INT          DEFAULT 0,
            `faculty_id`        INT          NULL,
            `status`            ENUM('upcoming','ongoing','completed','cancelled') DEFAULT 'upcoming',
            `certificate_issued`TINYINT(1)   DEFAULT 0,
            `created_at`        TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
            `updated_at`        TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_status` (`status`),
            INDEX `idx_dates`  (`start_date`, `end_date`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // ── training_registrations ─────────────────────────────────────────────
    $db->query("
        CREATE TABLE IF NOT EXISTS `training_registrations` (
            `id`                 INT       PRIMARY KEY AUTO_INCREMENT,
            `training_id`        INT       NOT NULL,
            `student_id`         INT       NOT NULL,
            `status`             ENUM('registered','attended','dropped','completed') DEFAULT 'registered',
            `attendance_count`   INT       DEFAULT 0,
            `certificate_issued` TINYINT(1)DEFAULT 0,
            `created_at`         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`training_id`) REFERENCES `trainings`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`student_id`)  REFERENCES `students`(`id`)  ON DELETE CASCADE,
            UNIQUE KEY `unique_registration` (`training_id`, `student_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // ── bookmarks ──────────────────────────────────────────────────────────
    $db->query("
        CREATE TABLE IF NOT EXISTS `bookmarks` (
            `id`         INT       PRIMARY KEY AUTO_INCREMENT,
            `student_id` INT       NOT NULL,
            `job_id`     INT       NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`job_id`)     REFERENCES `jobs`(`id`)     ON DELETE CASCADE,
            UNIQUE KEY `unique_bookmark` (`student_id`, `job_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // ── notifications ──────────────────────────────────────────────────────
    $db->query("
        CREATE TABLE IF NOT EXISTS `notifications` (
            `id`         INT          PRIMARY KEY AUTO_INCREMENT,
            `user_id`    INT          NULL,
            `title`      VARCHAR(255) NOT NULL,
            `message`    TEXT         NOT NULL,
            `type`       ENUM('info','success','warning','danger','announcement') DEFAULT 'info',
            `category`   ENUM('system','job','interview','placement','training','announcement') DEFAULT 'system',
            `is_read`    TINYINT(1)   DEFAULT 0,
            `is_global`  TINYINT(1)   DEFAULT 0,
            `link`       VARCHAR(500) NULL,
            `created_at` TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
            INDEX `idx_user`   (`user_id`),
            INDEX `idx_read`   (`is_read`),
            INDEX `idx_global` (`is_global`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // ── documents ──────────────────────────────────────────────────────────
    $db->query("
        CREATE TABLE IF NOT EXISTS `documents` (
            `id`            INT          PRIMARY KEY AUTO_INCREMENT,
            `user_id`       INT          NOT NULL,
            `document_type` ENUM('certificate','marksheet','id_proof','offer_letter','other') DEFAULT 'other',
            `file_path`     VARCHAR(255) NOT NULL,
            `original_name` VARCHAR(255) NOT NULL,
            `file_size`     INT          NULL,
            `mime_type`     VARCHAR(100) NULL,
            `description`   TEXT         NULL,
            `created_at`    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // ── student_projects ───────────────────────────────────────────────────
    $db->query("
        CREATE TABLE IF NOT EXISTS `student_projects` (
            `id`          INT          PRIMARY KEY AUTO_INCREMENT,
            `student_id`  INT          NOT NULL,
            `title`       VARCHAR(255) NOT NULL,
            `description` TEXT         NULL,
            `technologies`TEXT         NULL,
            `project_url` VARCHAR(500) NULL,
            `github_url`  VARCHAR(500) NULL,
            `start_date`  DATE         NULL,
            `end_date`    DATE         NULL,
            `created_at`  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // ── student_certifications ─────────────────────────────────────────────
    $db->query("
        CREATE TABLE IF NOT EXISTS `student_certifications` (
            `id`             INT          PRIMARY KEY AUTO_INCREMENT,
            `student_id`     INT          NOT NULL,
            `title`          VARCHAR(255) NOT NULL,
            `issuing_org`    VARCHAR(255) NULL,
            `issue_date`     DATE         NULL,
            `expiry_date`    DATE         NULL,
            `credential_id`  VARCHAR(100) NULL,
            `credential_url` VARCHAR(500) NULL,
            `created_at`     TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // ── student_languages ──────────────────────────────────────────────────
    $db->query("
        CREATE TABLE IF NOT EXISTS `student_languages` (
            `id`          INT          PRIMARY KEY AUTO_INCREMENT,
            `student_id`  INT          NOT NULL,
            `language`    VARCHAR(100) NOT NULL,
            `proficiency` ENUM('beginner','intermediate','advanced','native') DEFAULT 'intermediate',
            `created_at`  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // ── student_achievements ───────────────────────────────────────────────
    $db->query("
        CREATE TABLE IF NOT EXISTS `student_achievements` (
            `id`          INT          PRIMARY KEY AUTO_INCREMENT,
            `student_id`  INT          NOT NULL,
            `title`       VARCHAR(255) NOT NULL,
            `description` TEXT         NULL,
            `date`        DATE         NULL,
            `created_at`  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // ── faculty ────────────────────────────────────────────────────────────
    $db->query("
        CREATE TABLE IF NOT EXISTS `faculty` (
            `id`             INT          PRIMARY KEY AUTO_INCREMENT,
            `name`           VARCHAR(150) NOT NULL,
            `email`          VARCHAR(255) NULL,
            `phone`          VARCHAR(15)  NULL,
            `department`     VARCHAR(100) NULL,
            `designation`    VARCHAR(100) NULL,
            `specialization` TEXT         NULL,
            `status`         ENUM('active','inactive') DEFAULT 'active',
            `created_at`     TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
            `updated_at`     TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // ── universities ───────────────────────────────────────────────────────
    $db->query("
        CREATE TABLE IF NOT EXISTS `universities` (
            `id`                 INT          PRIMARY KEY AUTO_INCREMENT,
            `name`               VARCHAR(255) NOT NULL,
            `city`               VARCHAR(100) NULL,
            `country`            VARCHAR(100) DEFAULT 'India',
            `ranking`            INT          NULL,
            `website`            VARCHAR(500) NULL,
            `description`        TEXT         NULL,
            `admission_deadline` DATE         NULL,
            `course_count`       INT          DEFAULT 0,
            `status`             ENUM('active','inactive') DEFAULT 'active',
            `created_at`         TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
            `updated_at`         TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // ── courses ────────────────────────────────────────────────────────────
    $db->query("
        CREATE TABLE IF NOT EXISTS `courses` (
            `id`            INT          PRIMARY KEY AUTO_INCREMENT,
            `university_id` INT          NOT NULL,
            `name`          VARCHAR(255) NOT NULL,
            `duration`      VARCHAR(100) NULL,
            `degree_type`   VARCHAR(100) NULL,
            `description`   TEXT         NULL,
            `status`        ENUM('active','inactive') DEFAULT 'active',
            `created_at`    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`university_id`) REFERENCES `universities`(`id`) ON DELETE CASCADE,
            INDEX `idx_university` (`university_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // ── entrance_exams ─────────────────────────────────────────────────────
    $db->query("
        CREATE TABLE IF NOT EXISTS `entrance_exams` (
            `id`                    INT          PRIMARY KEY AUTO_INCREMENT,
            `name`                  VARCHAR(255) NOT NULL,
            `full_name`             VARCHAR(500) NULL,
            `conducting_body`       VARCHAR(255) NULL,
            `exam_date`             DATE         NULL,
            `registration_deadline` DATE         NULL,
            `website`               VARCHAR(500) NULL,
            `description`           TEXT         NULL,
            `status`                ENUM('active','inactive') DEFAULT 'active',
            `created_at`            TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // ── scholarships ───────────────────────────────────────────────────────
    $db->query("
        CREATE TABLE IF NOT EXISTS `scholarships` (
            `id`                   INT           PRIMARY KEY AUTO_INCREMENT,
            `name`                 VARCHAR(255)  NOT NULL,
            `provider`             VARCHAR(255)  NULL,
            `amount`               DECIMAL(12,2) NULL,
            `currency`             VARCHAR(10)   DEFAULT 'INR',
            `type`                 ENUM('merit','need-based','research','sport','other') DEFAULT 'merit',
            `eligibility`          TEXT          NULL,
            `application_deadline` DATE          NULL,
            `website`              VARCHAR(500)  NULL,
            `description`          TEXT          NULL,
            `status`               ENUM('active','inactive') DEFAULT 'active',
            `created_at`           TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // ── higher_study_applications ──────────────────────────────────────────
    $db->query("
        CREATE TABLE IF NOT EXISTS `higher_study_applications` (
            `id`              INT          PRIMARY KEY AUTO_INCREMENT,
            `student_id`      INT          NOT NULL,
            `university_id`   INT          NULL,
            `course_id`       INT          NULL,
            `university_name` VARCHAR(255) NOT NULL,
            `country`         VARCHAR(100) NULL,
            `course_name`     VARCHAR(255) NULL,
            `exam_score`      VARCHAR(50)  NULL,
            `status`          ENUM('interested','applied','accepted','rejected','enrolled') DEFAULT 'interested',
            `notes`           TEXT         NULL,
            `created_at`      TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`student_id`)    REFERENCES `students`(`id`)    ON DELETE CASCADE,
            FOREIGN KEY (`university_id`) REFERENCES `universities`(`id`) ON DELETE SET NULL,
            FOREIGN KEY (`course_id`)     REFERENCES `courses`(`id`)      ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // ── activity_logs ──────────────────────────────────────────────────────
    $db->query("
        CREATE TABLE IF NOT EXISTS `activity_logs` (
            `id`          INT          PRIMARY KEY AUTO_INCREMENT,
            `user_id`     INT          NULL,
            `action`      VARCHAR(100) NOT NULL,
            `module`      VARCHAR(100) NULL,
            `description` TEXT         NULL,
            `ip_address`  VARCHAR(45)  NULL,
            `user_agent`  TEXT         NULL,
            `created_at`  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
            INDEX `idx_action`  (`action`),
            INDEX `idx_created` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // ── password_resets ────────────────────────────────────────────────────
    $db->query("
        CREATE TABLE IF NOT EXISTS `password_resets` (
            `id`         INT          PRIMARY KEY AUTO_INCREMENT,
            `user_id`    INT          NOT NULL,
            `token`      VARCHAR(255) NOT NULL,
            `used`       TINYINT(1)   NOT NULL DEFAULT 0,
            `expires_at` DATETIME     NOT NULL,
            `created_at` TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
            INDEX `idx_token`   (`token`),
            INDEX `idx_user_id` (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $db->query("SET FOREIGN_KEY_CHECKS = 1");
};
