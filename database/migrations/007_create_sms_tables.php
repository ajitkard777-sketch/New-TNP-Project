<?php
/**
 * Migration 007 — Create SMS logging and settings tables
 *
 * Tables:
 *  - sms_logs (stores historical records of all outbound SMS, status, provider, error, retries)
 *  - sms_settings (stores runtime configuration overrides for provider settings & templates)
 */
return function (Database $db): void {

    // ── 1. sms_logs ──────────────────────────────────────────────────────────
    $db->query("
        CREATE TABLE IF NOT EXISTS `sms_logs` (
            `id`               INT           PRIMARY KEY AUTO_INCREMENT,
            `user_id`          INT           NULL,
            `recipient_phone`  VARCHAR(25)   NOT NULL,
            `event_type`       VARCHAR(50)   NOT NULL DEFAULT 'general',
            `provider`         VARCHAR(50)   NOT NULL,
            `message`          TEXT          NOT NULL,
            `status`           ENUM('sent', 'failed', 'pending') NOT NULL DEFAULT 'pending',
            `error_message`    TEXT          NULL,
            `retry_count`      INT           NOT NULL DEFAULT 0,
            `sent_at`          DATETIME      NULL,
            `created_at`       TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
            INDEX `idx_sms_status`    (`status`),
            INDEX `idx_sms_event`     (`event_type`),
            INDEX `idx_sms_provider`  (`provider`),
            INDEX `idx_sms_phone`     (`recipient_phone`),
            INDEX `idx_sms_created`   (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // ── 2. sms_settings ────────────────────────────────----------------------
    $db->query("
        CREATE TABLE IF NOT EXISTS `sms_settings` (
            `setting_key`    VARCHAR(100) PRIMARY KEY,
            `setting_value`  TEXT         NULL,
            `updated_at`     TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
};
