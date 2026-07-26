<?php
/**
 * Migration 014 — Enhance Training Registrations Table Status Column
 */
return function (Database $db): void {
    try {
        // Update status ENUM to include 'cancelled'
        $db->query("ALTER TABLE `training_registrations` MODIFY COLUMN `status` ENUM('registered','attended','dropped','completed','cancelled') DEFAULT 'registered'");
    } catch (Exception $e) {
        error_log('Migration 014 Error: ' . $e->getMessage());
    }
};
