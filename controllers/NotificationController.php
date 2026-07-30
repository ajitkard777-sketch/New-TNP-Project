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
        $role   = $_SESSION['user_role'] ?? 'student';

        $notifications = $this->db->fetchAll(
            "SELECT * FROM notifications WHERE (user_id = ? OR is_global = 1) AND is_read = 0 ORDER BY created_at DESC LIMIT 10",
            [$userId]
        );

        foreach ($notifications as &$n) {
            $n['time_ago'] = $this->timeAgo($n['created_at'] ?? null);
            // Prefer stored link, then build dynamically
            $n['link'] = (!empty($n['link'])) ? $n['link'] : $this->buildNotificationLink($n, $role);
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
     * Build a contextual navigation link based on category and reference_id
     */
    private function buildNotificationLink(array $n, string $role): string {
        $category    = $n['category'] ?? 'system';
        $referenceId = (int)($n['reference_id'] ?? 0);

        $baseUrl = defined('BASE_URL') ? BASE_URL : '';

        switch ($category) {
            case 'job':
                // Company: see applicants for their job  |  Student: browse jobs
                if ($role === 'company') {
                    return $referenceId
                        ? $baseUrl . "/company/applications/{$referenceId}"
                        : $baseUrl . "/company/jobs";
                }
                return $baseUrl . "/student/jobs";

            case 'interview':
                // All roles have an interviews page
                if ($role === 'admin') {
                    return $baseUrl . "/admin/interviews";
                }
                if ($role === 'company') {
                    return $baseUrl . "/company/interviews";
                }
                return $baseUrl . "/student/interviews";

            case 'placement':
                return $role === 'admin'
                    ? $baseUrl . "/admin/placements"
                    : $baseUrl . "/student/profile";

            case 'training':
                return $role === 'admin'
                    ? $baseUrl . "/admin/trainings"
                    : $baseUrl . "/student/trainings";

            case 'higher-studies':
                return $role === 'admin'
                    ? $baseUrl . "/admin/higher-studies"
                    : $baseUrl . "/student/higher-studies";

            case 'approval':
                return $role === 'admin'
                    ? $baseUrl . "/admin/approvals"
                    : $baseUrl . "/{$role}/dashboard";

            case 'announcement':
                return $baseUrl . "/{$role}/notifications";

            default: // 'system' and anything else
                return $baseUrl . "/{$role}/notifications";
        }
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
