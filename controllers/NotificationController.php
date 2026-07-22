<?php
/**
 * TPMS - Notification Controller (AJAX)
 */
class NotificationController {
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function fetchUnread(): void {
        $userId = $_SESSION['user_id'];
        $notifications = $this->db->fetchAll(
            "SELECT * FROM notifications WHERE (user_id = ? OR is_global = 1) AND is_read = 0 ORDER BY created_at DESC LIMIT 10",
            [$userId]
        );
        jsonResponse(['success' => true, 'notifications' => $notifications]);
    }

    public function markRead($id): void {
        $this->db->update("UPDATE notifications SET is_read = 1 WHERE id = ? AND (user_id = ? OR is_global = 1)", [$id, $_SESSION['user_id']]);
        jsonResponse(['success' => true]);
    }

    public function markAllRead(): void {
        $this->db->update("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0", [$_SESSION['user_id']]);
        jsonResponse(['success' => true, 'message' => 'All notifications marked as read']);
    }

    public function getUnreadCount(): void {
        $count = (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM notifications WHERE (user_id = ? OR is_global = 1) AND is_read = 0",
            [$_SESSION['user_id']]
        );
        jsonResponse(['success' => true, 'count' => $count]);
    }
}
