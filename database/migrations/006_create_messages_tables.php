<?php
/**
 * Migration 006 — Create messaging and user presence tables
 *
 * Tables:
 *  - messages (chat history, file attachments, read receipts)
 *  - user_presence (online status tracking, typing indicator)
 */
return function (Database $db): void {

    $pdo = $db->getConnection();

    // ── 1. messages ───────────────────────────────────────────────────────
    $db->query("
        CREATE TABLE IF NOT EXISTS `messages` (
            `id`            INT           PRIMARY KEY AUTO_INCREMENT,
            `sender_id`     INT           NOT NULL,
            `receiver_id`   INT           NOT NULL,
            `job_id`        INT           NULL,
            `message`       TEXT          NULL,
            `file_path`     VARCHAR(255)  NULL,
            `file_name`     VARCHAR(255)  NULL,
            `file_type`     VARCHAR(50)   NULL,
            `file_size`     INT           NULL,
            `is_read`       TINYINT(1)    NOT NULL DEFAULT 0,
            `read_at`       DATETIME      NULL,
            `created_at`    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`sender_id`)   REFERENCES `users`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`receiver_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`job_id`)      REFERENCES `jobs`(`id`)  ON DELETE SET NULL,
            INDEX `idx_sender_receiver` (`sender_id`, `receiver_id`),
            INDEX `idx_receiver_read`   (`receiver_id`, `is_read`),
            INDEX `idx_created_at`      (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // ── 2. user_presence ─────────────────────────────────────────────────
    $db->query("
        CREATE TABLE IF NOT EXISTS `user_presence` (
            `user_id`           INT        PRIMARY KEY,
            `last_activity`     DATETIME   NOT NULL,
            `typing_target_id`  INT        NULL,
            `typing_updated_at` DATETIME   NULL,
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
};
