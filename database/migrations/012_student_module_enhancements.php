<?php
/**
 * Migration 012 — Student Module Enhancements & Placement Calendar
 *
 * Adds columns to interviews, student_achievements,
 * Creates student_certificates and placement_calendar_events tables.
 */
return function (Database $db): void {
    try {
        $db->query("SET FOREIGN_KEY_CHECKS = 0");

        // 1. Extend `interviews` table
        $interviewCols = [
            'call_letter_path' => 'VARCHAR(255) NULL',
            'required_documents' => 'TEXT NULL',
            'round_type' => "VARCHAR(100) NULL DEFAULT 'Technical'"
        ];
        foreach ($interviewCols as $col => $def) {
            $existing = $db->fetchAll("SHOW COLUMNS FROM `interviews` LIKE '{$col}'");
            if (empty($existing)) {
                $db->query("ALTER TABLE `interviews` ADD COLUMN `{$col}` {$def}");
            }
        }

        // 2. Extend `student_achievements` table
        $achievementCols = [
            'category' => "VARCHAR(100) NOT NULL DEFAULT 'Others'",
            'organizer' => 'VARCHAR(255) NULL',
            'position_rank' => 'VARCHAR(100) NULL',
            'certificate_file' => 'VARCHAR(255) NULL',
            'achievement_image' => 'VARCHAR(255) NULL',
            'achievement_date' => 'DATE NULL',
            'status' => "ENUM('pending','verified','rejected') NOT NULL DEFAULT 'pending'",
            'admin_remarks' => 'TEXT NULL'
        ];
        foreach ($achievementCols as $col => $def) {
            $existing = $db->fetchAll("SHOW COLUMNS FROM `student_achievements` LIKE '{$col}'");
            if (empty($existing)) {
                $db->query("ALTER TABLE `student_achievements` ADD COLUMN `{$col}` {$def}");
            }
        }

        // If 'date' column exists in student_achievements, copy values to achievement_date if achievement_date is null
        $dateCol = $db->fetchAll("SHOW COLUMNS FROM `student_achievements` LIKE 'date'");
        if (!empty($dateCol)) {
            $db->query("UPDATE `student_achievements` SET achievement_date = date WHERE achievement_date IS NULL AND date IS NOT NULL");
        }

        // 3. Create `student_certificates` table
        $db->query("
            CREATE TABLE IF NOT EXISTS `student_certificates` (
                `id`                   INT AUTO_INCREMENT PRIMARY KEY,
                `student_id`           INT NOT NULL,
                `name`                 VARCHAR(255) NOT NULL,
                `issuing_organization` VARCHAR(255) NULL,
                `issue_date`           DATE NULL,
                `expiry_date`          DATE NULL,
                `credential_id`        VARCHAR(100) NULL,
                `credential_url`       VARCHAR(500) NULL,
                `certificate_file`     VARCHAR(255) NOT NULL,
                `skills_covered`       TEXT NULL,
                `status`               ENUM('pending','verified','rejected') NOT NULL DEFAULT 'pending',
                `admin_remarks`        TEXT NULL,
                `created_at`           TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at`           TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
                INDEX `idx_student` (`student_id`),
                INDEX `idx_status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // 4. Create `placement_calendar_events` table
        $db->query("
            CREATE TABLE IF NOT EXISTS `placement_calendar_events` (
                `id`                INT AUTO_INCREMENT PRIMARY KEY,
                `event_type`        ENUM('interview','drive','mock_test','workshop','deadline','activity','training','other') NOT NULL DEFAULT 'activity',
                `title`             VARCHAR(255) NOT NULL,
                `description`       TEXT NULL,
                `event_date`        DATE NOT NULL,
                `start_time`        TIME NULL,
                `end_time`          TIME NULL,
                `venue`             VARCHAR(255) NULL,
                `organizer`         VARCHAR(255) NULL,
                `company_id`        INT NULL,
                `job_id`            INT NULL,
                `registration_link` VARCHAR(500) NULL,
                `color`             VARCHAR(20) DEFAULT '#3b82f6',
                `created_by`        INT NULL,
                `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE SET NULL,
                FOREIGN KEY (`job_id`) REFERENCES `jobs`(`id`) ON DELETE SET NULL,
                INDEX `idx_event_date` (`event_date`),
                INDEX `idx_event_type` (`event_type`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $db->query("SET FOREIGN_KEY_CHECKS = 1");

    } catch (Exception $e) {
        error_log('Migration 012 Error: ' . $e->getMessage());
    }
};
