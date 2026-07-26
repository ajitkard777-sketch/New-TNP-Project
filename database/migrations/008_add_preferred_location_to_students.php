<?php
/**
 * Migration 008 — Add preferred_location to students table
 */
return function (Database $db): void {
    try {
        // Check if preferred_location column already exists
        $columns = $db->fetchAll("SHOW COLUMNS FROM `students` LIKE 'preferred_location'");
        if (empty($columns)) {
            $db->query("ALTER TABLE `students` ADD COLUMN `preferred_location` VARCHAR(255) NULL AFTER `pincode`");
        }
    } catch (Exception $e) {
        error_log('Migration 008 Error: ' . $e->getMessage());
    }
};
