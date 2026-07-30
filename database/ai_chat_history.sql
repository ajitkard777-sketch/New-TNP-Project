USE team1;
CREATE TABLE IF NOT EXISTS `ai_chat_history` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `role` ENUM('student','company','admin') NOT NULL,
    `session_id` VARCHAR(128) NOT NULL,
    `sender` ENUM('user','ai') NOT NULL DEFAULT 'user',
    `message` TEXT NOT NULL,
    `context_snapshot` JSON NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_user_session` (`user_id`, `session_id`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
