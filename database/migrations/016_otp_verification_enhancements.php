<?php
/**
 * Migration 016 — OTP Verification Enhancements
 *
 * Adds columns to users table:
 *  - otp_resend_last_at  DATETIME NULL
 *  - otp_attempts        INT NOT NULL DEFAULT 0
 *  - otp_resend_count     INT NOT NULL DEFAULT 0
 *
 * Fully idempotent / safe to re-run.
 */
return function (Database $db): void {

    $pdo = $db->getConnection();

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

    if (!$hasColumn('users', 'otp_resend_last_at')) {
        $db->query(
            "ALTER TABLE `users`
             ADD COLUMN `otp_resend_last_at` DATETIME NULL AFTER `otp_expires_at`"
        );
    }

    if (!$hasColumn('users', 'otp_attempts')) {
        $db->query(
            "ALTER TABLE `users`
             ADD COLUMN `otp_attempts` INT NOT NULL DEFAULT 0 AFTER `otp_resend_last_at`"
        );
    }

    if (!$hasColumn('users', 'otp_resend_count')) {
        $db->query(
            "ALTER TABLE `users`
             ADD COLUMN `otp_resend_count` INT NOT NULL DEFAULT 0 AFTER `otp_attempts`"
        );
    }
};
