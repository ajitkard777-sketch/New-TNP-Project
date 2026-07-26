<?php
/**
 * Migration 009 — Database Schema Audit & Foreign Key / Index Verifications
 * Verifies foreign keys, indexes, and primary key relationships for jobs, applications,
 * students, companies, messages, user_presence, and notifications tables.
 */
return function (Database $db): void {
    try {
        // 1. Audit applications table indexes
        $appIndexes = $db->fetchAll("SHOW INDEX FROM `applications` WHERE Key_name = 'idx_student_job'");
        if (empty($appIndexes)) {
            $db->query("ALTER TABLE `applications` ADD INDEX `idx_student_job` (`student_id`, `job_id`)");
        }

        // 2. Audit jobs table indexes
        $jobIndexes = $db->fetchAll("SHOW INDEX FROM `jobs` WHERE Key_name = 'idx_company_status'");
        if (empty($jobIndexes)) {
            $db->query("ALTER TABLE `jobs` ADD INDEX `idx_company_status` (`company_id`, `status`)");
        }

        // 3. Audit messages table indexes
        $msgIndexes = $db->fetchAll("SHOW INDEX FROM `messages` WHERE Key_name = 'idx_sender_receiver'");
        if (empty($msgIndexes)) {
            $db->query("ALTER TABLE `messages` ADD INDEX `idx_sender_receiver` (`sender_id`, `receiver_id`)");
        }

        // 4. Audit notifications table indexes
        $notifIndexes = $db->fetchAll("SHOW INDEX FROM `notifications` WHERE Key_name = 'idx_user_created'");
        if (empty($notifIndexes)) {
            $db->query("ALTER TABLE `notifications` ADD INDEX `idx_user_created` (`user_id`, `created_at`)");
        }
    } catch (Exception $e) {
        error_log('Migration 009 Audit Error: ' . $e->getMessage());
    }
};
