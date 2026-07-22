<?php
/**
 * TPMS - Student Controller
 */

require_once ROOT_PATH . '/models/Student.php';
require_once ROOT_PATH . '/models/User.php';

class StudentController {
    private Student $studentModel;
    private User $userModel;
    private Database $db;
    private ?array $student;

    public function __construct() {
        $this->studentModel = new Student();
        $this->userModel = new User();
        $this->db = Database::getInstance();
        $this->student = $this->studentModel->findByUserId($_SESSION['user_id']);
    }

    public function dashboard(): void {
        $student = $this->student;
        $pageTitle = 'Student Dashboard';

        // Stats
        $applicationCount = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM applications WHERE student_id = ?", [$student['id']]);
        $shortlistedCount = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM applications WHERE student_id = ? AND status = 'shortlisted'", [$student['id']]);
        $selectedCount = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM applications WHERE student_id = ? AND status = 'selected'", [$student['id']]);
        $interviewCount = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM interviews WHERE student_id = ? AND status = 'scheduled'", [$student['id']]);
        $trainingCount = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM training_registrations WHERE student_id = ?", [$student['id']]);
        $bookmarkCount = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM bookmarks WHERE student_id = ?", [$student['id']]);

        // Recent jobs
        $recentJobs = $this->db->fetchAll(
            "SELECT j.*, c.company_name, c.logo FROM jobs j JOIN companies c ON j.company_id = c.id WHERE j.status = 'active' AND (j.application_deadline IS NULL OR j.application_deadline >= CURDATE()) ORDER BY j.created_at DESC LIMIT 5"
        );

        // Recent notifications
        $notifications = $this->db->fetchAll(
            "SELECT * FROM notifications WHERE (user_id = ? OR is_global = 1) ORDER BY created_at DESC LIMIT 5",
            [$_SESSION['user_id']]
        );

        // Upcoming interviews
        $upcomingInterviews = $this->db->fetchAll(
            "SELECT i.*, j.title as job_title, c.company_name FROM interviews i JOIN jobs j ON i.job_id = j.id JOIN companies c ON i.company_id = c.id WHERE i.student_id = ? AND i.status = 'scheduled' ORDER BY i.interview_date ASC LIMIT 3",
            [$student['id']]
        );

        require_once VIEWS_PATH . '/student/dashboard.php';
    }

    public function profile(): void {
        $student = $this->student;
        $pageTitle = 'My Profile';
        $projects = $this->studentModel->getProjects($student['id']);
        $certifications = $this->studentModel->getCertifications($student['id']);
        $languages = $this->studentModel->getLanguages($student['id']);
        $achievements = $this->studentModel->getAchievements($student['id']);
        $documents = $this->db->fetchAll("SELECT * FROM documents WHERE user_id = ? ORDER BY created_at DESC", [$_SESSION['user_id']]);
        require_once VIEWS_PATH . '/student/profile.php';
    }

    public function editProfile(): void {
        $student = $this->student;
        $pageTitle = 'Edit Profile';
        $projects = $this->studentModel->getProjects($student['id']);
        $certifications = $this->studentModel->getCertifications($student['id']);
        $languages = $this->studentModel->getLanguages($student['id']);
        $achievements = $this->studentModel->getAchievements($student['id']);
        require_once VIEWS_PATH . '/student/edit-profile.php';
    }

    public function updateProfile(): void {
        CsrfMiddleware::requireValidToken();
        $data = sanitizeArray($_POST);
        $updateData = [
            'first_name' => $data['first_name'] ?? $this->student['first_name'],
            'last_name' => $data['last_name'] ?? $this->student['last_name'],
            'phone' => $data['phone'] ?? $this->student['phone'],
            'dob' => $data['dob'] ?: null,
            'gender' => $data['gender'] ?: null,
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'state' => $data['state'] ?? null,
            'pincode' => $data['pincode'] ?? null,
            'branch' => $data['branch'] ?? $this->student['branch'],
            'enrollment_no' => $data['enrollment_no'] ?? $this->student['enrollment_no'],
            'admission_year' => $data['admission_year'] ?: null,
            'passing_year' => $data['passing_year'] ?: null,
            'tenth_percentage' => $data['tenth_percentage'] ?: null,
            'twelfth_percentage' => $data['twelfth_percentage'] ?: null,
            'diploma_percentage' => $data['diploma_percentage'] ?: null,
            'degree' => $data['degree'] ?? 'B.Tech',
            'cgpa' => $data['cgpa'] ?: null,
            'backlogs' => $data['backlogs'] ?? 0,
            'active_backlogs' => $data['active_backlogs'] ?? 0,
            'skills' => $data['skills'] ?? null,
            'bio' => $data['bio'] ?? null,
            'linkedin' => $data['linkedin'] ?? null,
            'github' => $data['github'] ?? null,
            'portfolio' => $data['portfolio'] ?? null,
        ];

        // Validate CGPA
        if ($updateData['cgpa'] !== null && ($updateData['cgpa'] < 0 || $updateData['cgpa'] > 10)) {
            setFlash('danger', 'CGPA must be between 0 and 10.');
            redirect('/student/profile/edit');
            return;
        }

        $this->studentModel->updateByUserId($_SESSION['user_id'], $updateData);
        $this->studentModel->updateProfileCompletion($this->student['id']);
        logActivity('update_profile', 'student', 'Student updated profile');
        setFlash('success', 'Profile updated successfully!');
        redirect('/student/profile');
    }

    public function uploadPhoto(): void {
        CsrfMiddleware::requireValidToken();
        if (!isset($_FILES['profile_photo']) || $_FILES['profile_photo']['error'] !== UPLOAD_ERR_OK) {
            setFlash('danger', 'Please select a valid image file.');
            redirect('/student/profile/edit');
            return;
        }

        $file = $_FILES['profile_photo'];
        $mime = mime_content_type($file['tmp_name']);
        if (!in_array($mime, ALLOWED_IMAGE_TYPES)) {
            setFlash('danger', 'Invalid image format. Allowed: JPG, PNG, GIF, WEBP');
            redirect('/student/profile/edit');
            return;
        }
        if ($file['size'] > MAX_FILE_SIZE) {
            setFlash('danger', 'File size exceeds 5MB limit.');
            redirect('/student/profile/edit');
            return;
        }

        // Delete old photo
        if ($this->student['profile_photo']) {
            $oldPath = UPLOADS_PATH . '/profile_photos/' . $this->student['profile_photo'];
            if (file_exists($oldPath)) unlink($oldPath);
        }

        $fileName = generateFileName($file['name'], 'student_' . $this->student['id']);
        $destination = UPLOADS_PATH . '/profile_photos/' . $fileName;
        move_uploaded_file($file['tmp_name'], $destination);

        $this->studentModel->updateByUserId($_SESSION['user_id'], ['profile_photo' => $fileName]);
        $this->studentModel->updateProfileCompletion($this->student['id']);
        setFlash('success', 'Profile photo updated successfully!');
        redirect('/student/profile/edit');
    }

    public function uploadResume(): void {
        CsrfMiddleware::requireValidToken();
        if (!isset($_FILES['resume']) || $_FILES['resume']['error'] !== UPLOAD_ERR_OK) {
            setFlash('danger', 'Please select a PDF file.');
            redirect('/student/profile/edit');
            return;
        }

        $file = $_FILES['resume'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'pdf') {
            setFlash('danger', 'Only PDF files are allowed for resume.');
            redirect('/student/profile/edit');
            return;
        }
        if ($file['size'] > MAX_FILE_SIZE) {
            setFlash('danger', 'File size exceeds 5MB limit.');
            redirect('/student/profile/edit');
            return;
        }

        // Delete old resume
        if ($this->student['resume_path']) {
            $oldPath = UPLOADS_PATH . '/resume/' . $this->student['resume_path'];
            if (file_exists($oldPath)) unlink($oldPath);
        }

        $fileName = generateFileName($file['name'], 'resume_' . $this->student['id']);
        $destination = UPLOADS_PATH . '/resume/' . $fileName;
        move_uploaded_file($file['tmp_name'], $destination);

        $this->studentModel->updateByUserId($_SESSION['user_id'], [
            'resume_path' => $fileName,
            'resume_original_name' => $file['name']
        ]);
        $this->studentModel->updateProfileCompletion($this->student['id']);
        setFlash('success', 'Resume uploaded successfully!');
        redirect('/student/profile/edit');
    }

    public function deleteResume(): void {
        if ($this->student['resume_path']) {
            $path = UPLOADS_PATH . '/resume/' . $this->student['resume_path'];
            if (file_exists($path)) unlink($path);
            $this->studentModel->updateByUserId($_SESSION['user_id'], ['resume_path' => null, 'resume_original_name' => null]);
            setFlash('success', 'Resume deleted.');
        }
        redirect('/student/profile/edit');
    }

    public function downloadResume(): void {
        if ($this->student['resume_path']) {
            $path = UPLOADS_PATH . '/resume/' . $this->student['resume_path'];
            if (file_exists($path)) {
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="' . ($this->student['resume_original_name'] ?: 'resume.pdf') . '"');
                header('Content-Length: ' . filesize($path));
                readfile($path);
                exit;
            }
        }
        setFlash('danger', 'Resume not found.');
        redirect('/student/profile');
    }

    public function previewResume(): void {
        if ($this->student['resume_path']) {
            $path = UPLOADS_PATH . '/resume/' . $this->student['resume_path'];
            if (file_exists($path)) {
                header('Content-Type: application/pdf');
                header('Content-Disposition: inline; filename="' . ($this->student['resume_original_name'] ?: 'resume.pdf') . '"');
                readfile($path);
                exit;
            }
        }
        setFlash('danger', 'Resume not found.');
        redirect('/student/profile');
    }

    public function uploadDocument(): void {
        CsrfMiddleware::requireValidToken();
        if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
            setFlash('danger', 'Please select a file.');
            redirect('/student/profile/edit');
            return;
        }

        $file = $_FILES['document'];
        if ($file['size'] > MAX_FILE_SIZE) {
            setFlash('danger', 'File size exceeds 5MB limit.');
            redirect('/student/profile/edit');
            return;
        }

        $fileName = generateFileName($file['name'], 'doc_' . $this->student['id']);
        $destination = UPLOADS_PATH . '/documents/' . $fileName;
        move_uploaded_file($file['tmp_name'], $destination);

        $docType = sanitize($_POST['document_type'] ?? 'other');
        $this->db->insert("INSERT INTO documents (user_id, document_type, original_name, file_path, file_size, mime_type, description) VALUES (?, ?, ?, ?, ?, ?, ?)",
            [$_SESSION['user_id'], $docType, $file['name'], 'documents/' . $fileName, $file['size'], mime_content_type($file['tmp_name']), sanitize($_POST['document_description'] ?? '')]);

        setFlash('success', 'Document uploaded successfully!');
        redirect('/student/profile/edit');
    }

    public function deleteDocument($id): void {
        $doc = $this->db->fetchOne("SELECT * FROM documents WHERE id = ? AND user_id = ?", [$id, $_SESSION['user_id']]);
        if ($doc) {
            $path = UPLOADS_PATH . '/' . $doc['file_path'];
            if (file_exists($path)) unlink($path);
            $this->db->delete("DELETE FROM documents WHERE id = ?", [$id]);
            setFlash('success', 'Document deleted.');
        }
        redirect('/student/profile/edit');
    }

    public function jobs(): void {
        $pageTitle = 'Browse Jobs';
        $student = $this->student;
        $search = sanitize($_GET['search'] ?? '');
        $type = sanitize($_GET['type'] ?? '');
        $location = sanitize($_GET['location'] ?? '');
        $page = max(1, (int)($_GET['page'] ?? 1));

        $where = "j.status = 'active' AND (j.application_deadline IS NULL OR j.application_deadline >= CURDATE()) AND c.is_approved = 1";
        $params = [];

        if ($search) { $where .= " AND (j.title LIKE ? OR c.company_name LIKE ? OR j.skills_required LIKE ?)"; $params = array_merge($params, ["%$search%", "%$search%", "%$search%"]); }
        if ($type) { $where .= " AND j.job_type = ?"; $params[] = $type; }
        if ($location) { $where .= " AND j.location LIKE ?"; $params[] = "%$location%"; }

        $totalJobs = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM jobs j JOIN companies c ON j.company_id = c.id WHERE $where", $params);
        $pagination = getPagination($totalJobs, $page);

        $params[] = $pagination['per_page'];
        $params[] = $pagination['offset'];

        $jobs = $this->db->fetchAll(
            "SELECT j.*, c.company_name, c.logo, c.city as company_city,
             (SELECT COUNT(*) FROM applications a WHERE a.job_id = j.id) as application_count,
             (SELECT COUNT(*) FROM applications a WHERE a.job_id = j.id AND a.student_id = ?) as has_applied,
             (SELECT COUNT(*) FROM bookmarks b WHERE b.job_id = j.id AND b.student_id = ?) as is_bookmarked
             FROM jobs j JOIN companies c ON j.company_id = c.id WHERE $where ORDER BY j.created_at DESC LIMIT ? OFFSET ?",
            array_merge([$student['id'], $student['id']], $params)
        );

        require_once VIEWS_PATH . '/student/jobs.php';
    }

    public function applyJob($jobId): void {
        if (!$jobId) { redirect('/student/jobs'); return; }

        $job = $this->db->fetchOne("SELECT * FROM jobs WHERE id = ? AND status = 'active'", [$jobId]);
        if (!$job) { setFlash('danger', 'Job not found or closed.'); redirect('/student/jobs'); return; }

        // Check already applied
        $existing = $this->db->fetchColumn("SELECT COUNT(*) FROM applications WHERE student_id = ? AND job_id = ?", [$this->student['id'], $jobId]);
        if ($existing) { setFlash('warning', 'You have already applied for this job.'); redirect('/student/jobs'); return; }

        // Check eligibility
        if ($job['eligibility_cgpa'] > 0 && $this->student['cgpa'] < $job['eligibility_cgpa']) {
            setFlash('danger', 'You do not meet the CGPA requirement for this job.'); redirect('/student/jobs'); return;
        }

        if ($job['eligibility_branches']) {
            $branches = array_map('trim', explode(',', $job['eligibility_branches']));
            if (!in_array($this->student['branch'], $branches)) {
                setFlash('danger', 'Your branch is not eligible for this job.'); redirect('/student/jobs'); return;
            }
        }

        if ($job['eligibility_backlogs'] < $this->student['active_backlogs']) {
            setFlash('danger', 'You have more active backlogs than allowed.'); redirect('/student/jobs'); return;
        }

        $this->db->insert("INSERT INTO applications (student_id, job_id, status, resume_snapshot) VALUES (?, ?, 'applied', ?)",
            [$this->student['id'], $jobId, $this->student['resume_path']]);

        // Notification
        $this->db->insert("INSERT INTO notifications (user_id, title, message, type, category) VALUES (?, ?, ?, ?, ?)",
            [$_SESSION['user_id'], 'Application Submitted', "You have successfully applied for {$job['title']}.", 'success', 'job']);

        logActivity('apply_job', 'application', "Applied for job: {$job['title']}");
        if (isAjax()) { jsonResponse(['success' => true, 'message' => 'Application submitted successfully!']); }
        setFlash('success', 'Application submitted successfully!');
        redirect('/student/jobs');
    }

    public function withdrawApplication($appId): void {
        $this->db->update("UPDATE applications SET status = 'withdrawn' WHERE id = ? AND student_id = ? AND status = 'applied'",
            [$appId, $this->student['id']]);
        setFlash('success', 'Application withdrawn.');
        redirect('/student/applications');
    }

    public function applications(): void {
        $pageTitle = 'My Applications';
        $student = $this->student;
        $applications = $this->db->fetchAll(
            "SELECT a.*, j.title as job_title, j.salary_min, j.salary_max, j.location, j.job_type, c.company_name, c.logo
             FROM applications a JOIN jobs j ON a.job_id = j.id JOIN companies c ON j.company_id = c.id
             WHERE a.student_id = ? ORDER BY a.applied_at DESC",
            [$student['id']]
        );
        require_once VIEWS_PATH . '/student/applications.php';
    }

    public function trainings(): void {
        $pageTitle = 'Trainings';
        $student = $this->student;
        $trainings = $this->db->fetchAll(
            "SELECT t.*, f.name as faculty_name,
             (SELECT COUNT(*) FROM training_registrations tr WHERE tr.training_id = t.id AND tr.student_id = ?) as is_registered
             FROM trainings t LEFT JOIN faculty f ON t.faculty_id = f.id WHERE t.status IN ('upcoming', 'ongoing') ORDER BY t.start_date ASC",
            [$student['id']]
        );
        $myTrainings = $this->db->fetchAll(
            "SELECT tr.*, t.title, t.start_date, t.end_date, t.status as training_status, t.trainer_name
             FROM training_registrations tr JOIN trainings t ON tr.training_id = t.id WHERE tr.student_id = ? ORDER BY t.start_date DESC",
            [$student['id']]
        );
        require_once VIEWS_PATH . '/student/trainings.php';
    }

    public function registerTraining($trainingId): void {
        if (!$trainingId) { redirect('/student/trainings'); return; }
        $training = $this->db->fetchOne("SELECT * FROM trainings WHERE id = ?", [$trainingId]);
        if (!$training) { setFlash('danger', 'Training not found.'); redirect('/student/trainings'); return; }
        if ($training['registered_count'] >= $training['capacity']) { setFlash('danger', 'Training is full.'); redirect('/student/trainings'); return; }

        $existing = $this->db->fetchColumn("SELECT COUNT(*) FROM training_registrations WHERE training_id = ? AND student_id = ?", [$trainingId, $this->student['id']]);
        if ($existing) { setFlash('warning', 'Already registered.'); redirect('/student/trainings'); return; }

        $this->db->insert("INSERT INTO training_registrations (training_id, student_id) VALUES (?, ?)", [$trainingId, $this->student['id']]);
        $this->db->update("UPDATE trainings SET registered_count = registered_count + 1 WHERE id = ?", [$trainingId]);
        setFlash('success', 'Registered for training successfully!');
        redirect('/student/trainings');
    }

    public function higherStudies(): void {
        $pageTitle = 'Higher Studies';
        $student = $this->student;
        $universities = $this->db->fetchAll("SELECT u.*, (SELECT COUNT(*) FROM courses c WHERE c.university_id = u.id) as course_count FROM universities u WHERE u.status = 'active' ORDER BY u.ranking ASC");
        $exams = $this->db->fetchAll("SELECT * FROM entrance_exams WHERE status = 'active' ORDER BY exam_date ASC");
        $scholarships = $this->db->fetchAll("SELECT * FROM scholarships WHERE status = 'active' ORDER BY application_deadline ASC");
        $myApplications = $this->db->fetchAll(
            "SELECT hsa.*, u.name as university_name, u.country, c.name as course_name
             FROM higher_study_applications hsa JOIN universities u ON hsa.university_id = u.id LEFT JOIN courses c ON hsa.course_id = c.id
             WHERE hsa.student_id = ? ORDER BY hsa.created_at DESC",
            [$student['id']]
        );
        require_once VIEWS_PATH . '/student/higher-studies.php';
    }

    public function registerHigherStudy(): void {
        CsrfMiddleware::requireValidToken();
        $data = sanitizeArray($_POST);
        $this->db->insert("INSERT INTO higher_study_applications (student_id, university_id, course_id, exam_score, status, notes) VALUES (?, ?, ?, ?, 'interested', ?)",
            [$this->student['id'], $data['university_id'], $data['course_id'] ?: null, $data['exam_score'] ?? null, $data['notes'] ?? null]);
        setFlash('success', 'Interest registered successfully!');
        redirect('/student/higher-studies');
    }

    public function notifications(): void {
        $pageTitle = 'Notifications';
        $notifications = $this->db->fetchAll(
            "SELECT * FROM notifications WHERE user_id = ? OR is_global = 1 ORDER BY created_at DESC LIMIT 50",
            [$_SESSION['user_id']]
        );
        require_once VIEWS_PATH . '/student/notifications.php';
    }

    public function markNotificationRead($id): void {
        $this->db->update("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?", [$id, $_SESSION['user_id']]);
        if (isAjax()) { jsonResponse(['success' => true]); }
        redirect('/student/notifications');
    }

    public function interviews(): void {
        $pageTitle = 'Interview Schedule';
        $student = $this->student;
        $interviews = $this->db->fetchAll(
            "SELECT i.*, j.title as job_title, c.company_name, c.logo
             FROM interviews i JOIN jobs j ON i.job_id = j.id JOIN companies c ON i.company_id = c.id
             WHERE i.student_id = ? ORDER BY i.interview_date DESC",
            [$student['id']]
        );
        require_once VIEWS_PATH . '/student/interviews.php';
    }

    public function bookmarks(): void {
        $pageTitle = 'Bookmarked Jobs';
        $student = $this->student;
        $bookmarks = $this->db->fetchAll(
            "SELECT b.id as bookmark_id, b.created_at as bookmarked_at, j.*, j.status as job_status,
                    c.company_name, c.logo, c.city as company_city,
                    (SELECT COUNT(*) FROM applications a WHERE a.job_id = j.id AND a.student_id = ?) as has_applied
             FROM bookmarks b
             JOIN jobs j ON b.job_id = j.id
             JOIN companies c ON j.company_id = c.id
             WHERE b.student_id = ? ORDER BY b.created_at DESC",
            [$student['id'], $student['id']]
        );
        require_once VIEWS_PATH . '/student/bookmarks.php';
    }

    public function toggleBookmark($jobId): void {
        if (!$jobId) {
            if (isAjax()) jsonResponse(['success' => false, 'message' => 'Invalid job ID']);
            redirect('/student/jobs');
            return;
        }
        $existing = $this->db->fetchColumn("SELECT COUNT(*) FROM bookmarks WHERE student_id = ? AND job_id = ?", [$this->student['id'], $jobId]);
        if ($existing) {
            $this->db->delete("DELETE FROM bookmarks WHERE student_id = ? AND job_id = ?", [$this->student['id'], $jobId]);
            if (isAjax()) {
                jsonResponse(['success' => true, 'bookmarked' => false, 'message' => 'Bookmark removed successfully']);
            }
            setFlash('info', 'Bookmark removed.');
        } else {
            $this->db->insert("INSERT INTO bookmarks (student_id, job_id) VALUES (?, ?)", [$this->student['id'], $jobId]);
            if (isAjax()) {
                jsonResponse(['success' => true, 'bookmarked' => true, 'message' => 'Job bookmarked successfully']);
            }
            setFlash('success', 'Job bookmarked.');
        }
        $referer = $_SERVER['HTTP_REFERER'] ?? url('/student/jobs');
        redirect($referer);
    }

    public function changePasswordPage(): void {
        $pageTitle = 'Change Password';
        require_once VIEWS_PATH . '/student/change-password.php';
    }

    public function changePassword(): void {
        CsrfMiddleware::requireValidToken();
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        $user = $this->userModel->findById($_SESSION['user_id']);
        if (!password_verify($current, $user['password'])) { setFlash('danger', 'Current password is incorrect.'); redirect('/student/change-password'); return; }
        if (!isStrongPassword($new)) { setFlash('danger', 'New password must be at least 8 chars with uppercase, lowercase, number, and special char.'); redirect('/student/change-password'); return; }
        if ($new !== $confirm) { setFlash('danger', 'Passwords do not match.'); redirect('/student/change-password'); return; }

        $this->userModel->updatePassword($_SESSION['user_id'], $new);
        logActivity('change_password', 'auth', 'Password changed');
        setFlash('success', 'Password changed successfully!');
        redirect('/student/change-password');
    }

    // Project, Certification, Language, Achievement CRUD via AJAX
    public function addProject(): void {
        CsrfMiddleware::requireValidToken();
        $data = sanitizeArray($_POST);
        if (empty($data['title'])) { setFlash('danger', 'Project title is required.'); redirect('/student/profile/edit'); return; }
        $this->studentModel->addProject($this->student['id'], $data);
        setFlash('success', 'Project added!');
        redirect('/student/profile/edit');
    }

    public function deleteProject($id): void {
        $this->studentModel->deleteProject($id, $this->student['id']);
        setFlash('success', 'Project deleted.');
        redirect('/student/profile/edit');
    }

    public function addCertification(): void {
        CsrfMiddleware::requireValidToken();
        $data = sanitizeArray($_POST);
        if (empty($data['title'])) { setFlash('danger', 'Certificate title is required.'); redirect('/student/profile/edit'); return; }
        $this->studentModel->addCertification($this->student['id'], $data);
        setFlash('success', 'Certification added!');
        redirect('/student/profile/edit');
    }

    public function deleteCertification($id): void {
        $this->studentModel->deleteCertification($id, $this->student['id']);
        setFlash('success', 'Certification deleted.');
        redirect('/student/profile/edit');
    }

    public function addLanguage(): void {
        CsrfMiddleware::requireValidToken();
        $data = sanitizeArray($_POST);
        if (empty($data['language'])) { setFlash('danger', 'Language is required.'); redirect('/student/profile/edit'); return; }
        $this->studentModel->addLanguage($this->student['id'], $data);
        setFlash('success', 'Language added!');
        redirect('/student/profile/edit');
    }

    public function deleteLanguage($id): void {
        $this->studentModel->deleteLanguage($id, $this->student['id']);
        setFlash('success', 'Language deleted.');
        redirect('/student/profile/edit');
    }

    public function addAchievement(): void {
        CsrfMiddleware::requireValidToken();
        $data = sanitizeArray($_POST);
        if (empty($data['title'])) { setFlash('danger', 'Achievement title is required.'); redirect('/student/profile/edit'); return; }
        $this->studentModel->addAchievement($this->student['id'], $data);
        setFlash('success', 'Achievement added!');
        redirect('/student/profile/edit');
    }

    public function deleteAchievement($id): void {
        $this->studentModel->deleteAchievement($id, $this->student['id']);
        setFlash('success', 'Achievement deleted.');
        redirect('/student/profile/edit');
    }
}
