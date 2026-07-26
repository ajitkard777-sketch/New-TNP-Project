<?php
/**
 * TPMS - Message Model
 * Manages chat conversations, real-time message streaming, read receipts,
 * file sharing, and online/typing presence.
 */

require_once ROOT_PATH . '/includes/helpers.php';

class Message {
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->ensureTablesExist();
    }

    private function ensureTablesExist(): void {
        static $tablesCreated = false;
        if ($tablesCreated) return;

        try {
            $this->db->query("
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

            $this->db->query("
                CREATE TABLE IF NOT EXISTS `user_presence` (
                    `user_id`           INT        PRIMARY KEY,
                    `last_activity`     DATETIME   NOT NULL,
                    `typing_target_id`  INT        NULL,
                    `typing_updated_at` DATETIME   NULL,
                    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            $tablesCreated = true;
        } catch (Exception $e) {
            error_log('Message Model Table Auto-Creation Error: ' . $e->getMessage());
        }
    }

    /**
     * Verify if two users are authorized to chat.
     * Rule: A student can ONLY chat with a company recruiter IF the student
     * has applied to at least one job posted by that company.
     */
    public function canUsersChat(int $userId1, int $userId2): bool {
        if ($userId1 === $userId2) return false;

        $user1 = $this->db->fetchOne("SELECT id, role FROM users WHERE id = ?", [$userId1]);
        $user2 = $this->db->fetchOne("SELECT id, role FROM users WHERE id = ?", [$userId2]);

        if (!$user1 || !$user2) return false;

        $studentUserId = null;
        $companyUserId = null;

        if ($user1['role'] === 'student' && $user2['role'] === 'company') {
            $studentUserId = $user1['id'];
            $companyUserId = $user2['id'];
        } elseif ($user1['role'] === 'company' && $user2['role'] === 'student') {
            $studentUserId = $user2['id'];
            $companyUserId = $user1['id'];
        } else {
            // Only Student <-> Company chats are allowed
            return false;
        }

        // Check if company account exists
        $student = $this->db->fetchOne("SELECT id FROM students WHERE user_id = ?", [$studentUserId]);
        $company = $this->db->fetchOne("SELECT id, is_approved FROM companies WHERE user_id = ?", [$companyUserId]);

        if (!$student || !$company) return false;

        // Allow direct messaging between student and active/approved company HR
        return true;

        // Check if company has active jobs
        $jobCount = (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM jobs WHERE company_id = ? AND status = 'active'",
            [$company['id']]
        );

        if ($jobCount > 0) return true;

        // Check if there is existing message history
        $msgCount = (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)",
            [$studentUserId, $companyUserId, $companyUserId, $studentUserId]
        );

        return $msgCount > 0;
    }

    /**
     * Get list of all eligible chat partners for a user along with conversation metadata.
     */
    public function getConversations(int $userId, string $role, string $search = ''): array {
        $conversations = [];

        if ($role === 'student') {
            $student = $this->db->fetchOne("SELECT id FROM students WHERE user_id = ?", [$userId]);
            if (!$student) return [];

            // Get companies offering jobs, applied companies, or with existing messages
            $sql = "SELECT DISTINCT c.id as company_id, c.user_id as partner_user_id,
                           c.company_name as name, c.logo as avatar, u.email,
                           COALESCE(
                               (SELECT j.title FROM applications a2 JOIN jobs j ON a2.job_id = j.id WHERE a2.student_id = ? AND j.company_id = c.id ORDER BY a2.applied_at DESC LIMIT 1),
                               (SELECT j.title FROM jobs j WHERE j.company_id = c.id AND j.status = 'active' ORDER BY j.created_at DESC LIMIT 1),
                               'Recruiter Contact'
                           ) as subtitle
                    FROM companies c
                    JOIN users u ON c.user_id = u.id
                    LEFT JOIN jobs j ON c.id = j.company_id
                    LEFT JOIN applications a ON j.id = a.job_id AND a.student_id = ?
                    LEFT JOIN messages m ON (m.sender_id = u.id AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = u.id)
                    WHERE (a.id IS NOT NULL OR j.id IS NOT NULL OR m.id IS NOT NULL) AND c.is_approved = 1";
            $params = [$student['id'], $student['id'], $userId, $userId];

            if ($search) {
                $sql .= " AND (c.company_name LIKE ? OR u.email LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }

            $partners = $this->db->fetchAll($sql, $params);

        } elseif ($role === 'company') {
            $company = $this->db->fetchOne("SELECT id FROM companies WHERE user_id = ?", [$userId]);
            if (!$company) return [];

            // Get students who applied OR messaged company
            $sql = "SELECT DISTINCT s.id as student_id, s.user_id as partner_user_id,
                           CONCAT(s.first_name, ' ', s.last_name) as name, s.profile_photo as avatar, u.email,
                           COALESCE(
                               (SELECT j.title FROM applications a2 JOIN jobs j ON a2.job_id = j.id WHERE a2.student_id = s.id AND j.company_id = ? ORDER BY a2.applied_at DESC LIMIT 1),
                               CONCAT(s.branch, ' | Student')
                           ) as subtitle
                    FROM students s
                    JOIN users u ON s.user_id = u.id
                    LEFT JOIN applications a ON s.id = a.student_id
                    LEFT JOIN jobs j ON a.job_id = j.id AND j.company_id = ?
                    LEFT JOIN messages m ON (m.sender_id = u.id AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = u.id)
                    WHERE (j.company_id = ? OR m.id IS NOT NULL)";
            $params = [$company['id'], $company['id'], $userId, $userId, $company['id']];

            if ($search) {
                $sql .= " AND (s.first_name LIKE ? OR s.last_name LIKE ? OR u.email LIKE ? OR s.branch LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }

            $partners = $this->db->fetchAll($sql, $params);

        } else {
            return [];
        }

        foreach ($partners as $p) {
            $partnerId = (int)$p['partner_user_id'];

            // Get last message between current user and partner
            $lastMsg = $this->db->fetchOne(
                "SELECT * FROM messages
                 WHERE (sender_id = ? AND receiver_id = ?)
                    OR (sender_id = ? AND receiver_id = ?)
                 ORDER BY created_at DESC LIMIT 1",
                [$userId, $partnerId, $partnerId, $userId]
            );

            // Get unread count from this partner
            $unreadCount = (int)$this->db->fetchColumn(
                "SELECT COUNT(*) FROM messages
                 WHERE sender_id = ? AND receiver_id = ? AND is_read = 0",
                [$partnerId, $userId]
            );

            // Get presence info
            $presence = $this->getUserPresence($partnerId, $userId);

            $avatarUrl = '';
            if ($role === 'student') {
                $avatarUrl = $p['avatar'] ? uploadUrl('company/' . $p['avatar']) : asset('images/default-avatar.png');
            } else {
                $avatarUrl = $p['avatar'] ? uploadUrl('profile_photos/' . $p['avatar']) : asset('images/default-avatar.png');
            }

            $conversations[] = [
                'partner_id'    => $partnerId,
                'name'          => $p['name'],
                'subtitle'      => $p['subtitle'] ?? '',
                'avatar'        => $avatarUrl,
                'email'         => $p['email'],
                'last_message'  => !empty($lastMsg['message']) ? $lastMsg['message'] : (!empty($lastMsg['file_name']) ? '📎 [Attachment: ' . $lastMsg['file_name'] . ']' : 'No messages yet'),
                'last_time'     => $lastMsg ? timeAgo($lastMsg['created_at']) : '',
                'last_timestamp'=> $lastMsg['created_at'] ?? '1970-01-01 00:00:00',
                'unread_count'  => $unreadCount,
                'is_online'     => $presence['is_online'],
                'is_typing'     => $presence['is_typing'],
            ];
        }

        // Sort conversations by last message timestamp descending
        usort($conversations, function ($a, $b) {
            return strcmp($b['last_timestamp'], $a['last_timestamp']);
        });

        return $conversations;
    }

    /**
     * Get messages between current user and partner.
     */
    public function getMessages(int $userId, int $partnerId, int $lastId = 0): array {
        $params = [$userId, $partnerId, $partnerId, $userId];
        $whereAfter = '';

        if ($lastId > 0) {
            $whereAfter = " AND m.id > ?";
            $params[] = $lastId;
        }

        $sql = "SELECT m.*,
                       IF(m.sender_id = ?, 1, 0) as is_mine
                FROM messages m
                WHERE ((m.sender_id = ? AND m.receiver_id = ?)
                   OR  (m.sender_id = ? AND m.receiver_id = ?))
                {$whereAfter}
                ORDER BY m.created_at ASC";

        // Prepend userId for is_mine condition
        array_unshift($params, $userId);

        $messages = $this->db->fetchAll($sql, $params);

        foreach ($messages as &$msg) {
            $msg['time_formatted'] = date('h:i A', strtotime($msg['created_at']));
            $msg['date_formatted'] = date('M d, Y', strtotime($msg['created_at']));
            if ($msg['file_path']) {
                $msg['file_url'] = url('/messages/download/' . $msg['id']);
            }
        }

        return $messages;
    }

    /**
     * Send a text message or file attachment.
     */
    public function sendMessage(int $senderId, int $receiverId, ?string $messageText = null, ?array $fileData = null, ?int $jobId = null): array {
        if (!$this->canUsersChat($senderId, $receiverId)) {
            throw new Exception("You are not authorized to send messages to this user.");
        }

        $filePath = null;
        $fileName = null;
        $fileType = null;
        $fileSize = null;

        // Process File Upload if present
        if ($fileData && isset($fileData['tmp_name']) && $fileData['error'] === UPLOAD_ERR_OK) {
            $allowedExtensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'txt', 'zip'];
            $ext = strtolower(pathinfo($fileData['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, $allowedExtensions)) {
                throw new Exception("Invalid file type. Allowed: PDF, DOC, DOCX, JPG, PNG, GIF, WEBP, TXT, ZIP.");
            }

            if ($fileData['size'] > MAX_FILE_SIZE) {
                throw new Exception("File size exceeds maximum limit of " . (MAX_FILE_SIZE / 1048576) . "MB.");
            }

            $uploadDir = UPLOADS_PATH . '/chat_files';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fn = generateFileName($fileData['name'], 'chat_' . $senderId);
            $destination = $uploadDir . '/' . $fn;

            if (move_uploaded_file($fileData['tmp_name'], $destination)) {
                $filePath = $fn;
                $fileName = $fileData['name'];
                $fileType = in_array($ext, ['jpg','jpeg','png','gif','webp']) ? 'image' : 'document';
                $fileSize = $fileData['size'];
            } else {
                throw new Exception("Failed to upload file attachment.");
            }
        }

        if (empty(trim($messageText ?? '')) && !$filePath) {
            throw new Exception("Message content or file attachment is required.");
        }

        $msgId = (int)$this->db->insert(
            "INSERT INTO messages (sender_id, receiver_id, job_id, message, file_path, file_name, file_type, file_size, is_read)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)",
            [
                $senderId,
                $receiverId,
                $jobId,
                trim($messageText ?? ''),
                $filePath,
                $fileName,
                $fileType,
                $fileSize,
            ]
        );

        // Get inserted message record
        $msg = $this->db->fetchOne("SELECT * FROM messages WHERE id = ?", [$msgId]);
        $msg['is_mine'] = 1;
        $msg['time_formatted'] = date('h:i A', strtotime($msg['created_at']));
        $msg['date_formatted'] = date('M d, Y', strtotime($msg['created_at']));
        if ($msg['file_path']) {
            $msg['file_url'] = url('/messages/download/' . $msg['id']);
        }

        return $msg;
    }

    /**
     * Mark all unread messages from $senderId to $receiverId as read.
     */
    public function markAsRead(int $receiverId, int $senderId): void {
        $this->db->update(
            "UPDATE messages SET is_read = 1, read_at = NOW()
             WHERE receiver_id = ? AND sender_id = ? AND is_read = 0",
            [$receiverId, $senderId]
        );
    }

    /**
     * Update user online activity timestamp and typing indicator status.
     */
    public function updatePresence(int $userId, ?int $typingTargetId = null, bool $isTyping = false): void {
        $now = date('Y-m-d H:i:s');
        $target = $isTyping ? $typingTargetId : null;
        $typingTime = $isTyping ? $now : null;

        $this->db->query(
            "INSERT INTO user_presence (user_id, last_activity, typing_target_id, typing_updated_at)
             VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
               last_activity = VALUES(last_activity),
               typing_target_id = VALUES(typing_target_id),
               typing_updated_at = VALUES(typing_updated_at)",
            [$userId, $now, $target, $typingTime]
        );
    }

    /**
     * Get user online status and typing state.
     */
    public function getUserPresence(int $targetUserId, int $observerUserId): array {
        $presence = $this->db->fetchOne(
            "SELECT * FROM user_presence WHERE user_id = ?",
            [$targetUserId]
        );

        if (!$presence) {
            return [
                'is_online' => false,
                'last_seen' => 'Offline',
                'is_typing' => false,
            ];
        }

        $lastActivity = strtotime($presence['last_activity']);
        $isOnline     = (time() - $lastActivity) <= 60; // Active within last 60 seconds

        $isTyping = false;
        if (!empty($presence['typing_target_id']) && (int)$presence['typing_target_id'] === $observerUserId) {
            $typingTime = strtotime($presence['typing_updated_at'] ?? '1970-01-01');
            $isTyping   = (time() - $typingTime) <= 5; // Typing updated within last 5 seconds
        }

        return [
            'is_online' => $isOnline,
            'last_seen' => $isOnline ? 'Online' : 'Last seen ' . timeAgo($presence['last_activity']),
            'is_typing' => $isTyping,
        ];
    }

    /**
     * Get total unread count for current user (used for navbar/sidebar badges).
     */
    public function getTotalUnreadCount(int $userId): int {
        return (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = 0",
            [$userId]
        );
    }

    /**
     * Fetch single message for file download verification.
     */
    public function getMessageById(int $messageId): ?array {
        return $this->db->fetchOne("SELECT * FROM messages WHERE id = ?", [$messageId]);
    }
}
