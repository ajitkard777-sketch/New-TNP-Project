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

        // Add time_ago and ensure link field exists
        foreach ($notifications as &$n) {
            $n['time_ago'] = $this->timeAgo($n['created_at'] ?? null);
            $n['link'] = $n['link'] ?? $this->buildNotificationLink($n);
        }
        unset($n);

        $count = count($notifications);

        jsonResponse([
            'success'       => true,
            'count'         => $count,
            'notifications' => $notifications
        ]);
    }

    public function markRead($id): void {
        $this->db->update(
            "UPDATE notifications SET is_read = 1 WHERE id = ? AND (user_id = ? OR is_global = 1)",
            [$id, $_SESSION['user_id']]
        );
        jsonResponse(['success' => true]);
    }

    public function markAllRead(): void {
        $this->db->update(
            "UPDATE notifications SET is_read = 1 WHERE (user_id = ? OR is_global = 1) AND is_read = 0",
            [$_SESSION['user_id']]
        );
        jsonResponse(['success' => true, 'message' => 'All notifications marked as read']);
    }

    public function getUnreadCount(): void {
        $count = (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM notifications WHERE (user_id = ? OR is_global = 1) AND is_read = 0",
            [$_SESSION['user_id']]
        );
        jsonResponse(['success' => true, 'count' => $count]);
    }

    /**
     * Build a relevant link based on notification category/type
     */
    private function buildNotificationLink(array $n): string {
        $role = $_SESSION['user_role'] ?? 'student';
        $category = $n['category'] ?? '';

        $linkMap = [
            'job'        => "/{$role}/jobs",
            'interview'  => "/{$role}/interviews",
            'placement'  => "/{$role}/profile",
            'training'   => "/{$role}/trainings",
            'system'     => "/{$role}/dashboard",
            'approval'   => $role === 'admin' ? '/admin/approvals' : "/{$role}/dashboard",
        ];

        $path = $linkMap[$category] ?? "/{$role}/notifications";

        return defined('BASE_URL') ? BASE_URL . $path : $path;
    }

    /**
     * Human-readable time ago
     */
    private function timeAgo(?string $datetime): string {
        if (!$datetime) return 'just now';
        $time = strtotime($datetime);
        $diff = time() - $time;

        if ($diff < 60)         return 'just now';
        if ($diff < 3600)       return floor($diff / 60) . 'm ago';
        if ($diff < 86400)      return floor($diff / 3600) . 'h ago';
        if ($diff < 2592000)    return floor($diff / 86400) . 'd ago';
        return date('M j', $time);
    }
}
