-- =====================================================
-- TPMS - Database Migration: OTP Verification Columns
-- =====================================================

ALTER TABLE `users` MODIFY COLUMN `otp` VARCHAR(255) NULL;
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `otp_resend_count` INT NOT NULL DEFAULT 0 AFTER `otp_expires_at`;
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `otp_last_sent_at` DATETIME NULL AFTER `otp_resend_count`;
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `otp_attempts` INT NOT NULL DEFAULT 0 AFTER `otp_last_sent_at`;
