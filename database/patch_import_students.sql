-- =====================================================
-- TPMS - Patch: Import Students Feature
-- Run this patch to add missing columns for Excel import
-- =====================================================

-- Add registration_no column (separate from enrollment_no/PRN)
ALTER TABLE `students`
    ADD COLUMN IF NOT EXISTS `registration_no` VARCHAR(50) NULL COMMENT 'University Registration Number' AFTER `enrollment_no`;

-- Add parent/guardian info columns
ALTER TABLE `students`
    ADD COLUMN IF NOT EXISTS `parent_name` VARCHAR(150) NULL COMMENT 'Parent or Guardian Name' AFTER `portfolio`;

ALTER TABLE `students`
    ADD COLUMN IF NOT EXISTS `parent_phone` VARCHAR(15) NULL COMMENT 'Parent or Guardian Phone' AFTER `parent_name`;

-- Add OTP resend tracking columns (if not already present from previous patch)
ALTER TABLE `users`
    ADD COLUMN IF NOT EXISTS `otp_last_sent_at` DATETIME NULL AFTER `otp_expires_at`,
    ADD COLUMN IF NOT EXISTS `otp_resend_count` INT NOT NULL DEFAULT 0 AFTER `otp_last_sent_at`,
    ADD COLUMN IF NOT EXISTS `otp_attempts` INT NOT NULL DEFAULT 0 AFTER `otp_resend_count`,
    ADD COLUMN IF NOT EXISTS `theme_preference` VARCHAR(20) NOT NULL DEFAULT 'light' AFTER `otp_attempts`;

-- Add index on registration_no for fast duplicate checks during import
ALTER TABLE `students`
    ADD INDEX IF NOT EXISTS `idx_registration_no` (`registration_no`);
