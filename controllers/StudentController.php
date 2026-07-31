<?php
/**
 * TPMS - Student Controller
 */

require_once ROOT_PATH . '/models/Student.php';
require_once ROOT_PATH . '/models/User.php';
require_once ROOT_PATH . '/models/Recommendation.php';
require_once ROOT_PATH . '/includes/Mailer.php';

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
        $higherStudiesCount = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM higher_study_applications WHERE student_id = ?", [$student['id']]);

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

        // AI-recommended jobs for this student (load from cache or compute)
        $recoModel = new Recommendation();
        $aiRecommendedJobs = $recoModel->getTopJobsForStudent($student['id'], 4);
        // If no cached scores exist yet, compute them now (first-time load)
        if (empty($aiRecommendedJobs)) {
            try {
                $recoModel->recomputeForStudent($student['id']);
                $aiRecommendedJobs = $recoModel->getTopJobsForStudent($student['id'], 4);
            } catch (\Throwable $e) { $aiRecommendedJobs = []; }
        }

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
        $errors = [];

        // Phone — required
        $rawPhone = trim($_POST['phone'] ?? '');
        if (!empty($rawPhone)) {
            $phoneResult = Validator::phone($rawPhone);
            if (!$phoneResult['valid']) $errors[] = $phoneResult['message'];
        }

        // Pincode — optional but must be 6 digits if provided
        $rawPincode = trim($_POST['pincode'] ?? '');
        $pincodeResult = Validator::pincode($rawPincode);
        if (!$pincodeResult['valid']) $errors[] = $pincodeResult['message'];

        // City — optional
        $rawCity = trim($_POST['city'] ?? '');
        $cityResult = Validator::city($rawCity);
        if (!$cityResult['valid']) $errors[] = $cityResult['message'];

        // State — optional
        $rawState = trim($_POST['state'] ?? '');
        $stateResult = Validator::state($rawState);
        if (!$stateResult['valid']) $errors[] = $stateResult['message'];

        // Address — optional
        $rawAddress = trim($_POST['address'] ?? '');
        $addressResult = Validator::address($rawAddress);
        if (!$addressResult['valid']) $errors[] = $addressResult['message'];

        // Skills — optional, max 300 chars, deduped
        $rawSkills = trim($_POST['skills'] ?? '');
        $skillsResult = Validator::skills($rawSkills);
        if (!$skillsResult['valid']) $errors[] = $skillsResult['message'];

        // Bio — optional, min 20 if provided
        $rawBio = trim($_POST['bio'] ?? '');
        $bioResult = Validator::bio($rawBio);
        if (!$bioResult['valid']) $errors[] = $bioResult['message'];

        // LinkedIn — optional URL
        $rawLinkedin = trim($_POST['linkedin'] ?? '');
        $linkedinResult = Validator::optionalUrl($rawLinkedin, 'LinkedIn URL');
        if (!$linkedinResult['valid']) $errors[] = $linkedinResult['message'];

        // GitHub — optional URL
        $rawGithub = trim($_POST['github'] ?? '');
        $githubResult = Validator::optionalUrl($rawGithub, 'GitHub URL');
        if (!$githubResult['valid']) $errors[] = $githubResult['message'];

        // Portfolio — optional URL
        $rawPortfolio = trim($_POST['portfolio'] ?? '');
        $portfolioResult = Validator::optionalUrl($rawPortfolio, 'Portfolio URL');
        if (!$portfolioResult['valid']) $errors[] = $portfolioResult['message'];

        if (!empty($errors)) {
            setFlash('danger', implode('<br>', $errors));
            redirect('/student/profile/edit');
            return;
        }

        $updateData = [
            'first_name' => $data['first_name'] ?? $this->student['first_name'],
            'last_name'  => $data['last_name']  ?? $this->student['last_name'],
            'phone'      => $rawPhone ?: null,
            'dob'        => $data['dob'] ?: null,
            'gender'     => $data['gender'] ?: null,
            'address'    => $rawAddress ?: null,
            'city'       => $rawCity ?: null,
            'state'      => $rawState ?: null,
            'pincode'    => $rawPincode ?: null,
            'branch'     => $data['branch'] ?? $this->student['branch'],
            'enrollment_no'       => $data['enrollment_no'] ?? $this->student['enrollment_no'],
            'admission_year'      => $data['admission_year'] ?: null,
            'passing_year'        => $data['passing_year'] ?: null,
            'tenth_percentage'    => $data['tenth_percentage'] ?: null,
            'twelfth_percentage'  => $data['twelfth_percentage'] ?: null,
            'diploma_percentage'  => $data['diploma_percentage'] ?: null,
            'degree'     => $data['degree'] ?? 'B.Tech',
            'cgpa'       => $data['cgpa'] ?: null,
            'backlogs'   => $data['backlogs'] ?? 0,
            'active_backlogs' => $data['active_backlogs'] ?? 0,
            'skills'     => $skillsResult['normalized'] ?: null,
            'bio'        => $rawBio ?: null,
            'linkedin'   => $rawLinkedin ?: null,
            'github'     => $rawGithub ?: null,
            'portfolio'  => $rawPortfolio ?: null,
        ];

        // Validate CGPA
        if ($updateData['cgpa'] !== null && ($updateData['cgpa'] < 0 || $updateData['cgpa'] > 10)) {
            setFlash('danger', 'CGPA must be between 0 and 10.');
            redirect('/student/profile/edit');
            return;
        }

        $this->studentModel->updateByUserId($_SESSION['user_id'], $updateData);
        $this->studentModel->updateProfileCompletion($this->student['id']);
        // Refresh AI recommendations after profile change
        try { (new Recommendation())->recomputeForStudent($this->student['id']); } catch (\Throwable $e) { /* non-fatal */ }
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

        // --- File type validation (extension + MIME) ---
        $allowedExtensions = ['pdf', 'doc', 'docx'];
        $allowedMimes      = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];
        $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $mime = mime_content_type($file['tmp_name']);

        if (!in_array($ext, $allowedExtensions, true) || !in_array($mime, $allowedMimes, true)) {
            setFlash('danger', 'Invalid file type. Only PDF, DOC, and DOCX files are allowed.');
            redirect('/student/profile/edit');
            return;
        }
        // ------------------------------------------------

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
            [$_SESSION['user_id'], $docType, $file['name'], 'documents/' . $fileName, $file['size'], $mime, sanitize($_POST['document_description'] ?? '')]);

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

    public function aiJobs(): void {
        $student = $this->student;
        $pageTitle = 'AI Job Matches & Recommendations';

        $search   = sanitize($_GET['search'] ?? '');
        $type     = sanitize($_GET['type'] ?? '');
        $minScore = (float)($_GET['min_score'] ?? 0);

        $filters = [
            'search'    => $search,
            'job_type'  => $type,
            'min_score' => $minScore
        ];

        $recoModel = new Recommendation();

        if (isset($_GET['refresh'])) {
            $recoModel->recomputeForStudent($student['id']);
            setFlash('success', 'AI Job Recommendations refreshed successfully!');
            redirect('/student/ai-jobs');
            return;
        }

        $recommendations = $recoModel->getAllJobRecommendationsForStudent($student['id'], $filters);

        require_once VIEWS_PATH . '/student/ai-jobs.php';
    }

    public function viewJob($jobId): void {
        $student = $this->student;
        if (!$jobId) { redirect('/student/jobs'); return; }

        $job = $this->db->fetchOne(
            "SELECT j.*, c.company_name, c.logo, c.website, c.description as company_description, c.industry, c.city as company_city,
                    (SELECT COUNT(*) FROM applications a WHERE a.job_id = j.id) as total_applications,
                    (SELECT COUNT(*) FROM applications a WHERE a.job_id = j.id AND a.student_id = ?) as has_applied,
                    (SELECT COUNT(*) FROM bookmarks b WHERE b.job_id = j.id AND b.student_id = ?) as is_bookmarked
             FROM jobs j JOIN companies c ON j.company_id = c.id
             WHERE j.id = ?",
            [$student['id'], $student['id'], $jobId]
        );

        if (!$job) {
            setFlash('danger', 'Job posting not found or unavailable.');
            redirect('/student/jobs');
            return;
        }

        $pageTitle = $job['title'] . ' — ' . $job['company_name'];

        $recoModel = new Recommendation();
        $aiMatch = $recoModel->computeScore($student, $job);

        require_once VIEWS_PATH . '/student/view-job.php';
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

        if ($job['eligibility_backlogs'] < $this->student['active_backlogs']) {
            setFlash('danger', 'You have more active backlogs than allowed.'); redirect('/student/jobs'); return;
        }

        $this->db->insert("INSERT INTO applications (student_id, job_id, status, resume_snapshot) VALUES (?, ?, 'applied', ?)",
            [$this->student['id'], $jobId, $this->student['resume_path']]);

        // 1. Student In-app Notification & Confirmation Email
        $this->db->insert("INSERT INTO notifications (user_id, title, message, type, category, link) VALUES (?, ?, ?, ?, ?, ?)",
            [$_SESSION['user_id'], 'Application Submitted', "You have successfully applied for {$job['title']}.", 'success', 'job', url('/student/applications')]);

        $userEmail = $this->db->fetchOne("SELECT email FROM users WHERE id = ?", [$_SESSION['user_id']]);
        $emailSent = false;
        if ($userEmail && filter_var($userEmail['email'], FILTER_VALIDATE_EMAIL)) {
            $emailSent = Mailer::sendJobApplication(
                $userEmail['email'],
                $this->student['first_name'] . ' ' . $this->student['last_name'],
                $job['title'],
                $job['company_name'] ?? 'the company'
            );
        }

        // 2. Company HR In-app Notification & New Application Email (Student -> Company)
        $companyUser = $this->db->fetchOne(
            "SELECT u.id as user_id, u.email, c.company_name, c.contact_person FROM companies c JOIN users u ON c.user_id = u.id WHERE c.id = ?",
            [$job['company_id']]
        );
        if ($companyUser) {
            $studentFullName = $this->student['first_name'] . ' ' . $this->student['last_name'];
            $this->db->insert(
                "INSERT INTO notifications (user_id, title, message, type, category, link) VALUES (?, ?, ?, 'info', 'job', ?)",
                [
                    $companyUser['user_id'],
                    'New Candidate Application 📥',
                    "{$studentFullName} applied for '{$job['title']}'.",
                    url('/company/applications/' . $job['id'])
                ]
            );
            if (!empty($companyUser['email']) && filter_var($companyUser['email'], FILTER_VALIDATE_EMAIL)) {
                Mailer::sendCompanyNewApplication(
                    $companyUser['email'],
                    $companyUser['company_name'],
                    $studentFullName,
                    $this->student['branch'] ?? 'N/A',
                    $job['title']
                );
            }
        }

        logActivity('apply_job', 'application', "Applied for job: {$job['title']}");
        
        $msg = 'Application submitted successfully!';
        if (isAjax()) { jsonResponse(['success' => true, 'message' => $msg]); }
        setFlash('success', $msg);
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
            "SELECT a.*, j.title as job_title, j.salary_min, j.salary_max, j.location, j.job_type, c.company_name, c.logo, c.user_id as company_user_id
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

        // Email confirmation
        $userEmail = $this->db->fetchOne("SELECT email FROM users WHERE id = ?", [$_SESSION['user_id']]);
        if ($userEmail) {
            Mailer::sendTrainingRegistered(
                $userEmail['email'],
                $this->student['first_name'] . ' ' . $this->student['last_name'],
                $training['title'],
                $training['start_date']
            );
        }

        setFlash('success', 'Registered for training successfully!');
        redirect('/student/trainings');
    }

    public function higherStudies(): void {
        $pageTitle = 'Higher Studies';
        $student = $this->student;
        $universities = $this->db->fetchAll("SELECT u.*, (SELECT COUNT(*) FROM courses c WHERE c.university_id = u.id) as course_count FROM universities u WHERE u.status = 'active' ORDER BY u.ranking ASC");
        // Load courses per university for apply modals
        $coursesByUniversity = [];
        foreach ($universities as $u) {
            $coursesByUniversity[$u['id']] = $this->db->fetchAll("SELECT id, name, degree_type, duration FROM courses WHERE university_id = ? AND status = 'active' ORDER BY name", [$u['id']]);
        }
        $exams = $this->db->fetchAll("SELECT * FROM entrance_exams WHERE status = 'active' ORDER BY exam_date ASC");
        $scholarships = $this->db->fetchAll("SELECT * FROM scholarships WHERE status = 'active' ORDER BY application_deadline ASC");
        $myApplications = $this->db->fetchAll(
            "SELECT hsa.*, u.name as university_name, u.country, u.city, c.name as course_name
             FROM higher_study_applications hsa
             JOIN universities u ON hsa.university_id = u.id
             LEFT JOIN courses c ON hsa.course_id = c.id
             WHERE hsa.student_id = ? ORDER BY hsa.created_at DESC",
            [$student['id']]
        );
        // Set of university IDs this student already applied to
        $appliedUnivIds = array_column($myApplications, 'university_id');
        require_once VIEWS_PATH . '/student/higher-studies.php';
    }

    public function applyHigherStudy(): void {
        CsrfMiddleware::requireValidToken();
        $data = sanitizeArray($_POST);
        $univId = (int)($data['university_id'] ?? 0);
        if (!$univId) { setFlash('danger', 'Please select a university.'); redirect('/student/higher-studies'); return; }

        $uni = $this->db->fetchOne("SELECT * FROM universities WHERE id = ? AND status = 'active'", [$univId]);
        if (!$uni) { setFlash('danger', 'University not found.'); redirect('/student/higher-studies'); return; }

        // Prevent duplicate
        $existing = $this->db->fetchColumn("SELECT COUNT(*) FROM higher_study_applications WHERE student_id = ? AND university_id = ? AND status != 'withdrawn'", [$this->student['id'], $univId]);
        if ($existing) { setFlash('warning', 'You have already applied to this university.'); redirect('/student/higher-studies'); return; }

        $this->db->insert(
            "INSERT INTO higher_study_applications (student_id, university_id, course_id, university_name, country, course_name, entrance_exam, exam_score, application_date, expected_joining_date, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)",
            [
                $this->student['id'],
                $univId,
                $data['course_id'] ?: null,
                $uni['name'],
                $uni['country'],
                $data['course_name'] ?? null,
                $data['entrance_exam'] ?? null,
                $data['exam_score'] ?? null,
                $data['application_date'] ?: null,
                $data['expected_joining_date'] ?: null,
                $data['notes'] ?? null
            ]
        );

        $this->db->insert("INSERT INTO notifications (user_id, title, message, type, category, link) VALUES (?, ?, ?, 'info', 'higher-studies', ?)",
            [$_SESSION['user_id'], 'Higher Studies Application Submitted', "Your application to {$uni['name']} has been submitted and is under review.", url('/student/higher-studies')]);

        logActivity('apply_higher_study', 'higher_studies', "Applied to: {$uni['name']}");
        setFlash('success', 'Application submitted successfully! The admin will review it shortly.');
        redirect('/student/higher-studies');
    }

    public function editHigherStudy($id): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('/student/higher-studies'); return; }
        CsrfMiddleware::requireValidToken();
        $app = $this->db->fetchOne("SELECT * FROM higher_study_applications WHERE id = ? AND student_id = ?", [$id, $this->student['id']]);
        if (!$app) { setFlash('danger', 'Application not found.'); redirect('/student/higher-studies'); return; }
        if ($app['status'] !== 'pending') { setFlash('warning', 'Only pending applications can be edited.'); redirect('/student/higher-studies'); return; }

        $data = sanitizeArray($_POST);
        $this->db->update(
            "UPDATE higher_study_applications SET course_id = ?, course_name = ?, entrance_exam = ?, exam_score = ?, application_date = ?, expected_joining_date = ?, notes = ? WHERE id = ?",
            [
                $data['course_id'] ?: null,
                $data['course_name'] ?? null,
                $data['entrance_exam'] ?? null,
                $data['exam_score'] ?? null,
                $data['application_date'] ?: null,
                $data['expected_joining_date'] ?: null,
                $data['notes'] ?? null,
                $id
            ]
        );
        setFlash('success', 'Application updated successfully!');
        redirect('/student/higher-studies');
    }

    public function withdrawHigherStudy($id): void {
        $app = $this->db->fetchOne("SELECT * FROM higher_study_applications WHERE id = ? AND student_id = ?", [$id, $this->student['id']]);
        if (!$app) { setFlash('danger', 'Application not found.'); redirect('/student/higher-studies'); return; }
        if (!in_array($app['status'], ['pending'])) { setFlash('warning', 'Only pending applications can be withdrawn.'); redirect('/student/higher-studies'); return; }
        $this->db->update("UPDATE higher_study_applications SET status = 'withdrawn' WHERE id = ?", [$id]);
        setFlash('success', 'Application withdrawn.');
        redirect('/student/higher-studies');
    }

    public function withdrawTraining($id): void {
        $reg = $this->db->fetchOne("SELECT * FROM training_registrations WHERE id = ? AND student_id = ?", [$id, $this->student['id']]);
        if (!$reg) { setFlash('danger', 'Registration not found.'); redirect('/student/trainings'); return; }
        if (!in_array($reg['status'], ['registered'])) { setFlash('warning', 'Only pending registrations can be withdrawn.'); redirect('/student/trainings'); return; }
        $this->db->update("UPDATE training_registrations SET status = 'dropped' WHERE id = ?", [$id]);
        $this->db->update("UPDATE trainings SET registered_count = GREATEST(0, registered_count - 1) WHERE id = ?", [$reg['training_id']]);
        setFlash('success', 'Training registration withdrawn.');
        redirect('/student/trainings');
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
            "SELECT i.*, j.title as job_title, j.location as job_location, j.job_type, j.work_mode, j.salary_min, j.salary_max,
                    c.company_name, c.logo, c.website as company_website, c.contact_email, c.contact_phone
             FROM interviews i 
             JOIN jobs j ON i.job_id = j.id 
             JOIN companies c ON i.company_id = c.id
             WHERE i.student_id = ? 
             ORDER BY i.interview_date ASC, i.interview_time ASC",
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
        $errors = [];

        // Title required
        $titleResult = Validator::text($data['title'] ?? '', 'Project title', 2, 150, true);
        if (!$titleResult['valid']) $errors[] = $titleResult['message'];

        // Project URL — required, must be valid http/https
        $rawUrl = trim($_POST['project_url'] ?? '');
        $urlResult = Validator::projectUrl($rawUrl);
        if (!$urlResult['valid']) $errors[] = $urlResult['message'];

        // Technologies — optional, max 300 chars
        if (!empty($data['technologies']) && strlen($data['technologies']) > 300) {
            $errors[] = 'Technologies must not exceed 300 characters.';
        }

        // Description — optional, max 1000 chars
        if (!empty($data['description']) && strlen($data['description']) > 1000) {
            $errors[] = 'Project description must not exceed 1000 characters.';
        }

        if (!empty($errors)) {
            setFlash('danger', implode('<br>', $errors));
            redirect('/student/profile/edit');
            return;
        }

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
        $errors = [];

        $titleResult = Validator::text($data['title'] ?? '', 'Certificate title', 2, 150, true);
        if (!$titleResult['valid']) $errors[] = $titleResult['message'];

        // Credential URL — optional but must be valid if provided
        $rawCredUrl = trim($_POST['credential_url'] ?? '');
        $credUrlResult = Validator::optionalUrl($rawCredUrl, 'Credential URL');
        if (!$credUrlResult['valid']) $errors[] = $credUrlResult['message'];

        if (!empty($errors)) {
            setFlash('danger', implode('<br>', $errors));
            redirect('/student/profile/edit');
            return;
        }

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
        $errors = [];

        // Validate language name
        $langResult = Validator::languageName($data['language'] ?? '');
        if (!$langResult['valid']) $errors[] = $langResult['message'];

        // Prevent duplicates
        if ($langResult['valid']) {
            $existing = $this->db->fetchColumn(
                "SELECT COUNT(*) FROM student_languages WHERE student_id = ? AND LOWER(language) = LOWER(?)",
                [$this->student['id'], trim($data['language'] ?? '')]
            );
            if ($existing > 0) {
                $errors[] = 'You have already added "' . htmlspecialchars(trim($data['language'])) . '" as a language.';
            }
        }

        if (!empty($errors)) {
            setFlash('danger', implode('<br>', $errors));
            redirect('/student/profile/edit');
            return;
        }

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
        $errors = [];

        $titleResult = Validator::text($data['title'] ?? '', 'Achievement title', 2, 150, true);
        if (!$titleResult['valid']) $errors[] = $titleResult['message'];

        $descResult = Validator::achievement($data['description'] ?? '');
        if (!$descResult['valid']) $errors[] = $descResult['message'];

        if (!empty($errors)) {
            setFlash('danger', implode('<br>', $errors));
            redirect('/student/profile/edit');
            return;
        }

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
