<?php
/**
 * TPMS - Search Controller
 * Handles global search for Students, Companies, Jobs, Applications, Interviews, Trainings, Higher Studies, and Notifications.
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
        $userId = $_SESSION['user_id'] ?? 0;
        $searchParam = "%{$query}%";

        if ($role === 'admin') {
            // 1. Students
            $students = $this->db->fetchAll(
                "SELECT s.id, s.first_name, s.last_name, s.branch, s.enrollment_no, u.email 
                 FROM students s JOIN users u ON s.user_id = u.id 
                 WHERE s.first_name LIKE ? OR s.last_name LIKE ? OR u.email LIKE ? OR s.enrollment_no LIKE ? OR s.branch LIKE ?
                 LIMIT 4",
                [$searchParam, $searchParam, $searchParam, $searchParam, $searchParam]
            );
            foreach ($students as $s) {
                $results[] = [
                    'type' => 'Student',
                    'icon' => 'fas fa-user-graduate',
                    'color' => 'primary',
                    'title' => $s['first_name'] . ' ' . $s['last_name'],
                    'subtitle' => ($s['branch'] ?? 'Branch') . ' • ' . $s['email'],
                    'url' => url('/admin/view-student/' . $s['id']),
                ];
            }

            // 2. Companies
            $companies = $this->db->fetchAll(
                "SELECT c.id, c.company_name, c.industry, u.email 
                 FROM companies c JOIN users u ON c.user_id = u.id 
                 WHERE c.company_name LIKE ? OR c.industry LIKE ? OR u.email LIKE ?
                 LIMIT 4",
                [$searchParam, $searchParam, $searchParam]
            );
            foreach ($companies as $c) {
                $results[] = [
                    'type' => 'Company',
                    'icon' => 'fas fa-building',
                    'color' => 'info',
                    'title' => $c['company_name'],
                    'subtitle' => ($c['industry'] ?? 'Industry') . ' • ' . $c['email'],
                    'url' => url('/admin/companies') . '?search=' . urlencode($c['company_name']),
                ];
            }

            // 3. Jobs
            $jobs = $this->db->fetchAll(
                "SELECT j.id, j.title, c.company_name, j.location 
                 FROM jobs j JOIN companies c ON j.company_id = c.id 
                 WHERE j.title LIKE ? OR c.company_name LIKE ? OR j.skills_required LIKE ?
                 LIMIT 4",
                [$searchParam, $searchParam, $searchParam]
            );
            foreach ($jobs as $j) {
                $results[] = [
                    'type' => 'Job',
                    'icon' => 'fas fa-briefcase',
                    'color' => 'success',
                    'title' => $j['title'],
                    'subtitle' => $j['company_name'] . ' • ' . ($j['location'] ?? 'Location'),
                    'url' => url('/admin/jobs') . '?search=' . urlencode($j['title']),
                ];
            }

            // 4. Higher Studies
            $higherStudies = $this->db->fetchAll(
                "SELECT hsa.id, s.first_name, s.last_name, hsa.career_option, hsa.preferred_course, hsa.preferred_university
                 FROM higher_study_applications hsa JOIN students s ON hsa.student_id = s.id
                 WHERE s.first_name LIKE ? OR s.last_name LIKE ? OR hsa.career_option LIKE ? OR hsa.preferred_course LIKE ? OR hsa.preferred_university LIKE ?
                 LIMIT 4",
                [$searchParam, $searchParam, $searchParam, $searchParam, $searchParam]
            );
            foreach ($higherStudies as $hs) {
                $results[] = [
                    'type' => 'Higher Studies',
                    'icon' => 'fas fa-graduation-cap',
                    'color' => 'teal',
                    'title' => 'Higher Studies: ' . $hs['first_name'] . ' ' . $hs['last_name'],
                    'subtitle' => ($hs['career_option'] ?? 'Application') . ' • ' . ($hs['preferred_course'] ?: ($hs['preferred_university'] ?: 'Higher Education')),
                    'url' => url('/admin/higher-studies'),
                ];
            }

            // 5. Trainings
            $trainings = $this->db->fetchAll(
                "SELECT t.id, t.title, t.trainer_name, t.mode, t.platform_name 
                 FROM trainings t 
                 WHERE t.title LIKE ? OR t.trainer_name LIKE ? OR t.platform_name LIKE ? OR t.training_type LIKE ?
                 LIMIT 4",
                [$searchParam, $searchParam, $searchParam, $searchParam]
            );
            foreach ($trainings as $t) {
                $results[] = [
                    'type' => 'Training',
                    'icon' => 'fas fa-chalkboard-teacher',
                    'color' => 'warning',
                    'title' => 'Training: ' . $t['title'],
                    'subtitle' => 'Trainer: ' . ($t['trainer_name'] ?? 'TBA') . ' • ' . ucfirst($t['mode']),
                    'url' => url('/admin/trainings'),
                ];
            }

            // 6. Interviews
            $interviews = $this->db->fetchAll(
                "SELECT i.id, j.title as job_title, c.company_name, s.first_name, s.last_name 
                 FROM interviews i JOIN jobs j ON i.job_id = j.id JOIN companies c ON i.company_id = c.id JOIN students s ON i.student_id = s.id 
                 WHERE s.first_name LIKE ? OR s.last_name LIKE ? OR j.title LIKE ? OR c.company_name LIKE ?
                 LIMIT 4",
                [$searchParam, $searchParam, $searchParam, $searchParam]
            );
            foreach ($interviews as $inv) {
                $results[] = [
                    'type' => 'Interview',
                    'icon' => 'fas fa-comments',
                    'color' => 'violet',
                    'title' => 'Interview: ' . $inv['first_name'] . ' ' . $inv['last_name'],
                    'subtitle' => $inv['job_title'] . ' • ' . $inv['company_name'],
                    'url' => url('/admin/interviews'),
                ];
            }

            // 7. Notifications
            $notifications = $this->db->fetchAll(
                "SELECT n.id, n.title, n.message FROM notifications n WHERE (n.user_id = ? OR n.is_global = 1) AND (n.title LIKE ? OR n.message LIKE ?) LIMIT 3",
                [$userId, $searchParam, $searchParam]
            );
            foreach ($notifications as $n) {
                $results[] = [
                    'type' => 'Notification',
                    'icon' => 'fas fa-bell',
                    'color' => 'danger',
                    'title' => 'Notification: ' . $n['title'],
                    'subtitle' => truncateText($n['message'], 60),
                    'url' => url('/admin/notifications'),
                ];
            }
        } elseif ($role === 'student') {
            $student = $this->db->fetchOne("SELECT id FROM students WHERE user_id = ?", [$userId]);
            $studentId = $student['id'] ?? 0;

            // 1. Jobs
            $jobs = $this->db->fetchAll(
                "SELECT j.id, j.title, c.company_name, j.location 
                 FROM jobs j JOIN companies c ON j.company_id = c.id 
                 WHERE j.status = 'active' AND (j.title LIKE ? OR c.company_name LIKE ? OR j.skills_required LIKE ?)
                 LIMIT 5",
                [$searchParam, $searchParam, $searchParam]
            );
            foreach ($jobs as $j) {
                $results[] = [
                    'type' => 'Job',
                    'icon' => 'fas fa-briefcase',
                    'color' => 'primary',
                    'title' => $j['title'],
                    'subtitle' => $j['company_name'] . ' • ' . ($j['location'] ?? 'Location'),
                    'url' => url('/student/jobs') . '?search=' . urlencode($j['title']),
                ];
            }

            // 2. Applications
            $applications = $this->db->fetchAll(
                "SELECT a.id, j.title as job_title, c.company_name, a.status 
                 FROM applications a JOIN jobs j ON a.job_id = j.id JOIN companies c ON j.company_id = c.id 
                 WHERE a.student_id = ? AND (j.title LIKE ? OR c.company_name LIKE ?)
                 LIMIT 4",
                [$studentId, $searchParam, $searchParam]
            );
            foreach ($applications as $app) {
                $results[] = [
                    'type' => 'Application',
                    'icon' => 'fas fa-file-alt',
                    'color' => 'info',
                    'title' => 'Application: ' . $app['job_title'],
                    'subtitle' => $app['company_name'] . ' • Status: ' . ucfirst($app['status']),
                    'url' => url('/student/applications'),
                ];
            }

            // 3. Trainings
            $trainings = $this->db->fetchAll(
                "SELECT t.id, t.title, t.trainer_name, t.mode 
                 FROM trainings t 
                 WHERE t.status IN ('upcoming', 'ongoing') AND (t.title LIKE ? OR t.trainer_name LIKE ? OR t.platform_name LIKE ?)
                 LIMIT 4",
                [$searchParam, $searchParam, $searchParam]
            );
            foreach ($trainings as $t) {
                $results[] = [
                    'type' => 'Training',
                    'icon' => 'fas fa-chalkboard-teacher',
                    'color' => 'warning',
                    'title' => 'Training: ' . $t['title'],
                    'subtitle' => 'Trainer: ' . ($t['trainer_name'] ?? 'TBA') . ' • ' . ucfirst($t['mode']),
                    'url' => url('/student/trainings'),
                ];
            }

            // 4. Higher Studies
            $higherStudies = $this->db->fetchAll(
                "SELECT hsa.id, hsa.career_option, hsa.preferred_course, hsa.preferred_university 
                 FROM higher_study_applications hsa 
                 WHERE hsa.student_id = ? AND (hsa.career_option LIKE ? OR hsa.preferred_course LIKE ? OR hsa.preferred_university LIKE ?)
                 LIMIT 3",
                [$studentId, $searchParam, $searchParam, $searchParam]
            );
            foreach ($higherStudies as $hs) {
                $results[] = [
                    'type' => 'Higher Studies',
                    'icon' => 'fas fa-graduation-cap',
                    'color' => 'success',
                    'title' => 'Higher Studies: ' . ($hs['career_option'] ?? 'Application'),
                    'subtitle' => ($hs['preferred_course'] ?: 'Course') . ' • ' . ($hs['preferred_university'] ?: 'University'),
                    'url' => url('/student/higher-studies'),
                ];
            }

            // 5. Interviews
            $interviews = $this->db->fetchAll(
                "SELECT i.id, j.title as job_title, c.company_name, i.round 
                 FROM interviews i JOIN jobs j ON i.job_id = j.id JOIN companies c ON i.company_id = c.id 
                 WHERE i.student_id = ? AND (j.title LIKE ? OR c.company_name LIKE ?)
                 LIMIT 3",
                [$studentId, $searchParam, $searchParam]
            );
            foreach ($interviews as $inv) {
                $results[] = [
                    'type' => 'Interview',
                    'icon' => 'fas fa-comments',
                    'color' => 'violet',
                    'title' => 'Interview: ' . $inv['job_title'],
                    'subtitle' => $inv['company_name'] . ' • ' . ($inv['round'] ?? 'Scheduled'),
                    'url' => url('/student/interviews'),
                ];
            }

            // 6. Notifications
            $notifications = $this->db->fetchAll(
                "SELECT n.id, n.title, n.message FROM notifications n WHERE (n.user_id = ? OR n.is_global = 1) AND (n.title LIKE ? OR n.message LIKE ?) LIMIT 3",
                [$userId, $searchParam, $searchParam]
            );
            foreach ($notifications as $n) {
                $results[] = [
                    'type' => 'Notification',
                    'icon' => 'fas fa-bell',
                    'color' => 'danger',
                    'title' => 'Notification: ' . $n['title'],
                    'subtitle' => truncateText($n['message'], 60),
                    'url' => url('/student/notifications'),
                ];
            }
        } elseif ($role === 'company') {
            $company = $this->db->fetchOne("SELECT id FROM companies WHERE user_id = ?", [$userId]);
            $companyId = $company['id'] ?? 0;

            // 1. Company Jobs
            $jobs = $this->db->fetchAll(
                "SELECT j.id, j.title, j.location, j.status FROM jobs j WHERE j.company_id = ? AND (j.title LIKE ? OR j.skills_required LIKE ?) LIMIT 5",
                [$companyId, $searchParam, $searchParam]
            );
            foreach ($jobs as $j) {
                $results[] = [
                    'type' => 'Job',
                    'icon' => 'fas fa-briefcase',
                    'color' => 'primary',
                    'title' => $j['title'],
                    'subtitle' => 'Job • ' . ($j['location'] ?? 'Location') . ' • Status: ' . ucfirst($j['status']),
                    'url' => url('/company/jobs'),
                ];
            }

            // 2. Applications Received
            $applications = $this->db->fetchAll(
                "SELECT a.id, a.job_id, j.title as job_title, s.first_name, s.last_name, a.status 
                 FROM applications a JOIN jobs j ON a.job_id = j.id JOIN students s ON a.student_id = s.id 
                 WHERE j.company_id = ? AND (s.first_name LIKE ? OR s.last_name LIKE ? OR j.title LIKE ?)
                 LIMIT 4",
                [$companyId, $searchParam, $searchParam, $searchParam]
            );
            foreach ($applications as $app) {
                $results[] = [
                    'type' => 'Application',
                    'icon' => 'fas fa-user-check',
                    'color' => 'success',
                    'title' => 'Applicant: ' . $app['first_name'] . ' ' . $app['last_name'],
                    'subtitle' => 'For: ' . $app['job_title'] . ' • Status: ' . ucfirst($app['status']),
                    'url' => url('/company/applications/' . $app['job_id']),
                ];
            }

            // 3. Interviews
            $interviews = $this->db->fetchAll(
                "SELECT i.id, j.title as job_title, s.first_name, s.last_name 
                 FROM interviews i JOIN jobs j ON i.job_id = j.id JOIN students s ON i.student_id = s.id 
                 WHERE i.company_id = ? AND (s.first_name LIKE ? OR s.last_name LIKE ? OR j.title LIKE ?)
                 LIMIT 3",
                [$companyId, $searchParam, $searchParam, $searchParam]
            );
            foreach ($interviews as $inv) {
                $results[] = [
                    'type' => 'Interview',
                    'icon' => 'fas fa-calendar-day',
                    'color' => 'warning',
                    'title' => 'Interview: ' . $inv['first_name'] . ' ' . $inv['last_name'],
                    'subtitle' => 'Job: ' . $inv['job_title'],
                    'url' => url('/company/interviews'),
                ];
            }
        }

        if (isAjax()) {
            jsonResponse(['success' => true, 'results' => $results]);
        }
    }
}
