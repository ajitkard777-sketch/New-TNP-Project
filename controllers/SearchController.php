<?php
/**
 * TPMS - Search Controller
 */
class SearchController {
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function globalSearch(): void {
        $query = sanitize($_GET['q'] ?? '');
        if (empty($query) || strlen($query) < 2) {
            if (isAjax()) { jsonResponse(['success' => true, 'results' => []]); }
            return;
        }

        $results = [];
        $role = $_SESSION['user_role'] ?? '';
        $searchParam = "%{$query}%";

        // Search Jobs (all roles)
        $jobs = $this->db->fetchAll(
            "SELECT j.id, j.title, c.company_name, j.location, 'job' as type FROM jobs j JOIN companies c ON j.company_id = c.id WHERE j.status = 'active' AND (j.title LIKE ? OR c.company_name LIKE ? OR j.skills_required LIKE ?) LIMIT 5",
            [$searchParam, $searchParam, $searchParam]
        );
        foreach ($jobs as $j) {
            $results[] = [
                'type' => 'job',
                'icon' => 'fas fa-briefcase',
                'title' => $j['title'],
                'subtitle' => $j['company_name'] . ' • ' . ($j['location'] ?? ''),
                'url' => url('/student/jobs'),
            ];
        }

        // Admin can search students
        if ($role === 'admin') {
            $students = $this->db->fetchAll(
                "SELECT s.id, s.first_name, s.last_name, s.branch, u.email FROM students s JOIN users u ON s.user_id = u.id WHERE s.first_name LIKE ? OR s.last_name LIKE ? OR u.email LIKE ? OR s.enrollment_no LIKE ? LIMIT 5",
                [$searchParam, $searchParam, $searchParam, $searchParam]
            );
            foreach ($students as $s) {
                $results[] = [
                    'type' => 'student',
                    'icon' => 'fas fa-user-graduate',
                    'title' => $s['first_name'] . ' ' . $s['last_name'],
                    'subtitle' => $s['branch'] . ' • ' . $s['email'],
                    'url' => url('/admin/view-student/' . $s['id']),
                ];
            }

            $companies = $this->db->fetchAll(
                "SELECT c.id, c.company_name, c.industry, u.email FROM companies c JOIN users u ON c.user_id = u.id WHERE c.company_name LIKE ? OR u.email LIKE ? LIMIT 5",
                [$searchParam, $searchParam]
            );
            foreach ($companies as $c) {
                $results[] = [
                    'type' => 'company',
                    'icon' => 'fas fa-building',
                    'title' => $c['company_name'],
                    'subtitle' => ($c['industry'] ?? '') . ' • ' . $c['email'],
                    'url' => url('/admin/companies'),
                ];
            }
        }

        if (isAjax()) {
            jsonResponse(['success' => true, 'results' => $results]);
        }
    }
}
