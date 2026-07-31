<?php
/**
 * TPMS - Admin Controller
 */
require_once ROOT_PATH . '/models/Student.php';
require_once ROOT_PATH . '/models/Company.php';
require_once ROOT_PATH . '/models/Job.php';
require_once ROOT_PATH . '/models/User.php';
require_once ROOT_PATH . '/includes/Mailer.php';

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
        $totalInterviews = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM interviews");
        $scheduledInterviews = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM interviews WHERE status = 'scheduled' AND interview_date >= CURDATE()");
        $appliedStudentsCount = (int)$this->db->fetchColumn("SELECT COUNT(DISTINCT student_id) FROM applications");
        $trainingEnrolledCount = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM training_registrations");
        $higherStudiesCount = (int)$this->db->fetchColumn("SELECT COUNT(DISTINCT student_id) FROM higher_study_applications");
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
        $placedPackage = (float)($data['placed_package'] ?? 0);
        if ($placedPackage >= 1000) {
            $placedPackage = $placedPackage / 100000;
        }

        $this->studentModel->update($id, [
            'is_placed' => 1,
            'placed_company' => $data['placed_company'] ?? '',
            'placed_package' => $placedPackage,
            'placed_date' => $data['placed_date'] ?? date('Y-m-d'),
        ]);
        $student = $this->studentModel->findById($id);
        if ($student) {
            $companyObj = $this->db->fetchOne("SELECT id FROM companies WHERE company_name = ?", [$data['placed_company'] ?? '']);
            $this->db->insert("INSERT INTO placements (student_id, company_id, package, placement_date, status) VALUES (?, ?, ?, ?, 'confirmed')",
                [$id, $companyObj['id'] ?? null, $placedPackage, $data['placed_date'] ?? date('Y-m-d')]);
            $this->db->insert("INSERT INTO notifications (user_id, title, message, type, category, link) VALUES (?, ?, ?, ?, ?, ?)",
                [$student['user_id'], 'Congratulations! You are placed!', "You have been placed at {$data['placed_company']} with a package of " . formatPackage($placedPackage), 'success', 'placement', url('/student/profile')]);
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
            $this->db->insert("INSERT INTO notifications (user_id, title, message, type, category, link) VALUES (?, ?, ?, ?, ?, ?)",
                [$company['user_id'], 'Registration Approved!', 'Your company registration has been approved. You can now post jobs.', 'success', 'system', url('/company/post-job')]);
            // Send email
            $user = $this->db->fetchOne("SELECT email FROM users WHERE id = ?", [$company['user_id']]);
            if ($user) {
                Mailer::sendApprovalNotification($user['email'], $company['company_name'], 'Company Registration', $company['company_name'], true);
            }
        }
        logActivity('approve_company', 'company', "Approved company: " . ($company['company_name'] ?? ''));
        setFlash('success', 'Company approved!');
        redirect('/admin/companies');
    }

    public function rejectCompany($id): void {
        $company = $this->companyModel->findById($id);
        $this->companyModel->reject($id);
        if ($company) {
            $user = $this->db->fetchOne("SELECT email FROM users WHERE id = ?", [$company['user_id']]);
            if ($user) {
                Mailer::sendApprovalNotification($user['email'], $company['company_name'], 'Company Registration', $company['company_name'], false);
            }
        }
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
                $this->db->insert("INSERT INTO notifications (user_id, title, message, type, category, link) VALUES (?, ?, ?, ?, ?, ?)",
                    [$company['user_id'], 'Job Approved', "Your job posting '{$job['title']}' has been approved and is now live.", 'success', 'job', url('/company/jobs')]);
                // Send email
                $user = $this->db->fetchOne("SELECT email FROM users WHERE id = ?", [$company['user_id']]);
                if ($user) {
                    Mailer::sendApprovalNotification($user['email'], $company['company_name'], 'Job Posting', $job['title'], true);
                }
            }
            // Global notification for students
            $this->db->insert("INSERT INTO notifications (user_id, title, message, type, category, is_global, link) VALUES (NULL, ?, ?, ?, ?, 1, ?)",
                ['New Job: ' . $job['title'], $job['company_name'] . ' is hiring for ' . $job['title'], 'info', 'job', url('/student/jobs')]);
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
        $search  = sanitize($_GET['search']  ?? '');
        $branch  = sanitize($_GET['branch']  ?? '');
        $company = sanitize($_GET['company'] ?? '');
        $page    = max(1, (int)($_GET['page'] ?? 1));

        $params = [];
        $where  = '1=1';
        if ($search)  { $where .= ' AND (s.first_name LIKE ? OR s.last_name LIKE ? OR u.email LIKE ?)'; $params = array_merge($params, ["%$search%", "%$search%", "%$search%"]); }
        if ($branch)  { $where .= ' AND s.branch = ?';       $params[] = $branch; }
        if ($company) { $where .= ' AND c.company_name LIKE ?'; $params[] = "%$company%"; }

        $countParams = $params;
        $totalPlacements = (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM placements p
             JOIN students s ON p.student_id = s.id
             LEFT JOIN companies c ON p.company_id = c.id
             JOIN users u ON s.user_id = u.id
             WHERE $where",
            $countParams
        );

        $pagination = getPagination($totalPlacements, $page, 20);

        $listParams   = $params;
        $listParams[] = $pagination['per_page'];
        $listParams[] = $pagination['offset'];
        $placements = $this->db->fetchAll(
            "SELECT p.*, s.first_name, s.last_name, s.branch, s.profile_photo,
                    c.company_name, u.email
             FROM placements p
             JOIN students s ON p.student_id = s.id
             LEFT JOIN companies c ON p.company_id = c.id
             JOIN users u ON s.user_id = u.id
             WHERE $where
             ORDER BY p.placement_date DESC
             LIMIT ? OFFSET ?",
            $listParams
        );

        $stats = [
            'total'   => $totalPlacements,
            'highest' => $this->studentModel->getHighestPackage(),
            'average' => $this->studentModel->getAveragePackage(),
        ];

        // For the company filter dropdown
        $companyList = $this->db->fetchAll("SELECT DISTINCT c.company_name FROM placements p JOIN companies c ON p.company_id = c.id ORDER BY c.company_name");

        require_once VIEWS_PATH . '/admin/placements.php';
    }

    // ============ Trainings ============
    public function trainings(): void {
        $pageTitle = 'Manage Trainings';
        $trainings = $this->db->fetchAll("SELECT t.*, f.name as faculty_name, (SELECT COUNT(*) FROM training_registrations tr WHERE tr.training_id = t.id) as enrolled_count FROM trainings t LEFT JOIN faculty f ON t.faculty_id = f.id ORDER BY t.created_at DESC");
        $faculty = $this->db->fetchAll("SELECT * FROM faculty WHERE status = 'active' ORDER BY name");
        // All enrollments for the Enrollments tab
        $enrollments = $this->db->fetchAll(
            "SELECT tr.*, t.title as training_title, t.start_date, t.end_date, s.first_name, s.last_name, s.branch, s.enrollment_no
             FROM training_registrations tr
             JOIN trainings t ON tr.training_id = t.id
             JOIN students s ON tr.student_id = s.id
             ORDER BY tr.created_at DESC"
        );
        require_once VIEWS_PATH . '/admin/trainings.php';
    }

    public function trainingEnrollments(): void {
        $pageTitle = 'Training Enrollments';
        $search = sanitize($_GET['search'] ?? '');
        $status = sanitize($_GET['status'] ?? '');
        $branch = sanitize($_GET['branch'] ?? '');
        $trainingId = (int)($_GET['training_id'] ?? 0);
        $page = max(1, (int)($_GET['page'] ?? 1));

        // Sorting
        $allowedSorts = [
            'date'     => 'tr.created_at',
            'name'     => 's.first_name',
            'branch'   => 's.branch',
            'training' => 't.title',
            'status'   => 'tr.status',
        ];
        $sortBy  = isset($_GET['sort']) && array_key_exists($_GET['sort'], $allowedSorts) ? $_GET['sort'] : 'date';
        $sortDir = (isset($_GET['order']) && strtolower($_GET['order']) === 'asc') ? 'ASC' : 'DESC';
        $orderSql = $allowedSorts[$sortBy] . ' ' . $sortDir;

        $params = [];
        $where = "1=1";

        if ($search) {
            $where .= " AND (s.first_name LIKE ? OR s.last_name LIKE ? OR s.enrollment_no LIKE ? OR s.registration_no LIKE ? OR t.title LIKE ? OR u.email LIKE ?)";
            $params = array_merge($params, ["%$search%", "%$search%", "%$search%", "%$search%", "%$search%", "%$search%"]);
        }

        if ($status) {
            if ($status === 'pending' || $status === 'registered') {
                $where .= " AND (tr.status = 'pending' OR tr.status = 'registered')";
            } elseif ($status === 'approved' || $status === 'attended') {
                $where .= " AND (tr.status = 'approved' OR tr.status = 'attended')";
            } elseif ($status === 'rejected' || $status === 'dropped') {
                $where .= " AND (tr.status = 'rejected' OR tr.status = 'dropped')";
            } else {
                $where .= " AND tr.status = ?";
                $params[] = $status;
            }
        }

        if ($branch) {
            $where .= " AND s.branch = ?";
            $params[] = $branch;
        }

        if ($trainingId > 0) {
            $where .= " AND tr.training_id = ?";
            $params[] = $trainingId;
        }

        $totalSql = "SELECT COUNT(*) FROM training_registrations tr
                     JOIN trainings t ON tr.training_id = t.id
                     JOIN students s ON tr.student_id = s.id
                     JOIN users u ON s.user_id = u.id
                     WHERE $where";
        $total = (int)$this->db->fetchColumn($totalSql, $params);
        $pagination = getPagination($total, $page);

        $sql = "SELECT tr.id, tr.status, tr.certificate_issued, tr.admin_remarks, tr.created_at as applied_at,
                       t.id as training_id, t.title as training_title, t.trainer_name, t.training_type, t.mode, t.venue,
                       s.id as student_id, s.first_name, s.last_name,
                       s.enrollment_no, s.registration_no, s.branch, s.passing_year, s.admission_year,
                       s.degree, s.phone, s.resume_path, s.resume_original_name, s.profile_photo,
                       u.email, u.id as user_id
                FROM training_registrations tr
                JOIN trainings t ON tr.training_id = t.id
                JOIN students s ON tr.student_id = s.id
                JOIN users u ON s.user_id = u.id
                WHERE $where
                ORDER BY $orderSql
                LIMIT ? OFFSET ?";

        $queryParams = array_merge($params, [$pagination['per_page'], $pagination['offset']]);
        $enrollments = $this->db->fetchAll($sql, $queryParams);

        // For CSV/PDF export — fetch all matching rows (no limit)
        if (!empty($_GET['export']) && in_array($_GET['export'], ['csv', 'pdf'])) {
            $exportSql = "SELECT tr.id, tr.status, tr.certificate_issued, tr.admin_remarks, tr.created_at as applied_at,
                           t.title as training_title, t.trainer_name, t.training_type, t.mode,
                           s.first_name, s.last_name, s.enrollment_no, s.registration_no,
                           s.branch, s.degree, s.passing_year, s.phone, s.resume_path,
                           u.email
                    FROM training_registrations tr
                    JOIN trainings t ON tr.training_id = t.id
                    JOIN students s ON tr.student_id = s.id
                    JOIN users u ON s.user_id = u.id
                    WHERE $where
                    ORDER BY $orderSql";
            $exportRows = $this->db->fetchAll($exportSql, $params);

            if ($_GET['export'] === 'csv') {
                header('Content-Type: text/csv; charset=UTF-8');
                header('Content-Disposition: attachment; filename="training_enrollments_' . date('Y-m-d') . '.csv"');
                header('Pragma: no-cache');
                $fp = fopen('php://output', 'w');
                fputcsv($fp, ['App ID', 'Student Name', 'Roll No (Enrollment)', 'Registration No', 'Branch', 'Degree', 'Passing Year', 'Email', 'Phone', 'Training Program', 'Provider', 'Type', 'Mode', 'Enrollment Date', 'Status']);
                foreach ($exportRows as $r) {
                    $statusMap = ['pending'=>'Pending','registered'=>'Pending','approved'=>'Approved','attended'=>'Approved','rejected'=>'Rejected','dropped'=>'Rejected','completed'=>'Completed'];
                    fputcsv($fp, [
                        '#TR-' . $r['id'],
                        trim($r['first_name'] . ' ' . $r['last_name']),
                        $r['enrollment_no'] ?? '',
                        $r['registration_no'] ?? '',
                        $r['branch'] ?? '',
                        $r['degree'] ?? '',
                        $r['passing_year'] ?? '',
                        $r['email'] ?? '',
                        $r['phone'] ?? '',
                        $r['training_title'] ?? '',
                        $r['trainer_name'] ?? '',
                        ucfirst($r['training_type'] ?? ''),
                        ucfirst($r['mode'] ?? ''),
                        date('d M Y', strtotime($r['applied_at'])),
                        $statusMap[strtolower($r['status'])] ?? ucfirst($r['status']),
                    ]);
                }
                fclose($fp);
                exit;
            }
        }

        // Fetch all training programs for dropdown
        $allTrainings = $this->db->fetchAll("SELECT id, title FROM trainings ORDER BY title ASC");

        // Metrics (global, unfiltered)
        $totalApplications = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM training_registrations");
        $pendingCount   = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM training_registrations WHERE status IN ('pending', 'registered')");
        $approvedCount  = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM training_registrations WHERE status IN ('approved', 'attended')");
        $rejectedCount  = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM training_registrations WHERE status IN ('rejected', 'dropped')");
        $completedCount = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM training_registrations WHERE status = 'completed'");

        require_once VIEWS_PATH . '/admin/training-enrollments.php';
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

    public function approveTrainingEnrollment($id): void {
        CsrfMiddleware::requireValidToken();
        $reg = $this->db->fetchOne(
            "SELECT tr.*, t.title as training_title, s.user_id, s.first_name, s.last_name, u.email 
             FROM training_registrations tr 
             JOIN trainings t ON tr.training_id = t.id 
             JOIN students s ON tr.student_id = s.id 
             JOIN users u ON s.user_id = u.id 
             WHERE tr.id = ?",
            [$id]
        );
        if (!$reg) { setFlash('danger', 'Enrollment not found.'); redirect('/admin/training-enrollments'); return; }
        $remarks = sanitize($_POST['admin_remarks'] ?? '');
        $this->db->update("UPDATE training_registrations SET status = 'approved', admin_remarks = ? WHERE id = ?", [$remarks, $id]);
        $this->db->insert("INSERT INTO notifications (user_id, title, message, type, category, link) VALUES (?, ?, ?, 'success', 'training', ?)",
            [$reg['user_id'], 'Training Enrollment Approved', "Your registration for '{$reg['training_title']}' has been approved.", url('/student/trainings')]);

        $emailSent = false;
        if (!empty($reg['email']) && filter_var($reg['email'], FILTER_VALIDATE_EMAIL)) {
            $studentName = $reg['first_name'] . ' ' . $reg['last_name'];
            $emailSent = Mailer::sendTrainingStatus($reg['email'], $studentName, $reg['training_title'], 'approved', $remarks);
        }

        setFlash('success', 'Enrollment approved.');
        redirect($_SERVER['HTTP_REFERER'] ?? '/admin/training-enrollments');
    }

    public function rejectTrainingEnrollment($id): void {
        CsrfMiddleware::requireValidToken();
        $reg = $this->db->fetchOne(
            "SELECT tr.*, t.title as training_title, s.user_id, s.first_name, s.last_name, u.email, t.id as tid 
             FROM training_registrations tr 
             JOIN trainings t ON tr.training_id = t.id 
             JOIN students s ON tr.student_id = s.id 
             JOIN users u ON s.user_id = u.id 
             WHERE tr.id = ?",
            [$id]
        );
        if (!$reg) { setFlash('danger', 'Enrollment not found.'); redirect('/admin/training-enrollments'); return; }
        $remarks = sanitize($_POST['admin_remarks'] ?? '');
        $this->db->update("UPDATE training_registrations SET status = 'rejected', admin_remarks = ? WHERE id = ?", [$remarks, $id]);
        $this->db->update("UPDATE trainings SET registered_count = GREATEST(0, registered_count - 1) WHERE id = ?", [$reg['tid']]);
        $this->db->insert("INSERT INTO notifications (user_id, title, message, type, category, link) VALUES (?, ?, ?, 'danger', 'training', ?)",
            [$reg['user_id'], 'Training Enrollment Rejected', "Your registration for '{$reg['training_title']}' has been rejected." . ($remarks ? " Reason: $remarks" : ''), url('/student/trainings')]);

        $emailSent = false;
        if (!empty($reg['email']) && filter_var($reg['email'], FILTER_VALIDATE_EMAIL)) {
            $studentName = $reg['first_name'] . ' ' . $reg['last_name'];
            $emailSent = Mailer::sendTrainingStatus($reg['email'], $studentName, $reg['training_title'], 'rejected', $remarks);
        }

        setFlash('success', 'Enrollment rejected.');
        redirect($_SERVER['HTTP_REFERER'] ?? '/admin/training-enrollments');
    }

    public function issueTrainingCertificate($id): void {
        $reg = $this->db->fetchOne(
            "SELECT tr.*, t.title as training_title, s.user_id, s.first_name, s.last_name, u.email 
             FROM training_registrations tr 
             JOIN trainings t ON tr.training_id = t.id 
             JOIN students s ON tr.student_id = s.id 
             JOIN users u ON s.user_id = u.id 
             WHERE tr.id = ?",
            [$id]
        );
        if (!$reg) { setFlash('danger', 'Enrollment not found.'); redirect('/admin/training-enrollments'); return; }
        $this->db->update("UPDATE training_registrations SET certificate_issued = 1, status = 'completed' WHERE id = ?", [$id]);
        $this->db->insert("INSERT INTO notifications (user_id, title, message, type, category, link) VALUES (?, ?, ?, 'success', 'training', ?)",
            [$reg['user_id'], 'Certificate Issued!', "Your certificate for '{$reg['training_title']}' has been issued. Congratulations!", url('/student/trainings')]);

        $emailSent = false;
        if (!empty($reg['email']) && filter_var($reg['email'], FILTER_VALIDATE_EMAIL)) {
            $studentName = $reg['first_name'] . ' ' . $reg['last_name'];
            $emailSent = Mailer::sendTrainingCertificate($reg['email'], $studentName, $reg['training_title']);
        }

        setFlash('success', 'Enrollment marked completed and certificate issued.');
        redirect($_SERVER['HTTP_REFERER'] ?? '/admin/training-enrollments');
    }

    // ============ Higher Studies ============
    public function higherStudies(): void {
        $pageTitle = 'Higher Studies';
        $universities = $this->db->fetchAll("SELECT * FROM universities ORDER BY ranking ASC");
        $exams = $this->db->fetchAll("SELECT * FROM entrance_exams ORDER BY exam_date ASC");
        $scholarships = $this->db->fetchAll("SELECT * FROM scholarships ORDER BY created_at DESC");
        // Student applications tab
        $studentApplications = $this->db->fetchAll(
            "SELECT hsa.*, s.first_name, s.last_name, s.branch, s.enrollment_no, u.name as university_name, u.country
             FROM higher_study_applications hsa
             JOIN students s ON hsa.student_id = s.id
             JOIN universities u ON hsa.university_id = u.id
             WHERE hsa.status != 'withdrawn'
             ORDER BY hsa.created_at DESC"
        );
        require_once VIEWS_PATH . '/admin/higher-studies.php';
    }

    public function approveHigherStudy($id): void {
        CsrfMiddleware::requireValidToken();
        $app = $this->db->fetchOne(
            "SELECT hsa.*, s.user_id, s.first_name, s.last_name, usr.email, u.name as uni_name 
             FROM higher_study_applications hsa 
             JOIN students s ON hsa.student_id = s.id 
             JOIN users usr ON s.user_id = usr.id 
             JOIN universities u ON hsa.university_id = u.id 
             WHERE hsa.id = ?",
            [$id]
        );
        if (!$app) { setFlash('danger', 'Application not found.'); redirect('/admin/higher-studies'); return; }
        $remarks = sanitize($_POST['admin_remarks'] ?? '');
        $this->db->update("UPDATE higher_study_applications SET status = 'approved', admin_remarks = ? WHERE id = ?", [$remarks, $id]);
        $this->db->insert("INSERT INTO notifications (user_id, title, message, type, category, link) VALUES (?, ?, ?, 'success', 'higher-studies', ?)",
            [$app['user_id'], 'Higher Studies Application Approved', "Your application to {$app['uni_name']} has been approved! " . ($remarks ? "Remarks: $remarks" : ''), url('/student/higher-studies')]);
        
        $emailSent = false;
        if (!empty($app['email']) && filter_var($app['email'], FILTER_VALIDATE_EMAIL)) {
            $studentName = $app['first_name'] . ' ' . $app['last_name'];
            $emailSent = Mailer::sendHigherStudyStatus($app['email'], $studentName, $app['uni_name'], 'approved', $remarks);
        }

        logActivity('approve_higher_study', 'higher_studies', "Approved application ID: $id");
        setFlash('success', 'Higher Studies application approved!');
        redirect('/admin/higher-studies');
    }

    public function rejectHigherStudy($id): void {
        CsrfMiddleware::requireValidToken();
        $app = $this->db->fetchOne(
            "SELECT hsa.*, s.user_id, s.first_name, s.last_name, usr.email, u.name as uni_name 
             FROM higher_study_applications hsa 
             JOIN students s ON hsa.student_id = s.id 
             JOIN users usr ON s.user_id = usr.id 
             JOIN universities u ON hsa.university_id = u.id 
             WHERE hsa.id = ?",
            [$id]
        );
        if (!$app) { setFlash('danger', 'Application not found.'); redirect('/admin/higher-studies'); return; }
        $remarks = sanitize($_POST['admin_remarks'] ?? '');
        $this->db->update("UPDATE higher_study_applications SET status = 'rejected', admin_remarks = ? WHERE id = ?", [$remarks, $id]);
        $this->db->insert("INSERT INTO notifications (user_id, title, message, type, category, link) VALUES (?, ?, ?, 'danger', 'higher-studies', ?)",
            [$app['user_id'], 'Higher Studies Application Rejected', "Your application to {$app['uni_name']} was not approved. " . ($remarks ? "Reason: $remarks" : ''), url('/student/higher-studies')]);

        $emailSent = false;
        if (!empty($app['email']) && filter_var($app['email'], FILTER_VALIDATE_EMAIL)) {
            $studentName = $app['first_name'] . ' ' . $app['last_name'];
            $emailSent = Mailer::sendHigherStudyStatus($app['email'], $studentName, $app['uni_name'], 'rejected', $remarks);
        }

        logActivity('reject_higher_study', 'higher_studies', "Rejected application ID: $id");
        setFlash('success', 'Higher Studies application rejected.');
        redirect('/admin/higher-studies');
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

        // Validate Meeting Link (Mandatory & must be valid URL)
        $linkRes = Validator::meetingLink($data['meeting_link'] ?? '');
        if (!$linkRes['valid']) {
            setFlash('danger', $linkRes['message']);
            redirect('/admin/interviews');
            return;
        }

        $this->db->insert("INSERT INTO interviews (student_id, company_id, job_id, round, interview_date, interview_time, mode, venue, meeting_link, instructions, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'scheduled')",
            [$app['student_id'], $app['company_id'], $app['job_id'], $data['round'] ?? 'Round 1', $data['interview_date'], $data['interview_time'], 'online', $data['venue'] ?? null, trim($data['meeting_link']), $data['instructions'] ?? null]);

        $this->jobModel->updateApplicationStatus($appId, 'interview');

        $student = $this->db->fetchOne("SELECT user_id FROM students WHERE id = ?", [$app['student_id']]);
        if ($student) {
            $this->db->insert("INSERT INTO notifications (user_id, title, message, type, category, link) VALUES (?, ?, ?, ?, ?, ?)",
                [$student['user_id'], 'Interview Scheduled by Admin', "Interview for {$app['job_title']} on " . formatDate($data['interview_date']), 'info', 'interview', url('/student/interviews')]);
        }

        setFlash('success', 'Interview scheduled successfully by Admin!');
        redirect('/admin/interviews');
    }

    public function updateInterview($id): void {
        CsrfMiddleware::requireValidToken();
        $interview = $this->db->fetchOne("SELECT * FROM interviews WHERE id = ?", [$id]);
        if (!$interview) { setFlash('danger', 'Interview not found.'); redirect('/admin/interviews'); return; }

        $data = sanitizeArray($_POST);

        // Validate Meeting Link (Mandatory & must be valid URL)
        $linkRes = Validator::meetingLink($data['meeting_link'] ?? '');
        if (!$linkRes['valid']) {
            setFlash('danger', $linkRes['message']);
            redirect('/admin/interviews');
            return;
        }

        $mode = in_array($data['mode'] ?? '', ['online', 'offline'], true) ? $data['mode'] : ($interview['mode'] ?? 'online');

        $this->db->update(
            "UPDATE interviews SET round = ?, interview_date = ?, interview_time = ?, mode = ?, venue = ?, meeting_link = ?, instructions = ?, status = 'rescheduled' WHERE id = ?",
            [
                $data['round'] ?? $interview['round'],
                $data['interview_date'] ?? $interview['interview_date'],
                $data['interview_time'] ?? $interview['interview_time'],
                $mode,
                $data['venue'] ?? null,
                trim($data['meeting_link']),
                $data['instructions'] ?? null,
                $id
            ]
        );

        $student = $this->db->fetchOne("SELECT user_id FROM students WHERE id = ?", [$interview['student_id']]);
        if ($student) {
            $this->db->insert("INSERT INTO notifications (user_id, title, message, type, category, link) VALUES (?, ?, ?, ?, ?, ?)",
                [$student['user_id'], 'Interview Rescheduled', "Your interview has been rescheduled for " . formatDate($data['interview_date']), 'info', 'interview', url('/student/interviews')]);
        }

        setFlash('success', 'Interview rescheduled successfully.');
        redirect('/admin/interviews');
    }

    public function cancelInterview($id): void {
        CsrfMiddleware::requireValidToken();
        $interview = $this->db->fetchOne("SELECT * FROM interviews WHERE id = ?", [$id]);
        if (!$interview) { setFlash('danger', 'Interview not found.'); redirect('/admin/interviews'); return; }

        $this->db->update("UPDATE interviews SET status = 'cancelled' WHERE id = ?", [$id]);

        $student = $this->db->fetchOne("SELECT user_id FROM students WHERE id = ?", [$interview['student_id']]);
        if ($student) {
            $this->db->insert("INSERT INTO notifications (user_id, title, message, type, category, link) VALUES (?, ?, ?, ?, ?, ?)",
                [$student['user_id'], 'Interview Cancelled', "Your interview scheduled for " . formatDate($interview['interview_date']) . " has been cancelled.", 'warning', 'interview', url('/student/interviews')]);
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
            $this->db->insert("INSERT INTO notifications (user_id, title, message, type, category, link) VALUES (?, ?, ?, ?, ?, ?)",
                [$student['user_id'], 'Interview Result Updated', "Result for your interview: " . ucfirst($result), $result === 'passed' ? 'success' : 'danger', 'interview', url('/student/interviews')]);
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
        $users = [];
        if ($target === 'all') {
            $this->db->insert("INSERT INTO notifications (user_id, title, message, type, category, is_global) VALUES (NULL, ?, ?, ?, 'announcement', 1)", [$data['title'], $data['message'], $data['type'] ?? 'info']);
            $users = $this->db->fetchAll("SELECT u.id, u.email, COALESCE(CONCAT(s.first_name, ' ', s.last_name), c.company_name, u.email) as name FROM users u LEFT JOIN students s ON u.id = s.user_id LEFT JOIN companies c ON u.id = c.user_id WHERE u.status = 'active'");
        } elseif ($target === 'students') {
            $users = $this->db->fetchAll("SELECT u.id, u.email, CONCAT(s.first_name, ' ', s.last_name) as name FROM users u JOIN students s ON u.id = s.user_id WHERE u.status = 'active'");
            foreach ($users as $u) { $this->db->insert("INSERT INTO notifications (user_id, title, message, type, category) VALUES (?, ?, ?, ?, 'announcement')", [$u['id'], $data['title'], $data['message'], $data['type'] ?? 'info']); }
        } elseif ($target === 'companies') {
            $users = $this->db->fetchAll("SELECT u.id, u.email, c.company_name as name FROM users u JOIN companies c ON u.id = c.user_id WHERE u.status = 'active'");
            foreach ($users as $u) { $this->db->insert("INSERT INTO notifications (user_id, title, message, type, category) VALUES (?, ?, ?, ?, 'announcement')", [$u['id'], $data['title'], $data['message'], $data['type'] ?? 'info']); }
        }
        // Send emails asynchronously (best-effort)
        foreach (array_slice($users, 0, 200) as $u) {
            if (!empty($u['email'])) {
                Mailer::sendAdminAnnouncement($u['email'], $u['name'] ?? 'User', $data['title'], $data['message']);
            }
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
        $pageTitle  = 'System Settings';
        $smtpStatus = Mailer::getSmtpStatus();
        require_once VIEWS_PATH . '/admin/settings.php';
    }

    /**
     * Send a test email to verify Brevo SMTP configuration (AJAX POST)
     */
    public function sendTestEmail(): void {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
            exit;
        }

        CsrfMiddleware::requireValidToken();

        $toEmail = $_SESSION['user_email'] ?? '';
        $toName  = $_SESSION['user_name']  ?? 'Admin';

        if (!$toEmail || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Admin email address is not configured.']);
            exit;
        }

        $ok = Mailer::sendTestEmail($toEmail, $toName);

        if ($ok) {
            echo json_encode([
                'success' => true,
                'message' => "Test email sent to {$toEmail}. Please check your inbox.",
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to send test email: ' . Mailer::getLastError(),
            ]);
        }
        exit;
    }

    // ============ Import Students ============

    /**
     * Show the Import Students wizard page (GET)
     */
    public function importStudentsPage(): void {
        $pageTitle = 'Import Students from Excel';
        require_once VIEWS_PATH . '/admin/import-students.php';
    }

    /**
     * Download a sample Excel template (.xlsx) for bulk import (GET)
     */
    public function downloadImportTemplate(): void {
        require_once ROOT_PATH . '/includes/ExcelParser.php';
        try {
            $xlsx = ExcelParser::generateTemplate();
        } catch (RuntimeException $e) {
            setFlash('danger', 'Could not generate template: ' . $e->getMessage());
            redirect('/admin/import-students');
            return;
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="TPMS_Student_Import_Template.xlsx"');
        header('Content-Length: ' . strlen($xlsx));
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        echo $xlsx;
        exit;
    }

    /**
     * Handle the AJAX Excel import POST request.
     * Returns JSON with import results.
     */
    public function doImportStudents(): void {
        // Must be POST + admin + CSRF
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Invalid request method.'], 405);
        }

        CsrfMiddleware::requireValidToken();

        require_once ROOT_PATH . '/includes/ExcelParser.php';
        require_once ROOT_PATH . '/includes/Mailer.php';

        // ── File Validation ──────────────────────────────────────────
        if (empty($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
            $errCode = $_FILES['import_file']['error'] ?? -1;
            $msg = match($errCode) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'File exceeds the maximum allowed size of 10 MB.',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
                default => 'File upload failed (error code: ' . $errCode . ').',
            };
            jsonResponse(['success' => false, 'message' => $msg]);
        }

        $file     = $_FILES['import_file'];
        $fileSize = $file['size'];
        $tmpPath  = $file['tmp_name'];
        $origName = $file['name'];
        $ext      = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

        // File size limit: 10 MB
        $maxBytes = 10 * 1024 * 1024;
        if ($fileSize > $maxBytes) {
            jsonResponse(['success' => false, 'message' => 'File size exceeds 10 MB limit.']);
        }

        // Extension whitelist
        if (!in_array($ext, ['xlsx', 'xls', 'csv'])) {
            jsonResponse(['success' => false, 'message' => 'Invalid file type. Only .xlsx, .xls, and .csv files are allowed.']);
        }

        // MIME type check (anti-malicious upload)
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $tmpPath);
        finfo_close($finfo);
        $allowedMimes = [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-excel',
            'text/csv', 'text/plain',
            'application/csv', 'application/octet-stream',
        ];
        if (!in_array($mime, $allowedMimes) && $ext !== 'csv') {
            // For Excel files, be more strict; CSV can have text/plain
            if (!str_starts_with($mime, 'application/') && !str_starts_with($mime, 'text/')) {
                jsonResponse(['success' => false, 'message' => 'File appears to be invalid or potentially malicious.']);
            }
        }

        // ── Parse File ───────────────────────────────────────────────
        try {
            $rawRows = ExcelParser::parse($tmpPath, $ext, 2000);
        } catch (RuntimeException $e) {
            jsonResponse(['success' => false, 'message' => 'Could not parse file: ' . $e->getMessage()]);
        }

        if (empty($rawRows)) {
            jsonResponse(['success' => false, 'message' => 'The file is empty or contains no data rows.']);
        }

        // Map flexible column names to canonical field names
        $rows = array_map([ExcelParser::class, 'mapHeaders'], $rawRows);

        // ── Build existing DB sets for duplicate detection ────────────
        $existingEmails = array_column(
            $this->db->fetchAll("SELECT email FROM users WHERE role = 'student'"), 'email'
        );
        $existingEmails = array_map('strtolower', $existingEmails);

        $existingPRNs = array_column(
            $this->db->fetchAll("SELECT enrollment_no FROM students WHERE enrollment_no IS NOT NULL AND enrollment_no != ''"), 'enrollment_no'
        );
        $existingPRNs = array_map('strtolower', $existingPRNs);

        $existingRegNos = array_column(
            $this->db->fetchAll("SELECT registration_no FROM students WHERE registration_no IS NOT NULL AND registration_no != ''"), 'registration_no'
        );
        $existingRegNos = array_map('strtolower', $existingRegNos);

        // ── Process each row ─────────────────────────────────────────
        $results       = [];
        $successCount  = 0;
        $skippedCount  = 0;
        $failedCount   = 0;
        $successReport = [];
        $failedReport  = [];

        // Track new duplicates within the current batch
        $batchEmails  = [];
        $batchPRNs    = [];
        $batchRegNos  = [];

        foreach ($rows as $rowIndex => $row) {
            $rowNum = $rowIndex + 2; // Excel row number (1-based, +1 for header)
            $errors = [];

            // ── Required Fields ───────────────────────────────────
            $fullName = trim($row['full_name'] ?? '');
            $email    = strtolower(trim($row['email'] ?? ''));
            $prn      = trim($row['enrollment_no'] ?? '');
            $branch   = trim($row['branch'] ?? '');

            if (empty($fullName)) $errors[] = 'Full Name is required.';
            if (empty($email))    $errors[] = 'Email is required.';
            if (empty($prn))      $errors[] = 'PRN/Roll Number is required.';
            if (empty($branch))   $errors[] = 'Branch is required.';

            // ── Email Format Validation ───────────────────────────
            if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Invalid email format: '{$email}'.";
            }

            // ── Phone Validation ──────────────────────────────────
            $phone = preg_replace('/\D/', '', trim($row['phone'] ?? ''));
            if ($phone && strlen($phone) !== 10) {
                $errors[] = "Phone number must be exactly 10 digits (got: '{$row['phone']}').";
            }

            // ── CGPA Validation ───────────────────────────────────
            $cgpa = trim($row['cgpa'] ?? '');
            if ($cgpa !== '') {
                if (!is_numeric($cgpa) || (float)$cgpa < 0 || (float)$cgpa > 10) {
                    $errors[] = "CGPA must be a number between 0 and 10 (got: '{$cgpa}').";
                } else {
                    $cgpa = (float)$cgpa;
                }
            } else {
                $cgpa = null;
            }

            // ── Gender Validation ─────────────────────────────────
            $gender = strtolower(trim($row['gender'] ?? ''));
            if ($gender && !in_array($gender, ['male', 'female', 'other'])) {
                // Try to normalize common variations
                if (in_array($gender, ['m', 'male', 'boy'])) $gender = 'male';
                elseif (in_array($gender, ['f', 'female', 'girl'])) $gender = 'female';
                else $gender = 'other';
            }
            if (!$gender) $gender = null;

            // ── Date of Birth Validation ──────────────────────────
            $dob = trim($row['dob'] ?? '');
            if ($dob) {
                // Try various formats
                $dobFormats = ['Y-m-d', 'd/m/Y', 'm/d/Y', 'd-m-Y', 'd.m.Y', 'Y/m/d'];
                $dobParsed = null;
                foreach ($dobFormats as $fmt) {
                    $dt = DateTime::createFromFormat($fmt, $dob);
                    if ($dt && $dt->format($fmt) === $dob) {
                        $dobParsed = $dt->format('Y-m-d');
                        break;
                    }
                }
                // Fallback
                if (!$dobParsed) {
                    $ts = strtotime($dob);
                    $dobParsed = $ts ? date('Y-m-d', $ts) : null;
                }
                $dob = $dobParsed;
                if (!$dob) {
                    $errors[] = "Invalid Date of Birth format. Use YYYY-MM-DD or DD/MM/YYYY.";
                }
            } else {
                $dob = null;
            }

            // ── Passing Year ──────────────────────────────────────
            $passingYear = trim($row['passing_year'] ?? '');
            if ($passingYear) {
                if (!preg_match('/^\d{4}$/', $passingYear) || (int)$passingYear < 2000 || (int)$passingYear > 2050) {
                    $errors[] = "Passing Year must be a 4-digit year between 2000 and 2050 (got: '{$passingYear}').";
                    $passingYear = null;
                } else {
                    $passingYear = (int)$passingYear;
                }
            } else {
                $passingYear = null;
            }

            // ── If validation errors, mark as failed ──────────────
            if (!empty($errors)) {
                $failedCount++;
                $results[] = [
                    'row'    => $rowNum,
                    'name'   => $fullName ?: '(unknown)',
                    'email'  => $email ?: '(unknown)',
                    'status' => 'failed',
                    'errors' => $errors,
                ];
                $failedReport[] = array_merge(['row' => $rowNum, 'status' => 'failed', 'error' => implode(' | ', $errors)], $row);
                continue;
            }

            // ── Duplicate Detection ───────────────────────────────
            $regNo = trim($row['registration_no'] ?? '');

            $isDuplicate = false;
            $dupReasons  = [];

            if (in_array($email, $existingEmails) || in_array($email, $batchEmails)) {
                $isDuplicate = true;
                $dupReasons[] = "Email '{$email}' already exists.";
            }
            if ($prn && (in_array(strtolower($prn), $existingPRNs) || in_array(strtolower($prn), $batchPRNs))) {
                $isDuplicate = true;
                $dupReasons[] = "PRN/Roll Number '{$prn}' already exists.";
            }
            if ($regNo && (in_array(strtolower($regNo), $existingRegNos) || in_array(strtolower($regNo), $batchRegNos))) {
                $isDuplicate = true;
                $dupReasons[] = "Registration Number '{$regNo}' already exists.";
            }

            if ($isDuplicate) {
                $skippedCount++;
                $results[] = [
                    'row'    => $rowNum,
                    'name'   => $fullName,
                    'email'  => $email,
                    'status' => 'skipped',
                    'errors' => $dupReasons,
                ];
                $failedReport[] = array_merge(['row' => $rowNum, 'status' => 'skipped', 'error' => implode(' | ', $dupReasons)], $row);
                continue;
            }

            // ── Split Full Name ───────────────────────────────────
            $nameParts = explode(' ', $fullName, 2);
            $firstName = $nameParts[0];
            $lastName  = $nameParts[1] ?? '';

            // ── Generate Student ID ───────────────────────────────
            $year      = date('Y');
            $lastId    = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM students") + $successCount + 1;
            $studentId = 'STU' . $year . str_pad($lastId, 5, '0', STR_PAD_LEFT);

            // ── Generate Temporary Password ───────────────────────
            $tempPassword = $this->generateTempPassword();

            // ── Insert into DB (transaction) ──────────────────────
            try {
                $this->db->beginTransaction();

                // Create user account
                $userId = $this->db->insert(
                    "INSERT INTO users (email, password, role, status, email_verified) VALUES (?, ?, 'student', 'active', 1)",
                    [$email, password_hash($tempPassword, PASSWORD_BCRYPT, ['cost' => 10])]
                );

                // Calculate profile completion (basic import data)
                $completion = 20; // Base for having name + email
                if ($phone)       $completion += 10;
                if ($branch)      $completion += 10;
                if ($cgpa)        $completion += 10;
                if ($dob)         $completion += 10;
                if ($gender)      $completion += 5;
                if (!empty($row['skills'])) $completion += 10;
                if (!empty($row['address'])) $completion += 5;
                if (!empty($row['linkedin'])) $completion += 10;
                $completion = min($completion, 85); // Cap at 85% for imports

                // Create student profile
                $this->db->insert(
                    "INSERT INTO students (
                        user_id, first_name, last_name, phone, dob, gender,
                        address, branch, enrollment_no, registration_no,
                        passing_year, cgpa, skills, linkedin, github, portfolio,
                        parent_name, parent_phone, profile_completion, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
                    [
                        $userId,
                        $firstName,
                        $lastName,
                        $phone ?: null,
                        $dob,
                        $gender,
                        trim($row['address'] ?? '') ?: null,
                        $branch,
                        $prn ?: null,
                        $regNo ?: null,
                        $passingYear,
                        $cgpa,
                        trim($row['skills'] ?? '') ?: null,
                        trim($row['linkedin'] ?? '') ?: null,
                        trim($row['github'] ?? '') ?: null,
                        trim($row['portfolio'] ?? '') ?: null,
                        trim($row['parent_name'] ?? '') ?: null,
                        preg_replace('/\D/', '', trim($row['parent_phone'] ?? '')) ?: null,
                        $completion,
                    ]
                );

                // Send welcome notification
                $this->db->insert(
                    "INSERT INTO notifications (user_id, title, message, type, category) VALUES (?, ?, ?, 'success', 'system')",
                    [$userId, 'Welcome to TPMS!', "Your student account has been created. Login with your email and temporary password to get started."]
                );

                $this->db->commit();

                // Add to batch duplicate sets
                $batchEmails[]  = $email;
                $batchPRNs[]    = strtolower($prn);
                if ($regNo) $batchRegNos[] = strtolower($regNo);

                // Send welcome email (non-blocking best-effort)
                Mailer::sendImportWelcome($email, $fullName, $studentId, $tempPassword);

                $successCount++;
                $results[] = [
                    'row'       => $rowNum,
                    'name'      => $fullName,
                    'email'     => $email,
                    'studentId' => $studentId,
                    'status'    => 'success',
                    'errors'    => [],
                ];
                $successReport[] = [
                    'row'        => $rowNum,
                    'name'       => $fullName,
                    'email'      => $email,
                    'student_id' => $studentId,
                    'branch'     => $branch,
                    'prn'        => $prn,
                    'status'     => 'imported',
                ];

            } catch (Exception $dbEx) {
                $this->db->rollback();
                $failedCount++;
                $results[] = [
                    'row'    => $rowNum,
                    'name'   => $fullName,
                    'email'  => $email,
                    'status' => 'failed',
                    'errors' => ['Database error: ' . $dbEx->getMessage()],
                ];
                $failedReport[] = array_merge([
                    'row'    => $rowNum,
                    'status' => 'db_error',
                    'error'  => $dbEx->getMessage(),
                ], $row);
            }
        }

        // ── Log activity ──────────────────────────────────────────────
        logActivity(
            'import_students',
            'students',
            "Bulk import: {$successCount} imported, {$skippedCount} skipped, {$failedCount} failed from '{$origName}'"
        );

        // ── Store reports in session for download ─────────────────────
        $_SESSION['import_success_report'] = $successReport;
        $_SESSION['import_failed_report']  = array_filter($results, fn($r) => in_array($r['status'], ['failed', 'skipped']));

        jsonResponse([
            'success'      => true,
            'totalRows'    => count($rows),
            'imported'     => $successCount,
            'skipped'      => $skippedCount,
            'failed'       => $failedCount,
            'results'      => $results,
        ]);
    }

    /**
     * Download import report as CSV (success or failed)
     */
    public function downloadImportReport(): void {
        $type = sanitize($_GET['type'] ?? 'success');

        if ($type === 'success') {
            $data    = $_SESSION['import_success_report'] ?? [];
            $headers = ['Row', 'Name', 'Email', 'Student ID', 'Branch', 'PRN', 'Status'];
            $filename = 'TPMS_Import_Success_Report_' . date('Y-m-d_H-i') . '.csv';
            $rows = array_map(fn($r) => [
                $r['row'], $r['name'], $r['email'], $r['student_id'] ?? '', $r['branch'] ?? '', $r['prn'] ?? '', $r['status']
            ], $data);
        } else {
            $data    = $_SESSION['import_failed_report'] ?? [];
            $headers = ['Row', 'Name', 'Email', 'Status', 'Errors'];
            $filename = 'TPMS_Import_Failed_Report_' . date('Y-m-d_H-i') . '.csv';
            $rows = array_map(fn($r) => [
                $r['row'], $r['name'] ?? '', $r['email'] ?? '', $r['status'], implode('; ', $r['errors'] ?? [])
            ], $data);
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, no-store, must-revalidate');

        $out = fopen('php://output', 'w');
        // BOM for Excel UTF-8 compatibility
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, $headers);
        foreach ($rows as $row) {
            fputcsv($out, $row);
        }
        fclose($out);
        exit;
    }

    /**
     * Generate a secure temporary password.
     * Format: 3 upper + 3 lower + 3 digits + 2 special = 11 chars
     */
    private function generateTempPassword(): string {
        $upper   = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        $lower   = 'abcdefghjkmnpqrstuvwxyz';
        $digits  = '23456789';
        $special = '@#$!';

        $pass  = '';
        $pass .= $upper[random_int(0, strlen($upper) - 1)];
        $pass .= $upper[random_int(0, strlen($upper) - 1)];
        $pass .= $lower[random_int(0, strlen($lower) - 1)];
        $pass .= $lower[random_int(0, strlen($lower) - 1)];
        $pass .= $digits[random_int(0, strlen($digits) - 1)];
        $pass .= $digits[random_int(0, strlen($digits) - 1)];
        $pass .= $digits[random_int(0, strlen($digits) - 1)];
        $pass .= $special[random_int(0, strlen($special) - 1)];

        // Shuffle to avoid predictable pattern
        return str_shuffle($pass);
    }
}

