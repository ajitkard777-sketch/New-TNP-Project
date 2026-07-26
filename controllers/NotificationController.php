<?php
/**
 * TPMS - Notification Controller (AJAX & Redirects)
 */
class NotificationController {
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Fetch unread notifications for navbar dropdown
     */
    public function fetchUnread(): void {
        $userId = $_SESSION['user_id'] ?? 0;
        $notifications = $this->db->fetchAll(
            "SELECT * FROM notifications WHERE (user_id = ? OR is_global = 1) AND is_read = 0 ORDER BY created_at DESC LIMIT 10",
            [$userId]
        );
        foreach ($notifications as &$n) {
            $n['time_ago'] = timeAgo($n['created_at']);
        }
        jsonResponse(['success' => true, 'notifications' => $notifications]);
    }

    /**
     * Mark single notification as read
     */
    public function markRead($id): void {
        $userId = $_SESSION['user_id'] ?? 0;
        $id = (int)$id;
        if ($id > 0) {
            $this->db->update(
                "UPDATE notifications SET is_read = 1 WHERE id = ? AND (user_id = ? OR is_global = 1)",
                [$id, $userId]
            );
        }
        $unreadCount = (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM notifications WHERE (user_id = ? OR is_global = 1) AND is_read = 0",
            [$userId]
        );
        jsonResponse(['success' => true, 'count' => $unreadCount, 'message' => 'Notification marked as read']);
    }

    /**
     * Mark all notifications as read for current user
     */
    public function markAllRead(): void {
        $userId = $_SESSION['user_id'] ?? 0;
        $this->db->update(
            "UPDATE notifications SET is_read = 1 WHERE (user_id = ? OR is_global = 1) AND is_read = 0",
            [$userId]
        );
        jsonResponse(['success' => true, 'count' => 0, 'message' => 'All notifications marked as read']);
    }

    /**
     * Get unread notification count
     */
    public function getUnreadCount(): void {
        $userId = $_SESSION['user_id'] ?? 0;
        $count = (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM notifications WHERE (user_id = ? OR is_global = 1) AND is_read = 0",
            [$userId]
        );
        jsonResponse(['success' => true, 'count' => $count]);
    }

    /**
     * Mark notification as read and redirect to target page
     */
    public function readAndRedirect($id): void {
        $userId = $_SESSION['user_id'] ?? 0;
        $role   = $_SESSION['user_role'] ?? $_SESSION['role'] ?? 'student';
        $id     = (int)$id;
        
        $notif = $this->db->fetchOne(
            "SELECT * FROM notifications WHERE id = ? AND (user_id = ? OR is_global = 1)",
            [$id, $userId]
        );
        if ($notif) {
            $this->db->update("UPDATE notifications SET is_read = 1 WHERE id = ?", [$id]);
            
            $link = $notif['link'] ?? null;
            if (empty($link)) {
                $category = strtolower($notif['category'] ?? '');
                $title    = strtolower($notif['title'] ?? '');
                
                if ($category === 'job' || str_contains($title, 'job') || str_contains($title, 'drive') || str_contains($title, 'opening') || str_contains($title, 'post')) {
                    $link = "/{$role}/jobs";
                } elseif ($category === 'interview' || str_contains($title, 'interview') || str_contains($title, 'schedule')) {
                    $link = "/{$role}/interviews";
                } elseif ($category === 'placement' || str_contains($title, 'application') || str_contains($title, 'shortlist') || str_contains($title, 'status') || str_contains($title, 'select')) {
                    $link = ($role === 'student') ? '/student/applications' : (($role === 'company') ? '/company/jobs' : '/admin/placements');
                } elseif ($category === 'training' || str_contains($title, 'training') || str_contains($title, 'workshop') || str_contains($title, 'course')) {
                    $link = ($role === 'student') ? '/student/trainings' : (($role === 'admin') ? '/admin/trainings' : '/company/dashboard');
                } elseif (str_contains($title, 'message') || str_contains($title, 'chat') || str_contains($title, 'hr')) {
                    $link = in_array($role, ['student', 'company']) ? "/{$role}/messages" : "/admin/dashboard";
                } else {
                    $link = "/{$role}/notifications";
                }
            }
            redirect($link);
        } else {
            redirect("/{$role}/notifications");
        }
    }

    /**
     * Search notifications with title, message, company name, and type/category filters
     */
    public function search(): void {
        $userId = $_SESSION['user_id'] ?? 0;
        $query  = trim($_GET['q'] ?? '');
        $category = trim($_GET['category'] ?? '');

        $sql = "SELECT * FROM notifications WHERE (user_id = ? OR is_global = 1)";
        $params = [$userId];

        if (!empty($query)) {
            $sql .= " AND (LOWER(title) LIKE ? OR LOWER(message) LIKE ? OR LOWER(IFNULL(company_name, '')) LIKE ? OR LOWER(IFNULL(type, '')) LIKE ? OR LOWER(IFNULL(category, '')) LIKE ?)";
            $searchTerm = '%' . strtolower($query) . '%';
            array_push($params, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
        }

        if (!empty($category) && $category !== 'all') {
            $sql .= " AND (LOWER(category) = ? OR LOWER(type) = ?)";
            $catLower = strtolower($category);
            array_push($params, $catLower, $catLower);
        }

        $sql .= " ORDER BY created_at DESC LIMIT 100";

        $notifications = $this->db->fetchAll($sql, $params);
        foreach ($notifications as &$n) {
            $n['time_ago'] = timeAgo($n['created_at']);
        }

        jsonResponse(['success' => true, 'notifications' => $notifications, 'count' => count($notifications)]);
    }
}

