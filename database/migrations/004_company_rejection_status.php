<?php
/**
 * Migration 004 — Add company rejection tracking columns
 *
 * Adds:
 *  - companies.is_rejected   TINYINT(1) DEFAULT 0
 *  - companies.rejection_reason VARCHAR(500) NULL
 *
 * Both guarded with information_schema checks — safe to re-run.
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

    // ── is_rejected ───────────────────────────────────────────────────────
    if (!$hasColumn('companies', 'is_rejected')) {
        $db->query(
            "ALTER TABLE `companies`
             ADD COLUMN `is_rejected` TINYINT(1) NOT NULL DEFAULT 0
             AFTER `is_approved`"
        );
    }

    // ── rejection_reason ──────────────────────────────────────────────────
    if (!$hasColumn('companies', 'rejection_reason')) {
        $db->query(
            "ALTER TABLE `companies`
             ADD COLUMN `rejection_reason` VARCHAR(500) NULL
             AFTER `is_rejected`"
        );
    }
};
