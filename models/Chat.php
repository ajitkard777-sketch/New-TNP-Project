<?php
/**
 * TPMS - Chat Model
 * Handles all database operations for the real-time chat system.
 */

class Chat {

    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Get allowed contact list for a user based on role-based permission rules.
     */
    public function getAllowedContacts(int $userId, string $role): array {
        $contacts = [];

        if ($role === 'student') {
            // Student: Get student ID
            $student = $this->db->fetchOne("SELECT id FROM students WHERE user_id = ?", [$userId]);
            $studentId = $student['id'] ?? 0;

            // 1. Companies where student has applied to at least 1 job
            $companies = $this->db->fetchAll(
                "SELECT DISTINCT u.id as user_id, c.company_name as name, c.logo as photo, 'company' as role,
                        c.industry as detail, 'Company' as role_label
                 FROM applications a
                 JOIN jobs j ON a.job_id = j.id
                 JOIN companies c ON j.company_id = c.id
                 JOIN users u ON c.user_id = u.id
                 WHERE a.student_id = ? AND u.status = 'active'
                 ORDER BY c.company_name ASC",
                [$studentId]
            );
            foreach ($companies as $c) {
                $contacts[$c['user_id']] = $c;
            }

            // 2. Admin users (Placement Officers)
            $admins = $this->db->fetchAll(
                "SELECT u.id as user_id, u.email as name, NULL as photo, 'admin' as role,
                        'Placement Cell' as detail, 'Placement Officer' as role_label
                 FROM users u
                 WHERE u.role = 'admin' AND u.status = 'active' AND u.id != ?
                 ORDER BY u.id ASC",
                [$userId]
            );
            foreach ($admins as $ad) {
                if (!isset($contacts[$ad['user_id']])) {
                    $contacts[$ad['user_id']] = $ad;
                }
            }

        } elseif ($role === 'company') {
            // Company: Get company ID
            $company = $this->db->fetchOne("SELECT id FROM companies WHERE user_id = ?", [$userId]);
            $companyId = $company['id'] ?? 0;

            // 1. Students who have applied for or were recommended for this company's jobs
            $candidates = $this->db->fetchAll(
                "SELECT DISTINCT u.id as user_id, CONCAT(s.first_name, ' ', s.last_name) as name,
                        s.profile_photo as photo, 'student' as role,
                        CONCAT(COALESCE(s.branch, 'Student'), ' • CGPA ', COALESCE(s.cgpa, 'N/A')) as detail,
                        CASE WHEN a.id IS NOT NULL THEN 'Applicant' ELSE 'Recommended Candidate' END as role_label
                 FROM students s
                 JOIN users u ON s.user_id = u.id
                 LEFT JOIN applications a ON a.student_id = s.id
                 LEFT JOIN jobs j1 ON a.job_id = j1.id AND j1.company_id = ?
                 LEFT JOIN job_recommendations jr ON jr.student_id = s.id
                 LEFT JOIN jobs j2 ON jr.job_id = j2.id AND j2.company_id = ?
                 WHERE (j1.id IS NOT NULL OR j2.id IS NOT NULL) AND u.status = 'active'
                 ORDER BY s.first_name ASC",
                [$companyId, $companyId]
            );
            foreach ($candidates as $cand) {
                $contacts[$cand['user_id']] = $cand;
            }

            // 2. Admin users (Placement Officers)
            $admins = $this->db->fetchAll(
                "SELECT u.id as user_id, u.email as name, NULL as photo, 'admin' as role,
                        'Placement Cell' as detail, 'Placement Officer' as role_label
                 FROM users u
                 WHERE u.role = 'admin' AND u.status = 'active' AND u.id != ?
                 ORDER BY u.id ASC",
                [$userId]
            );
            foreach ($admins as $ad) {
                if (!isset($contacts[$ad['user_id']])) {
                    $contacts[$ad['user_id']] = $ad;
                }
            }

        } elseif ($role === 'admin') {
            // Admin: Can chat with all active students & companies
            $students = $this->db->fetchAll(
                "SELECT u.id as user_id, CONCAT(s.first_name, ' ', s.last_name) as name,
                        s.profile_photo as photo, 'student' as role,
                        CONCAT(COALESCE(s.branch, 'Student'), ' • CGPA ', COALESCE(s.cgpa, 'N/A')) as detail,
                        'Student' as role_label
                 FROM students s
                 JOIN users u ON s.user_id = u.id
                 WHERE u.status = 'active'
                 ORDER BY s.first_name ASC LIMIT 100"
            );
            foreach ($students as $st) {
                $contacts[$st['user_id']] = $st;
            }

            $companies = $this->db->fetchAll(
                "SELECT u.id as user_id, c.company_name as name, c.logo as photo, 'company' as role,
                        c.industry as detail, 'Company' as role_label
                 FROM companies c
                 JOIN users u ON c.user_id = u.id
                 WHERE u.status = 'active'
                 ORDER BY c.company_name ASC LIMIT 100"
            );
            foreach ($companies as $cp) {
                $contacts[$cp['user_id']] = $cp;
            }
        }

        return array_values($contacts);
    }

    /**
     * Verify if user A is allowed to chat with user B based on permissions.
     */
    public function isChatAllowed(int $userAId, string $roleA, int $userBId): bool {
        if ($userAId === $userBId) return false;
        if ($roleA === 'admin') return true;

        $allowed = $this->getAllowedContacts($userAId, $roleA);
        foreach ($allowed as $c) {
            if ((int)$c['user_id'] === $userBId) {
                return true;
            }
        }

        // Check if there is an existing active conversation
        $conv = $this->getConversationBetween($userAId, $userBId);
        return $conv !== null;
    }

    /**
     * Find or create conversation between two users.
     */
    public function getOrCreateConversation(int $userOneId, int $userTwoId): array {
        $conv = $this->getConversationBetween($userOneId, $userTwoId);
        if ($conv) {
            return $conv;
        }

        // Normalize order so user_one_id < user_two_id
        $first  = min($userOneId, $userTwoId);
        $second = max($userOneId, $userTwoId);

        $id = $this->db->insert(
            "INSERT INTO chat_conversations (user_one_id, user_two_id, created_at) VALUES (?, ?, NOW())",
            [$first, $second]
        );

        return $this->db->fetchOne("SELECT * FROM chat_conversations WHERE id = ?", [$id]);
    }

    /**
     * Get conversation between two users.
     */
    public function getConversationBetween(int $userOneId, int $userTwoId): ?array {
        $first  = min($userOneId, $userTwoId);
        $second = max($userOneId, $userTwoId);

        $row = $this->db->fetchOne(
            "SELECT * FROM chat_conversations WHERE user_one_id = ? AND user_two_id = ?",
            [$first, $second]
        );
        return $row ?: null;
    }

    /**
     * Get all active conversations for a user with recipient details, unread counts, and last message.
     */
    public function getUserConversations(int $userId): array {
        $rows = $this->db->fetchAll(
            "SELECT c.*,
                    CASE WHEN c.user_one_id = ? THEN c.user_two_id ELSE c.user_one_id END as other_user_id
             FROM chat_conversations c
             WHERE (c.user_one_id = ? OR c.user_two_id = ?)
             ORDER BY c.last_message_at DESC, c.id DESC",
            [$userId, $userId, $userId]
        );

        $conversations = [];
        foreach ($rows as $row) {
            // Check soft delete
            $deletedBy = json_decode($row['deleted_by'] ?? '[]', true) ?: [];
            if (in_array($userId, $deletedBy)) {
                continue;
            }

            $otherUserId = (int)$row['other_user_id'];
            $userInfo    = $this->getUserDisplayInfo($otherUserId);

            // Unread count for current user
            $unreadCount = (int)$this->db->fetchColumn(
                "SELECT COUNT(*) FROM chat_messages
                 WHERE conversation_id = ? AND receiver_id = ? AND is_read = 0 AND is_deleted = 0",
                [$row['id'], $userId]
            );

            // Last message snippet
            $lastMsg = null;
            if ($row['last_message_id']) {
                $lastMsg = $this->db->fetchOne(
                    "SELECT id, sender_id, message, is_read, created_at FROM chat_messages WHERE id = ?",
                    [$row['last_message_id']]
                );
            }

            // Check typing & online presence
            $presence = $this->db->fetchOne(
                "SELECT last_active_at, typing_to_user_id, typing_updated_at FROM chat_presence WHERE user_id = ?",
                [$otherUserId]
            );

            $isOnline = false;
            $isTyping = false;
            if ($presence) {
                if ($presence['last_active_at'] && (time() - strtotime($presence['last_active_at'])) < 25) {
                    $isOnline = true;
                }
                if ((int)($presence['typing_to_user_id'] ?? 0) === $userId && $presence['typing_updated_at'] && (time() - strtotime($presence['typing_updated_at'])) < 6) {
                    $isTyping = true;
                }
            }

            $archivedBy = json_decode($row['archived_by'] ?? '[]', true) ?: [];

            $conversations[] = [
                'id'              => (int)$row['id'],
                'other_user_id'   => $otherUserId,
                'name'            => $userInfo['name'],
                'photo'           => $userInfo['photo'],
                'role'            => $userInfo['role'],
                'role_label'      => $userInfo['role_label'],
                'detail'          => $userInfo['detail'],
                'last_message'    => $lastMsg ? $lastMsg['message'] : '',
                'last_sender_id'  => $lastMsg ? (int)$lastMsg['sender_id'] : 0,
                'last_time'       => $row['last_message_at'],
                'unread_count'    => $unreadCount,
                'is_online'       => $isOnline,
                'is_typing'       => $isTyping,
                'is_archived'     => in_array($userId, $archivedBy),
            ];
        }

        return $conversations;
    }

    /**
     * Get display info for a user ID (name, photo, role).
     */
    public function getUserDisplayInfo(int $userId): array {
        $u = $this->db->fetchOne("SELECT id, email, role FROM users WHERE id = ?", [$userId]);
        if (!$u) {
            return ['name' => 'Unknown User', 'photo' => null, 'role' => 'user', 'role_label' => 'User', 'detail' => ''];
        }

        if ($u['role'] === 'student') {
            $s = $this->db->fetchOne("SELECT first_name, last_name, profile_photo, branch, cgpa FROM students WHERE user_id = ?", [$userId]);
            if ($s) {
                $photo = $s['profile_photo'] ? uploadUrl('profile_photos/' . $s['profile_photo']) : asset('images/default-avatar.png');
                return [
                    'name'       => $s['first_name'] . ' ' . $s['last_name'],
                    'photo'      => $photo,
                    'role'       => 'student',
                    'role_label' => 'Student',
                    'detail'     => ($s['branch'] ?? 'Student') . ($s['cgpa'] ? ' • CGPA ' . $s['cgpa'] : ''),
                ];
            }
        } elseif ($u['role'] === 'company') {
            $c = $this->db->fetchOne("SELECT company_name, logo, industry FROM companies WHERE user_id = ?", [$userId]);
            if ($c) {
                $photo = $c['logo'] ? uploadUrl('company/' . $c['logo']) : asset('images/default-avatar.png');
                return [
                    'name'       => $c['company_name'],
                    'photo'      => $photo,
                    'role'       => 'company',
                    'role_label' => 'Company',
                    'detail'     => $c['industry'] ?? 'Company',
                ];
            }
        }

        return [
            'name'       => $u['email'],
            'photo'      => asset('images/default-avatar.png'),
            'role'       => 'admin',
            'role_label' => 'Placement Officer',
            'detail'     => 'Placement Cell',
        ];
    }

    /**
     * Get message history for a conversation. Automatically marks received unread messages as read.
     */
    public function getMessages(int $conversationId, int $currentUserId): array {
        // Mark unread as read
        $this->db->query(
            "UPDATE chat_messages SET is_read = 1, read_at = NOW()
             WHERE conversation_id = ? AND receiver_id = ? AND is_read = 0",
            [$conversationId, $currentUserId]
        );


        $rows = $this->db->fetchAll(
            "SELECT m.*,
                    a.id as attachment_id, a.file_name, a.file_path, a.file_type, a.file_size
             FROM chat_messages m
             LEFT JOIN chat_attachments a ON a.message_id = m.id
             WHERE m.conversation_id = ? AND m.is_deleted = 0
             ORDER BY m.created_at ASC",
            [$conversationId]
        );

        $messages = [];
        foreach ($rows as $r) {
            $msgId = (int)$r['id'];
            if (!isset($messages[$msgId])) {
                $messages[$msgId] = [
                    'id'              => $msgId,
                    'conversation_id' => (int)$r['conversation_id'],
                    'sender_id'       => (int)$r['sender_id'],
                    'receiver_id'     => (int)$r['receiver_id'],
                    'message'         => $r['message'],
                    'is_read'         => (bool)$r['is_read'],
                    'read_at'         => $r['read_at'],
                    'created_at'      => $r['created_at'],
                    'time_formatted'  => date('h:i A', strtotime($r['created_at'])),
                    'date_formatted'  => date('d M Y', strtotime($r['created_at'])),
                    'attachments'     => [],
                ];
            }

            if ($r['attachment_id']) {
                $messages[$msgId]['attachments'][] = [
                    'id'        => (int)$r['attachment_id'],
                    'file_name' => $r['file_name'],
                    'file_path' => uploadUrl('chat/' . $r['file_path']),
                    'file_type' => $r['file_type'],
                    'file_size' => $r['file_size'],
                    'size_fmt'  => $this->formatFileSize((int)$r['file_size']),
                ];
            }
        }

        return array_values($messages);
    }

    /**
     * Send a new message.
     */
    public function sendMessage(int $conversationId, int $senderId, int $receiverId, ?string $message, ?array $attachment = null): array {
        // Un-delete conversation for both users
        $conv = $this->db->fetchOne("SELECT deleted_by FROM chat_conversations WHERE id = ?", [$conversationId]);
        if ($conv) {
            $deletedBy = json_decode($conv['deleted_by'] ?? '[]', true) ?: [];
            if (!empty($deletedBy)) {
                $this->db->query("UPDATE chat_conversations SET deleted_by = NULL WHERE id = ?", [$conversationId]);
            }
        }


        $msgId = $this->db->insert(
            "INSERT INTO chat_messages (conversation_id, sender_id, receiver_id, message, created_at) VALUES (?, ?, ?, ?, NOW())",
            [$conversationId, $senderId, $receiverId, $message]
        );

        // Attachment save
        $attachmentData = null;
        if ($attachment && !empty($attachment['file_name']) && !empty($attachment['file_path'])) {
            $attId = $this->db->insert(
                "INSERT INTO chat_attachments (message_id, file_name, file_path, file_type, file_size, created_at) VALUES (?, ?, ?, ?, ?, NOW())",
                [$msgId, $attachment['file_name'], $attachment['file_path'], $attachment['file_type'], $attachment['file_size']]
            );

            $attachmentData = [
                'id'        => $attId,
                'file_name' => $attachment['file_name'],
                'file_path' => uploadUrl('chat/' . $attachment['file_path']),
                'file_type' => $attachment['file_type'],
                'file_size' => $attachment['file_size'],
                'size_fmt'  => $this->formatFileSize((int)$attachment['file_size']),
            ];
        }

        // Update conversation last message pointer & timestamp
        $this->db->query(
            "UPDATE chat_conversations SET last_message_id = ?, last_message_at = NOW() WHERE id = ?",
            [$msgId, $conversationId]
        );

        $msg = $this->db->fetchOne("SELECT * FROM chat_messages WHERE id = ?", [$msgId]);

        return [
            'id'              => (int)$msg['id'],
            'conversation_id' => (int)$msg['conversation_id'],
            'sender_id'       => (int)$msg['sender_id'],
            'receiver_id'     => (int)$msg['receiver_id'],
            'message'         => $msg['message'],
            'is_read'         => false,
            'created_at'      => $msg['created_at'],
            'time_formatted'  => date('h:i A', strtotime($msg['created_at'])),
            'attachments'     => $attachmentData ? [$attachmentData] : [],
        ];
    }

    /**
     * Update user presence / online active timestamp and typing state.
     */
    public function updatePresence(int $userId, ?int $typingToUserId = null): void {
        $now = date('Y-m-d H:i:s');
        $this->db->query(
            "INSERT INTO chat_presence (user_id, last_active_at, typing_to_user_id, typing_updated_at)
             VALUES (?, NOW(), ?, ?)
             ON DUPLICATE KEY UPDATE last_active_at = NOW(), typing_to_user_id = VALUES(typing_to_user_id), typing_updated_at = VALUES(typing_updated_at)",
            [$userId, $typingToUserId, $typingToUserId ? $now : null]
        );
    }

    /**
     * Soft delete conversation for a user.
     */
    public function deleteConversation(int $conversationId, int $userId): void {
        $conv = $this->db->fetchOne("SELECT deleted_by FROM chat_conversations WHERE id = ?", [$conversationId]);
        if (!$conv) return;

        $deletedBy = json_decode($conv['deleted_by'] ?? '[]', true) ?: [];
        if (!in_array($userId, $deletedBy)) {
            $deletedBy[] = $userId;
            $this->db->query(
                "UPDATE chat_conversations SET deleted_by = ? WHERE id = ?",
                [json_encode($deletedBy), $conversationId]
            );
        }
    }

    /**
     * Archive or unarchive conversation for a user.
     */
    public function toggleArchiveConversation(int $conversationId, int $userId): bool {
        $conv = $this->db->fetchOne("SELECT archived_by FROM chat_conversations WHERE id = ?", [$conversationId]);
        if (!$conv) return false;

        $archivedBy = json_decode($conv['archived_by'] ?? '[]', true) ?: [];
        if (in_array($userId, $archivedBy)) {
            $archivedBy = array_values(array_diff($archivedBy, [$userId]));
            $isArchived = false;
        } else {
            $archivedBy[] = $userId;
            $isArchived = true;
        }

        $this->db->query(
            "UPDATE chat_conversations SET archived_by = ? WHERE id = ?",
            [json_encode($archivedBy), $conversationId]
        );


        return $isArchived;
    }

    /**
     * Get total unread chat message count for a user (for navbar badge).
     */
    public function getTotalUnreadCount(int $userId): int {
        return (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM chat_messages WHERE receiver_id = ? AND is_read = 0 AND is_deleted = 0",
            [$userId]
        );
    }

    /**
     * Format bytes into human readable KB/MB string.
     */
    private function formatFileSize(int $bytes): string {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 0) . ' KB';
        }
        return $bytes . ' B';
    }
}
