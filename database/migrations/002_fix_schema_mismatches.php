<?php
/**
 * Migration 002 — Fix existing schema mismatches on already-created tables
 *
 * This migration is safe to run even if the tables were created by importing
 * tpms.sql manually. Every ALTER is guarded by a SHOW COLUMNS / SHOW TABLES
 * check so it will never fail on "column already exists" errors.
 */
return function (Database $db): void {

    $pdo = $db->getConnection();

    /* ── helper: does a column exist? ────────────────────────────────────── */
    $hasColumn = function (string $table, string $column) use ($pdo): bool {
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME   = ?
               AND COLUMN_NAME  = ?"
        );
        $stmt->execute([$table, $column]);
        return (bool) $stmt->fetchColumn();
    };

    /* ── helper: does a table exist? ─────────────────────────────────────── */
    $hasTable = function (string $table) use ($pdo): bool {
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?"
        );
        $stmt->execute([$table]);
        return (bool) $stmt->fetchColumn();
    };

    /* ── helper: current ENUM values for a column ────────────────────────── */
    $getEnumValues = function (string $table, string $column) use ($pdo): array {
        $stmt = $pdo->prepare(
            "SELECT COLUMN_TYPE FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME   = ?
               AND COLUMN_NAME  = ?"
        );
        $stmt->execute([$table, $column]);
        $type = $stmt->fetchColumn();
        if (!$type) return [];
        preg_match_all("/'([^']+)'/", $type, $matches);
        return $matches[1] ?? [];
    };

    // ── 1. users.theme_preference ─────────────────────────────────────────
    if (!$hasColumn('users', 'theme_preference')) {
        $db->query(
            "ALTER TABLE `users`
             ADD COLUMN `theme_preference` VARCHAR(50) NOT NULL DEFAULT 'light'
             AFTER `locked_until`"
        );
    }

    // ── 2. users.status ENUM — ensure 'blocked' is present ───────────────
    $enumVals = $getEnumValues('users', 'status');
    if (!in_array('blocked', $enumVals, true)) {
        // Merge with whatever values already exist + add 'blocked'
        $merged  = array_unique(array_merge($enumVals, ['blocked']));
        $list    = implode("','", array_map('addslashes', $merged));
        $db->query(
            "ALTER TABLE `users`
             MODIFY COLUMN `status` ENUM('{$list}') NOT NULL DEFAULT 'active'"
        );
    }

    // ── 3. users: rename otp_expiry → otp_expires_at (old schema compat) ──
    if ($hasColumn('users', 'otp_expiry') && !$hasColumn('users', 'otp_expires_at')) {
        $db->query(
            "ALTER TABLE `users`
             CHANGE COLUMN `otp_expiry` `otp_expires_at` DATETIME NULL"
        );
    }

    // ── 4. documents.mime_type ────────────────────────────────────────────
    if ($hasTable('documents') && !$hasColumn('documents', 'mime_type')) {
        $db->query(
            "ALTER TABLE `documents`
             ADD COLUMN `mime_type` VARCHAR(100) NULL AFTER `file_size`"
        );
    }

    // ── 5. documents.description ──────────────────────────────────────────
    if ($hasTable('documents') && !$hasColumn('documents', 'description')) {
        $db->query(
            "ALTER TABLE `documents`
             ADD COLUMN `description` TEXT NULL AFTER `mime_type`"
        );
    }

    // ── 6. applications.resume_snapshot ───────────────────────────────────
    if ($hasTable('applications') && !$hasColumn('applications', 'resume_snapshot')) {
        $db->query(
            "ALTER TABLE `applications`
             ADD COLUMN `resume_snapshot` VARCHAR(255) NULL AFTER `cover_letter`"
        );
    }

    // ── 7. student_projects.github_url ────────────────────────────────────
    if ($hasTable('student_projects') && !$hasColumn('student_projects', 'github_url')) {
        $db->query(
            "ALTER TABLE `student_projects`
             ADD COLUMN `github_url` VARCHAR(500) NULL AFTER `project_url`"
        );
    }

    // ── 8. student_certifications.expiry_date ────────────────────────────
    if ($hasTable('student_certifications') && !$hasColumn('student_certifications', 'expiry_date')) {
        $db->query(
            "ALTER TABLE `student_certifications`
             ADD COLUMN `expiry_date` DATE NULL AFTER `issue_date`"
        );
    }

    // ── 9. higher_study_applications.course_id ───────────────────────────
    if ($hasTable('higher_study_applications') && !$hasColumn('higher_study_applications', 'course_id')) {
        $db->query(
            "ALTER TABLE `higher_study_applications`
             ADD COLUMN `course_id` INT NULL AFTER `university_id`"
        );
        // Add FK only if courses table exists
        if ($hasTable('courses')) {
            try {
                $db->query(
                    "ALTER TABLE `higher_study_applications`
                     ADD CONSTRAINT `fk_hsa_course`
                     FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE SET NULL"
                );
            } catch (Exception $e) {
                // FK may already exist — ignore duplicate constraint errors
            }
        }
    }

    // ── 10. higher_study_applications.exam_score ─────────────────────────
    if ($hasTable('higher_study_applications') && !$hasColumn('higher_study_applications', 'exam_score')) {
        $db->query(
            "ALTER TABLE `higher_study_applications`
             ADD COLUMN `exam_score` VARCHAR(50) NULL AFTER `course_name`"
        );
    }

    // ── 11. higher_study_applications.notes ──────────────────────────────
    if ($hasTable('higher_study_applications') && !$hasColumn('higher_study_applications', 'notes')) {
        $db->query(
            "ALTER TABLE `higher_study_applications`
             ADD COLUMN `notes` TEXT NULL AFTER `status`"
        );
    }

    // ── 12. companies.company_type — add 'other' if missing ──────────────
    if ($hasTable('companies')) {
        $enumVals = $getEnumValues('companies', 'company_type');
        if (!empty($enumVals) && !in_array('other', $enumVals, true)) {
            $merged = array_unique(array_merge($enumVals, ['other']));
            $list   = implode("','", array_map('addslashes', $merged));
            $db->query(
                "ALTER TABLE `companies`
                 MODIFY COLUMN `company_type`
                 ENUM('{$list}') NULL"
            );
        }
    }
};
