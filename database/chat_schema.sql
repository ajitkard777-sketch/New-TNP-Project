USE team1;

-- 1. Chat Conversations Table
CREATE TABLE IF NOT EXISTS `chat_conversations` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_one_id` INT NOT NULL,
    `user_two_id` INT NOT NULL,
    `last_message_id` INT NULL,
    `last_message_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `archived_by` JSON NULL,
    `deleted_by` JSON NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_one_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_two_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_conversation` (`user_one_id`, `user_two_id`),
    INDEX `idx_user_one` (`user_one_id`),
    INDEX `idx_user_two` (`user_two_id`),
    INDEX `idx_last_msg` (`last_message_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Chat Messages Table
CREATE TABLE IF NOT EXISTS `chat_messages` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `conversation_id` INT NOT NULL,
    `sender_id` INT NOT NULL,
    `receiver_id` INT NOT NULL,
    `message` TEXT NULL,
    `is_read` TINYINT(1) DEFAULT 0,
    `read_at` DATETIME NULL,
    `is_deleted` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`conversation_id`) REFERENCES `chat_conversations`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`sender_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`receiver_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_conv_time` (`conversation_id`, `created_at`),
    INDEX `idx_unread` (`receiver_id`, `is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Chat Attachments Table
CREATE TABLE IF NOT EXISTS `chat_attachments` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `message_id` INT NOT NULL,
    `file_name` VARCHAR(255) NOT NULL,
    `file_path` VARCHAR(255) NOT NULL,
    `file_type` VARCHAR(50) NOT NULL,
    `file_size` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`message_id`) REFERENCES `chat_messages`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Chat Presence & Typing Table
CREATE TABLE IF NOT EXISTS `chat_presence` (
    `user_id` INT PRIMARY KEY,
    `last_active_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `typing_to_user_id` INT NULL,
    `typing_updated_at` DATETIME NULL,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
