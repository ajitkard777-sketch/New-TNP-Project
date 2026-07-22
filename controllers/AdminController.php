<?php
/**
 * TPMS - Admin Controller
 */
require_once ROOT_PATH . '/models/Student.php';
require_once ROOT_PATH . '/models/Company.php';
require_once ROOT_PATH . '/models/Job.php';
require_once ROOT_PATH . '/models/User.php';

class AdminController {
    private Student $studentModel;
    private Company $companyModel;
    private Job $jobModel;
    private User $userModel;
    private Database $db;

    public function __construct() {
        $this->studentModel = new Student();
        $this->companyModel = new Company();
        $this->jobModel = new Job();
        $this->userModel = new User();
        $this->db = Database::getInstance();
    }

    public function dashboard(): void {
        $pageTitle = 'Admin Dashboard';
        $totalStudents = $this->studentModel->getTotalCount();
        $placedStudents = $this->studentModel->getPlacedCount();
        $totalCompanies = $this->companyModel->getTotalCount();
        $approvedCompanies = $this->companyModel->getApprovedCount();
        $pendingCompanies = $this->companyModel->getPendingCount();
        $totalJobs = $this->jobModel->getTotalCount();
        $activeJobs = $this->jobModel->getActiveCount();
        $totalApplications = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM applications");
        $highestPackage = $this->studentModel->getHighestPackage();
        $averagePackage = $this->studentModel->getAveragePackage();
        $totalPlacements = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM placements");
        $totalTrainings = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM trainings");
        $branchStats = $this->studentModel->getBranchStats();
        $recentActivities = $this->db->fetchAll("SELECT al.*, u.email FROM activity_logs al LEFT JOIN users u ON al.user_id = u.id ORDER BY al.created_at DESC LIMIT 10");
        $pendingJobs = $this->db->fetchAll("SELECT j.*, c.company_name FROM jobs j JOIN companies c ON j.company_id = c.id WHERE j.status = 'pending' ORDER BY j.created_at DESC LIMIT 10");
        $pendingCompanyList = $this->db->fetchAll("SELECT c.*, u.email FROM companies c JOIN users u ON c.user_id = u.id WHERE c.is_approved = 0 ORDER BY c.created_at DESC LIMIT 10");
        $monthlyPlacements = $this->db->fetchAll("SELECT DATE_FORMAT(placement_date, '%Y-%m') as month, COUNT(*) as count, AVG(package) as avg_package FROM placements WHERE placement_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) GROUP BY month ORDER BY month");
        require_once VIEWS_PATH . '/admin/dashboard.php';
    }

    // ============ Students ============
    public function students(): void {
        $pageTitle = 'Manage Students';
        $search = sanitize($_GET['search'] ?? '');
        $branch = sanitize($_GET['branch'] ?? '');
        $status = sanitize($_GET['status'] ?? '');
        $page = max(1, (int)($_GET['page'] ?? 1));
        $total = $this->studentModel->count($search, $branch, $status);
        $pagination = getPagination($total, $page);
        $students = $this->studentModel->getAll($pagination['offset'], $pagination['per_page'], $search, $branch, $status);
        require_once VIEWS_PATH . '/admin/students.php';
    }

    public function viewStudent($id): void {
        $student = $this->studentModel->findById($id);
        if (!$student) { setFlash('danger', 'Student not found.'); redirect('/admin/students'); return; }
        $pageTitle = $student['first_name'] . ' ' . $student['last_name'];
        $projects = $this->studentModel->getProjects($id);
        $certifications = $this->studentModel->getCertifications($id);
        $applications = $this->db->fetchAll("SELECT a.*, j.title as job_title, c.company_name FROM applications a JOIN jobs j ON a.job_id = j.id JOIN companies c ON j.company_id = c.id WHERE a.student_id = ? ORDER BY a.applied_at DESC", [$id]);
        require_once VIEWS_PATH . '/admin/view-student.php';
    }

    public function updateStudentStatus($id): void {
        $status = sanitize($_POST['status'] ?? '');
        if (!in_array($status, ['active', 'inactive', 'banned'])) { redirect('/admin/students'); return; }
        $student = $this->studentModel->findById($id);
        if ($student) { $this->db->update("UPDATE users SET status = ? WHERE id = ?", [$status, $student['user_id']]); }
        setFlash('success', 'Student status updated.');
        redirect('/admin/students');
    }

    public function markPlaced($id): void {
        CsrfMiddleware::requireValidToken();
        $data = sanitizeArray($_POST);
        $this->studentModel->update($id, [
            'is_placed' => 1,
            'placed_company' => $data['placed_company'] ?? '',
            'placed_package' => $data['placed_package'] ?? 0,
            'placed_date' => $data['placed_date'] ?? date('Y-m-d'),
        ]);
        $student = $this->studentModel->findById($id);
        if ($student) {
            $companyObj = $this->db->fetchOne("SELECT id FROM companies WHERE company_name = ?", [$data['placed_company'] ?? '']);
            $this->db->insert("INSERT INTO placements (student_id, company_id, package, placement_date, status) VALUES (?, ?, ?, ?, 'confirmed')",
                [$id, $companyObj['id'] ?? null, $data['placed_package'] ?? 0, $data['placed_date'] ?? date('Y-m-d')]);
            $this->db->insert("INSERT INTO notifications (user_id, title, message, type, category) VALUES (?, ?, ?, ?, ?)",
                [$student['user_id'], 'Congratulations! You are placed!', "You have been placed at {$data['placed_company']} with a package of " . formatCurrency($data['placed_package'] ?? 0), 'success', 'placement']);
        }
        setFlash('success', 'Student marked as placed!');
        redirect('/admin/students');
    }

    public function deleteStudent($id): void {
        $student = $this->studentModel->findById($id);
        if ($student) {
            $this->db->delete("DELETE FROM students WHERE id = ?", [$id]);
            $this->db->delete("DELETE FROM users WHERE id = ?", [$student['user_id']]);
        }
        setFlash('success', 'Student deleted.');
        redirect('/admin/students');
    }

    // ============ Companies ============
    public function companies(): void {
        $pageTitle = 'Manage Companies';
        $search = sanitize($_GET['search'] ?? '');
        $status = sanitize($_GET['status'] ?? '');
        $page = max(1, (int)($_GET['page'] ?? 1));
        $total = $this->companyModel->count($search, $status);
        $pagination = getPagination($total, $page);
        $companies = $this->companyModel->getAll($pagination['offset'], $pagination['per_page'], $search, $status);
        require_once VIEWS_PATH . '/admin/companies.php';
    }

    public function approveCompany($id): void {
        $this->companyModel->approve($id);
        $company = $this->companyModel->findById($id);
        if ($company) {
            $this->db->update("UPDATE users SET status = 'active' WHERE id = ?", [$company['user_id']]);
            $this->db->insert("INSERT INTO notifications (user_id, title, message, type, category) VALUES (?, ?, ?, ?, ?)",
                [$company['user_id'], 'Registration Approved!', 'Your company registration has been approved. You can now post jobs.', 'success', 'system']);
        }
        logActivity('approve_company', 'company', "Approved company: " . ($company['company_name'] ?? ''));
        setFlash('success', 'Company approved!');
        redirect('/admin/companies');
    }

    public function rejectCompany($id): void {
        $this->companyModel->reject($id);
        setFlash('success', 'Company rejected.');
        redirect('/admin/companies');
    }

    public function deleteCompany($id): void {
        $company = $this->companyModel->findById($id);
        if ($company) {
            $this->db->delete("DELETE FROM companies WHERE id = ?", [$id]);
            $this->db->delete("DELETE FROM users WHERE id = ?", [$company['user_id']]);
        }
        setFlash('success', 'Company deleted.');
        redirect('/admin/companies');
    }

    // ============ Jobs ============
    public function jobs(): void {
        $pageTitle = 'Manage Jobs';
        $search = sanitize($_GET['search'] ?? '');
        $status = sanitize($_GET['status'] ?? '');
        $params = []; $where = "1=1";
        if ($search) { $where .= " AND (j.title LIKE ? OR c.company_name LIKE ?)"; $params = array_merge($params, ["%$search%", "%$search%"]); }
        if ($status) { $where .= " AND j.status = ?"; $params[] = $status; }
        $jobs = $this->db->fetchAll("SELECT j.*, c.company_name, c.logo, (SELECT COUNT(*) FROM applications a WHERE a.job_id = j.id) as app_count FROM jobs j JOIN companies c ON j.company_id = c.id WHERE $where ORDER BY j.created_at DESC LIMIT 100", $params);
        require_once VIEWS_PATH . '/admin/jobs.php';
    }

    public function approveJob($id): void {
        $this->jobModel->update($id, ['status' => 'active']);
        $job = $this->jobModel->findById($id);
        if ($job) {
            $company = $this->companyModel->findById($job['company_id']);
            if ($company) {
                $this->db->insert("INSERT INTO notifications (user_id, title, message, type, category) VALUES (?, ?, ?, ?, ?)",
                    [$company['user_id'], 'Job Approved', "Your job posting '{$job['title']}' has been approved and is now live.", 'success', 'job']);
            }
            // Global notification for students
            $this->db->insert("INSERT INTO notifications (user_id, title, message, type, category, is_global) VALUES (NULL, ?, ?, ?, ?, 1)",
                ['New Job: ' . $job['title'], $job['company_name'] . ' is hiring for ' . $job['title'], 'info', 'job']);
        }
        setFlash('success', 'Job approved and live!');
        redirect('/admin/jobs');
    }

    public function closeJob($id): void {
        $this->jobModel->update($id, ['status' => 'closed']);
        setFlash('success', 'Job closed.');
        redirect('/admin/jobs');
    }

    public function deleteJob($id): void {
        $this->jobModel->delete($id);
        setFlash('success', 'Job deleted.');
        redirect('/admin/jobs');
    }

    // ============ Placements ============
    public function placements(): void {
        $pageTitle = 'Placements';
        $placements = $this->db->fetchAll("SELECT p.*, s.first_name, s.last_name, s.branch, s.profile_photo, c.company_name FROM placements p JOIN students s ON p.student_id = s.id LEFT JOIN companies c ON p.company_id = c.id ORDER BY p.placement_date DESC LIMIT 200");
        $stats = [
            'total' => count($placements),
            'highest' => $this->studentModel->getHighestPackage(),
            'average' => $this->studentModel->getAveragePackage(),
        ];
        require_once VIEWS_PATH . '/admin/placements.php';
    }

    // ============ Trainings ============
    public function trainings(): void {
        $pageTitle = 'Manage Trainings';
        $trainings = $this->db->fetchAll("SELECT t.*, f.name as faculty_name FROM trainings t LEFT JOIN faculty f ON t.faculty_id = f.id ORDER BY t.created_at DESC");
        $faculty = $this->db->fetchAll("SELECT * FROM faculty WHERE status = 'active' ORDER BY name");
        require_once VIEWS_PATH . '/admin/trainings.php';
    }

    public function createTraining(): void {
        CsrfMiddleware::requireValidToken();
        $data = sanitizeArray($_POST);
        $this->db->insert("INSERT INTO trainings (title, description, training_type, mode, venue, trainer_name, start_date, end_date, start_time, end_time, capacity, faculty_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [$data['title'], $data['description'] ?? '', $data['training_type'] ?? 'technical', $data['mode'] ?? 'offline', $data['venue'] ?? '', $data['trainer_name'] ?? '', $data['start_date'], $data['end_date'], $data['start_time'] ?? null, $data['end_time'] ?? null, $data['capacity'] ?? 50, $data['faculty_id'] ?: null, $data['status'] ?? 'upcoming']);
        setFlash('success', 'Training created!');
        redirect('/admin/trainings');
    }

    public function deleteTraining($id): void {
        $this->db->delete("DELETE FROM trainings WHERE id = ?", [$id]);
        setFlash('success', 'Training deleted.');
        redirect('/admin/trainings');
    }

    // ============ Higher Studies ============
    public function higherStudies(): void {
        $pageTitle = 'Higher Studies';
        $universities = $this->db->fetchAll("SELECT * FROM universities ORDER BY ranking ASC");
        $exams = $this->db->fetchAll("SELECT * FROM entrance_exams ORDER BY exam_date ASC");
        $scholarships = $this->db->fetchAll("SELECT * FROM scholarships ORDER BY created_at DESC");
        require_once VIEWS_PATH . '/admin/higher-studies.php';
    }

    public function createUniversity(): void {
        CsrfMiddleware::requireValidToken();
        $data = sanitizeArray($_POST);
        $this->db->insert("INSERT INTO universities (name, city, country, ranking, website, description, admission_deadline, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'active')",
            [$data['name'], $data['city'] ?? '', $data['country'] ?? 'India', $data['ranking'] ?: null, $data['website'] ?? '', $data['description'] ?? '', $data['admission_deadline'] ?: null]);
        setFlash('success', 'University added!');
        redirect('/admin/higher-studies');
    }

    // ============ Interviews ============
    public function interviews(): void {
        $pageTitle = 'All Interviews';
        $interviews = $this->db->fetchAll("SELECT i.*, j.title as job_title, c.company_name, s.first_name, s.last_name, s.branch FROM interviews i JOIN jobs j ON i.job_id = j.id JOIN companies c ON i.company_id = c.id JOIN students s ON i.student_id = s.id ORDER BY i.interview_date DESC LIMIT 200");
        $applications = $this->db->fetchAll("SELECT a.id as application_id, a.student_id, a.job_id, j.company_id, j.title as job_title, c.company_name, s.first_name, s.last_name, s.branch FROM applications a JOIN jobs j ON a.job_id = j.id JOIN companies c ON j.company_id = c.id JOIN students s ON a.student_id = s.id ORDER BY a.applied_at DESC LIMIT 200");
        require_once VIEWS_PATH . '/admin/interviews.php';
    }

    public function scheduleInterview(): void {
        CsrfMiddleware::requireValidToken();
        $data = sanitizeArray($_POST);
        $appId = (int)($data['application_id'] ?? 0);

        $app = $this->db->fetchOne("SELECT a.*, j.company_id, j.title as job_title FROM applications a JOIN jobs j ON a.job_id = j.id WHERE a.id = ?", [$appId]);
        if (!$app) { setFlash('danger', 'Application not found.'); redirect('/admin/interviews'); return; }

        $this->db->insert("INSERT INTO interviews (student_id, company_id, job_id, round, interview_date, interview_time, mode, venue, meeting_link, instructions, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'scheduled')",
            [$app['student_id'], $app['company_id'], $app['job_id'], $data['round'] ?? 'Round 1', $data['interview_date'], $data['interview_time'], $data['mode'] ?? 'offline', $data['venue'] ?? null, $data['meeting_link'] ?? null, $data['instructions'] ?? null]);

        $this->jobModel->updateApplicationStatus($appId, 'interview');

        $student = $this->db->fetchOne("SELECT user_id FROM students WHERE id = ?", [$app['student_id']]);
        if ($student) {
            $this->db->insert("INSERT INTO notifications (user_id, title, message, type, category) VALUES (?, ?, ?, ?, ?)",
                [$student['user_id'], 'Interview Scheduled by Admin', "Interview for {$app['job_title']} on " . formatDate($data['interview_date']), 'info', 'interview']);
        }

        setFlash('success', 'Interview scheduled successfully by Admin!');
        redirect('/admin/interviews');
    }

    public function updateInterview($id): void {
        CsrfMiddleware::requireValidToken();
        $interview = $this->db->fetchOne("SELECT * FROM interviews WHERE id = ?", [$id]);
        if (!$interview) { setFlash('danger', 'Interview not found.'); redirect('/admin/interviews'); return; }

        $data = sanitizeArray($_POST);
        $this->db->update(
            "UPDATE interviews SET round = ?, interview_date = ?, interview_time = ?, mode = ?, venue = ?, meeting_link = ?, instructions = ?, status = 'rescheduled' WHERE id = ?",
            [
                $data['round'] ?? $interview['round'],
                $data['interview_date'] ?? $interview['interview_date'],
                $data['interview_time'] ?? $interview['interview_time'],
                $data['mode'] ?? $interview['mode'],
                $data['venue'] ?? null,
                $data['meeting_link'] ?? null,
                $data['instructions'] ?? null,
                $id
            ]
        );

        $student = $this->db->fetchOne("SELECT user_id FROM students WHERE id = ?", [$interview['student_id']]);
        if ($student) {
            $this->db->insert("INSERT INTO notifications (user_id, title, message, type, category) VALUES (?, ?, ?, ?, ?)",
                [$student['user_id'], 'Interview Rescheduled', "Your interview has been rescheduled for " . formatDate($data['interview_date']), 'info', 'interview']);
        }

        setFlash('success', 'Interview updated successfully.');
        redirect('/admin/interviews');
    }

    public function cancelInterview($id): void {
        CsrfMiddleware::requireValidToken();
        $interview = $this->db->fetchOne("SELECT * FROM interviews WHERE id = ?", [$id]);
        if (!$interview) { setFlash('danger', 'Interview not found.'); redirect('/admin/interviews'); return; }

        $this->db->update("UPDATE interviews SET status = 'cancelled' WHERE id = ?", [$id]);

        $student = $this->db->fetchOne("SELECT user_id FROM students WHERE id = ?", [$interview['student_id']]);
        if ($student) {
            $this->db->insert("INSERT INTO notifications (user_id, title, message, type, category) VALUES (?, ?, ?, ?, ?)",
                [$student['user_id'], 'Interview Cancelled', "Your interview scheduled for " . formatDate($interview['interview_date']) . " has been cancelled.", 'warning', 'interview']);
        }

        setFlash('success', 'Interview cancelled.');
        redirect('/admin/interviews');
    }

    public function updateInterviewResult($id): void {
        $result = sanitize($_POST['result'] ?? '');
        if (!in_array($result, ['pending', 'passed', 'failed'])) { setFlash('danger', 'Invalid result.'); redirect('/admin/interviews'); return; }
        $interview = $this->db->fetchOne("SELECT * FROM interviews WHERE id = ?", [$id]);
        if (!$interview) { setFlash('danger', 'Interview not found.'); redirect('/admin/interviews'); return; }
        
        $this->db->update("UPDATE interviews SET result = ?, status = 'completed' WHERE id = ?", [$result, $id]);
        if ($result === 'passed') { $this->jobModel->updateApplicationStatus($this->db->fetchColumn("SELECT id FROM applications WHERE student_id = ? AND job_id = ?", [$interview['student_id'], $interview['job_id']]), 'shortlisted'); }
        if ($result === 'failed') { $this->jobModel->updateApplicationStatus($this->db->fetchColumn("SELECT id FROM applications WHERE student_id = ? AND job_id = ?", [$interview['student_id'], $interview['job_id']]), 'rejected'); }
        
        $student = $this->db->fetchOne("SELECT user_id FROM students WHERE id = ?", [$interview['student_id']]);
        if ($student) {
            $this->db->insert("INSERT INTO notifications (user_id, title, message, type, category) VALUES (?, ?, ?, ?, ?)",
                [$student['user_id'], 'Interview Result Updated', "Result for your interview: " . ucfirst($result), $result === 'passed' ? 'success' : 'danger', 'interview']);
        }

        setFlash('success', 'Interview result updated.');
        redirect('/admin/interviews');
    }

    // ============ Approvals ============
    public function approvals(): void {
        $pageTitle = 'Pending Approvals';
        $pendingCompanies = $this->db->fetchAll("SELECT c.*, u.email FROM companies c JOIN users u ON c.user_id = u.id WHERE c.is_approved = 0 ORDER BY c.created_at DESC");
        $pendingJobs = $this->db->fetchAll("SELECT j.*, c.company_name FROM jobs j JOIN companies c ON j.company_id = c.id WHERE j.status = 'pending' ORDER BY j.created_at DESC");
        require_once VIEWS_PATH . '/admin/approvals.php';
    }

    // ============ Notifications ============
    public function notifications(): void {
        $pageTitle = 'Notifications';
        $notifications = $this->db->fetchAll("SELECT * FROM notifications WHERE user_id = ? OR is_global = 1 ORDER BY created_at DESC LIMIT 50", [$_SESSION['user_id']]);
        require_once VIEWS_PATH . '/admin/notifications.php';
    }

    public function sendNotification(): void {
        CsrfMiddleware::requireValidToken();
        $data = sanitizeArray($_POST);
        $target = $data['target'] ?? 'all';
        if ($target === 'all') {
            $this->db->insert("INSERT INTO notifications (user_id, title, message, type, category, is_global) VALUES (NULL, ?, ?, ?, 'announcement', 1)", [$data['title'], $data['message'], $data['type'] ?? 'info']);
        } elseif ($target === 'students') {
            $users = $this->db->fetchAll("SELECT id FROM users WHERE role = 'student' AND status = 'active'");
            foreach ($users as $u) { $this->db->insert("INSERT INTO notifications (user_id, title, message, type, category) VALUES (?, ?, ?, ?, 'announcement')", [$u['id'], $data['title'], $data['message'], $data['type'] ?? 'info']); }
        } elseif ($target === 'companies') {
            $users = $this->db->fetchAll("SELECT id FROM users WHERE role = 'company' AND status = 'active'");
            foreach ($users as $u) { $this->db->insert("INSERT INTO notifications (user_id, title, message, type, category) VALUES (?, ?, ?, ?, 'announcement')", [$u['id'], $data['title'], $data['message'], $data['type'] ?? 'info']); }
        }
        logActivity('send_notification', 'notification', "Sent notification: {$data['title']} to $target");
        setFlash('success', 'Notification sent!');
        redirect('/admin/notifications');
    }

    // ============ Faculty ============
    public function faculty(): void {
        $pageTitle = 'Manage Faculty';
        $faculty = $this->db->fetchAll("SELECT * FROM faculty ORDER BY name ASC");
        require_once VIEWS_PATH . '/admin/faculty.php';
    }

    public function createFaculty(): void {
        CsrfMiddleware::requireValidToken();
        $data = sanitizeArray($_POST);
        $this->db->insert("INSERT INTO faculty (name, email, phone, department, designation, specialization, status) VALUES (?, ?, ?, ?, ?, ?, 'active')",
            [$data['name'], $data['email'] ?? '', $data['phone'] ?? '', $data['department'] ?? '', $data['designation'] ?? '', $data['specialization'] ?? '']);
        setFlash('success', 'Faculty added!');
        redirect('/admin/faculty');
    }

    public function deleteFaculty($id): void {
        $this->db->delete("DELETE FROM faculty WHERE id = ?", [$id]);
        setFlash('success', 'Faculty deleted.');
        redirect('/admin/faculty');
    }

    // ============ Reports ============
    public function reports(): void {
        $pageTitle = 'Reports & Analytics';
        $placementRate = $this->studentModel->getTotalCount() > 0 ? round(($this->studentModel->getPlacedCount() / $this->studentModel->getTotalCount()) * 100, 1) : 0;
        $branchStats = $this->studentModel->getBranchStats();
        $companyWise = $this->db->fetchAll("SELECT c.company_name, COUNT(p.id) as placements, AVG(p.package) as avg_package, MAX(p.package) as max_package FROM placements p JOIN companies c ON p.company_id = c.id GROUP BY c.id ORDER BY placements DESC LIMIT 20");
        $yearlyStats = $this->db->fetchAll("SELECT s.passing_year as year, COUNT(*) as total, SUM(s.is_placed) as placed FROM students s WHERE s.passing_year IS NOT NULL GROUP BY s.passing_year ORDER BY s.passing_year DESC LIMIT 5");
        $topRecruiters = $this->db->fetchAll("SELECT c.company_name, c.logo, COUNT(p.id) as hires FROM placements p JOIN companies c ON p.company_id = c.id GROUP BY c.id ORDER BY hires DESC LIMIT 10");
        require_once VIEWS_PATH . '/admin/reports.php';
    }

    // ============ Activity Logs ============
    public function logs(): void {
        $pageTitle = 'Activity Logs';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $total = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM activity_logs");
        $pagination = getPagination($total, $page, 25);
        $logs = $this->db->fetchAll("SELECT al.*, u.email, u.role FROM activity_logs al LEFT JOIN users u ON al.user_id = u.id ORDER BY al.created_at DESC LIMIT ? OFFSET ?", [$pagination['per_page'], $pagination['offset']]);
        require_once VIEWS_PATH . '/admin/logs.php';
    }

    // ============ Settings ============
    public function settings(): void {
        $pageTitle = 'System Settings';
        require_once VIEWS_PATH . '/admin/settings.php';
    }
}
