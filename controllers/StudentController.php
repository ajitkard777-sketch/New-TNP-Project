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
        $this->student = isset($_SESSION['user_id']) ? $this->studentModel->findByUserId((int)$_SESSION['user_id']) : null;
    }

    public function dashboard(): void {
        $student = $this->student;
        $pageTitle = 'Student Dashboard';

        // Stats for Top Summary Cards
        $jobsAvailableCount = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM jobs WHERE status = 'active' AND (application_deadline IS NULL OR application_deadline >= CURDATE())");
        $applicationCount = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM applications WHERE student_id = ?", [$student['id']]);
        
        $activeJobs = $this->db->fetchAll("SELECT * FROM jobs WHERE status = 'active' AND (application_deadline IS NULL OR application_deadline >= CURDATE())");
        $eligibleJobsCount = 0;
        foreach ($activeJobs as $j) {
            $check = checkStudentJobEligibility($j, $student);
            if ($check['is_eligible']) {
                $eligibleJobsCount++;
            }
        }

        $interviewCount = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM interviews WHERE student_id = ? AND (status = 'scheduled' OR status = 'rescheduled') AND interview_date >= CURDATE()", [$student['id']]);
        $selectedCount = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM applications WHERE student_id = ? AND status = 'selected'", [$student['id']]);
        $notificationCount = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM notifications WHERE (user_id = ? OR is_global = 1)", [$_SESSION['user_id']]);

        // Specific Stats for Dashboard Integration Cards
        $upcomingInterviewsCount = $interviewCount;
        $upcomingDrivesCount = $jobsAvailableCount;
        $totalAchievementsCount = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM student_achievements WHERE student_id = ?", [$student['id']]);

        $firstDayOfMonth = date('Y-m-01');
        $lastDayOfMonth = date('Y-m-t');
        $eventsThisMonth = $this->studentModel->getPlacementCalendarEvents($firstDayOfMonth, $lastDayOfMonth, $student['id']);
        $eventsThisMonthCount = count($eventsThisMonth);

        // Student Data Collections for Cards
        $projects = $this->studentModel->getProjects($student['id']);
        $certifications = $this->studentModel->getCertifications($student['id']);
        $languages = $this->studentModel->getLanguages($student['id']);
        $achievements = $this->studentModel->getAchievements($student['id']);

        // AI Recommended Jobs
        require_once ROOT_PATH . '/services/JobRecommendationService.php';
        $recommendedJobs = JobRecommendationService::getInstance()->getRecommendedJobs($student, 6);

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
            'preferred_location' => $data['preferred_location'] ?? null,
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
            if (file_exists($oldPath)) @unlink($oldPath);
        }

        $dir = UPLOADS_PATH . '/profile_photos/';
        if (!is_dir($dir)) @mkdir($dir, 0777, true);

        $fileName = generateFileName($file['name'], 'student_' . $this->student['id']);
        $destination = $dir . $fileName;
        move_uploaded_file($file['tmp_name'], $destination);

        $this->studentModel->updateByUserId($_SESSION['user_id'], ['profile_photo' => $fileName]);
        $this->studentModel->updateProfileCompletion($this->student['id']);
        setFlash('success', 'Profile photo updated successfully!');
        redirectBack();
    }

    public function uploadResume(): void {
        CsrfMiddleware::requireValidToken();
        if (!isset($_FILES['resume']) || $_FILES['resume']['error'] !== UPLOAD_ERR_OK) {
            setFlash('danger', 'Please select a PDF file to upload.');
            redirectBack();
            return;
        }

        $file = $_FILES['resume'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'pdf') {
            setFlash('danger', 'Only PDF files are allowed for resume.');
            redirectBack();
            return;
        }
        if ($file['size'] > MAX_FILE_SIZE) {
            setFlash('danger', 'File size exceeds 5MB limit.');
            redirectBack();
            return;
        }

        $dir = UPLOADS_PATH . '/resume/';
        if (!is_dir($dir)) @mkdir($dir, 0777, true);

        // Delete old resume
        if ($this->student['resume_path']) {
            $oldPath = $dir . $this->student['resume_path'];
            if (file_exists($oldPath)) @unlink($oldPath);
        }

        $fileName = generateFileName($file['name'], 'resume_' . $this->student['id']);
        $destination = $dir . $fileName;
        move_uploaded_file($file['tmp_name'], $destination);

        $this->studentModel->updateByUserId($_SESSION['user_id'], [
            'resume_path' => $fileName,
            'resume_original_name' => $file['name']
        ]);
        $this->studentModel->updateProfileCompletion($this->student['id']);
        setFlash('success', 'Resume uploaded successfully!');
        redirectBack();
    }

    public function deleteResume(): void {
        if ($this->student['resume_path']) {
            $path = UPLOADS_PATH . '/resume/' . $this->student['resume_path'];
            if (file_exists($path)) @unlink($path);
            $this->studentModel->updateByUserId($_SESSION['user_id'], ['resume_path' => null, 'resume_original_name' => null]);
            setFlash('success', 'Resume deleted.');
        }
        redirectBack();
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
            setFlash('danger', 'Please select a document file.');
            redirectBack();
            return;
        }

        $file = $_FILES['document'];
        if ($file['size'] > MAX_FILE_SIZE) {
            setFlash('danger', 'File size exceeds 5MB limit.');
            redirectBack();
            return;
        }

        $dir = UPLOADS_PATH . '/documents/';
        if (!is_dir($dir)) @mkdir($dir, 0777, true);

        $fileName = generateFileName($file['name'], 'doc_' . $this->student['id']);
        $destination = $dir . $fileName;
        move_uploaded_file($file['tmp_name'], $destination);

        $docType = sanitize($_POST['document_type'] ?? 'other');
        $this->db->insert("INSERT INTO documents (user_id, document_type, original_name, file_path, file_size, mime_type, description) VALUES (?, ?, ?, ?, ?, ?, ?)",
            [$_SESSION['user_id'], $docType, $file['name'], 'documents/' . $fileName, $file['size'], mime_content_type($destination), sanitize($_POST['document_description'] ?? '')]);

        setFlash('success', 'Document uploaded successfully!');
        redirectBack();
    }

    public function deleteDocument($id): void {
        $doc = $this->db->fetchOne("SELECT * FROM documents WHERE id = ? AND user_id = ?", [$id, $_SESSION['user_id']]);
        if ($doc) {
            $path = UPLOADS_PATH . '/' . $doc['file_path'];
            if (file_exists($path)) unlink($path);
            $this->db->delete("DELETE FROM documents WHERE id = ?", [$id]);
            setFlash('success', 'Document deleted.');
        }
        redirectBack();
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
            "SELECT j.*, c.company_name, c.logo, c.city as company_city, c.user_id as company_user_id, c.is_approved as company_approved,
             (SELECT COUNT(*) FROM applications a WHERE a.job_id = j.id) as application_count,
             (SELECT COUNT(*) FROM applications a WHERE a.job_id = j.id AND a.student_id = ?) as has_applied,
             (SELECT COUNT(*) FROM bookmarks b WHERE b.job_id = j.id AND b.student_id = ?) as is_bookmarked
             FROM jobs j JOIN companies c ON j.company_id = c.id WHERE $where ORDER BY j.created_at DESC LIMIT ? OFFSET ?",
            array_merge([$student['id'], $student['id']], $params)
        );

        // Calculate AI recommendation match scores and mandatory eligibility for all jobs
        require_once ROOT_PATH . '/services/JobRecommendationService.php';
        $recService = JobRecommendationService::getInstance();

        foreach ($jobs as &$j) {
            $match = $recService->calculateMatch($student, $j);
            $eligibility = $recService->checkEligibility($student, $j);

            $j['match_score']       = $match['score'];
            $j['match_label']       = $match['match_label'];
            $j['match_badge_class'] = $match['badge_class'];
            $j['match_explanation'] = $match['explanation'];
            $j['matched_skills']    = $match['matched_skills'];
            $j['eligibility']        = $eligibility;
        }
        unset($j);

        // Sort jobs by match score descending (Top recommended at top)
        usort($jobs, function ($a, $b) {
            return $b['match_score'] <=> $a['match_score'];
        });

        require_once VIEWS_PATH . '/student/jobs.php';
    }

    public function applyJob($jobId): void {
        if (!$jobId) { redirect('/student/jobs'); return; }

        $job = $this->db->fetchOne("SELECT j.*, c.company_name, c.user_id as company_user_id FROM jobs j JOIN companies c ON j.company_id = c.id WHERE j.id = ?", [$jobId]);
        if (!$job) { setFlash('danger', 'Job not found or closed.'); redirect('/student/jobs'); return; }

        // Check already applied
        $existingApp = $this->db->fetchOne("SELECT * FROM applications WHERE student_id = ? AND job_id = ?", [$this->student['id'], $jobId]);
        if ($existingApp) {
            if ($existingApp['status'] === 'withdrawn') {
                $this->db->update("UPDATE applications SET status = 'applied', applied_at = CURRENT_TIMESTAMP, resume_snapshot = ? WHERE id = ?",
                    [$this->student['resume_path'], $existingApp['id']]);
                
                $this->db->insert("INSERT INTO notifications (user_id, title, message, type, category) VALUES (?, ?, ?, ?, ?)",
                    [$_SESSION['user_id'], 'Application Re-submitted', "Your application for '{$job['title']}' at {$job['company_name']} has been re-submitted.", 'success', 'job']);

                if (!empty($job['company_user_id'])) {
                    $studentName = trim(($this->student['first_name'] ?? '') . ' ' . ($this->student['last_name'] ?? ''));
                    $this->db->insert("INSERT INTO notifications (user_id, title, message, type, category) VALUES (?, ?, ?, ?, ?)",
                        [$job['company_user_id'], 'Student Re-applied', "{$studentName} has re-applied for '{$job['title']}'.", 'info', 'application']);
                }

                logActivity('reapply_job', 'application', "Re-applied for job: {$job['title']}");
                if (isAjax()) { jsonResponse(['success' => true, 'message' => 'Application re-submitted successfully!']); }
                setFlash('success', 'Application re-submitted successfully!');
                redirect('/student/applications');
                return;
            } else {
                if (isAjax()) { jsonResponse(['success' => false, 'message' => 'You have already applied for this job.']); }
                setFlash('warning', 'You have already applied for this job.');
                redirect('/student/applications');
                return;
            }
        }

        // Strict Server-Side Eligibility Validation
        require_once ROOT_PATH . '/services/JobRecommendationService.php';
        $recService = JobRecommendationService::getInstance();
        $eligibility = $recService->checkEligibility($this->student, $job);

        if (!$eligibility['is_eligible']) {
            $msg = 'Not Eligible: ' . implode(' | ', $eligibility['reasons']);
            if (isAjax()) { jsonResponse(['success' => false, 'message' => $msg], 400); }
            setFlash('danger', $msg);
            redirect('/student/jobs');
            return;
        }

        // Save application
        $this->db->insert("INSERT INTO applications (student_id, job_id, status, resume_snapshot) VALUES (?, ?, 'applied', ?)",
            [$this->student['id'], $jobId, $this->student['resume_path']]);

        // Student Notification
        $this->db->insert("INSERT INTO notifications (user_id, title, message, type, category) VALUES (?, ?, ?, ?, ?)",
            [$_SESSION['user_id'], 'Application Submitted Successfully', "Your application for '{$job['title']}' at {$job['company_name']} has been submitted.", 'success', 'job']);

        // Company Recruiter Notification
        if (!empty($job['company_user_id'])) {
            $studentName = trim(($this->student['first_name'] ?? '') . ' ' . ($this->student['last_name'] ?? ''));
            $this->db->insert("INSERT INTO notifications (user_id, title, message, type, category) VALUES (?, ?, ?, ?, ?)",
                [$job['company_user_id'], 'New Student Applied', "{$studentName} has applied for '{$job['title']}'.", 'info', 'application']);
        }

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
            "SELECT a.*, j.title as job_title, j.salary_min, j.salary_max, j.location, j.job_type, j.status as job_status, j.description as job_description, j.skills_required, j.work_mode,
                    c.company_name, c.logo, c.user_id as company_user_id,
                    i.interview_date, i.interview_time, i.status as interview_status, i.round as interview_round
             FROM applications a
             JOIN jobs j ON a.job_id = j.id
             JOIN companies c ON j.company_id = c.id
             LEFT JOIN interviews i ON i.student_id = a.student_id AND i.job_id = a.job_id
             WHERE a.student_id = ?
             ORDER BY a.applied_at DESC",
            [$student['id']]
        );
        require_once VIEWS_PATH . '/student/applications.php';
    }

    public function trainings(): void {
        $pageTitle = 'Trainings';
        $student = $this->student;
        $trainings = $this->db->fetchAll(
            "SELECT t.*, f.name as faculty_name,
             (SELECT COUNT(*) FROM training_registrations tr WHERE tr.training_id = t.id AND tr.student_id = ? AND tr.status != 'cancelled') as is_registered
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
        $trainingId = (int)$trainingId;
        if (!$trainingId) {
            if (isAjax()) { jsonResponse(['success' => false, 'message' => 'Invalid training program.'], 400); }
            redirect('/student/trainings');
            return;
        }

        $training = $this->db->fetchOne("SELECT * FROM trainings WHERE id = ?", [$trainingId]);
        if (!$training) {
            if (isAjax()) { jsonResponse(['success' => false, 'message' => 'Training program not found.'], 404); }
            setFlash('danger', 'Training not found.');
            redirect('/student/trainings');
            return;
        }

        if ((int)$training['registered_count'] >= (int)$training['capacity']) {
            if (isAjax()) { jsonResponse(['success' => false, 'message' => 'Training capacity is full.'], 400); }
            setFlash('danger', 'Training is full.');
            redirect('/student/trainings');
            return;
        }

        $activeReg = $this->db->fetchOne(
            "SELECT * FROM training_registrations WHERE training_id = ? AND student_id = ? AND status != 'cancelled'",
            [$trainingId, $this->student['id']]
        );
        if ($activeReg) {
            if (isAjax()) { jsonResponse(['success' => false, 'message' => 'You are already registered for this training program.'], 400); }
            setFlash('warning', 'Already registered for this training program.');
            redirect('/student/trainings');
            return;
        }

        $cancelledReg = $this->db->fetchOne(
            "SELECT * FROM training_registrations WHERE training_id = ? AND student_id = ? AND status = 'cancelled'",
            [$trainingId, $this->student['id']]
        );

        if ($cancelledReg) {
            $this->db->update(
                "UPDATE training_registrations SET status = 'registered', created_at = CURRENT_TIMESTAMP WHERE id = ?",
                [$cancelledReg['id']]
            );
        } else {
            $this->db->insert(
                "INSERT INTO training_registrations (training_id, student_id, status) VALUES (?, ?, 'registered')",
                [$trainingId, $this->student['id']]
            );
        }

        $this->db->update(
            "UPDATE trainings SET registered_count = registered_count + 1 WHERE id = ?",
            [$trainingId]
        );

        // Send Notification to Student
        $startDateFormatted = formatDate($training['start_date'], 'd M Y');
        $trainerName = !empty($training['trainer_name']) ? $training['trainer_name'] : 'T&P Cell';
        $notifTitle = 'Training Registration Confirmed';
        $notifMsg = '✅ Successfully registered for "' . $training['title'] . '". Trainer: ' . $trainerName . '. Starts on ' . $startDateFormatted . '.';

        $notifCreated = createNotification(
            $_SESSION['user_id'],
            $notifTitle,
            $notifMsg,
            'success',
            'training',
            '/student/trainings',
            false
        );

        if (!$notifCreated) {
            error_log("Training Notification Error: Failed to create registration notification for user #" . $_SESSION['user_id'] . " training #" . $trainingId);
        }

        $successMsg = 'Successfully registered for "' . $training['title'] . '". Starts on ' . $startDateFormatted . '.';

        if (isAjax()) {
            jsonResponse([
                'success'     => true,
                'message'     => $successMsg,
                'training_id' => $trainingId
            ]);
        }

        setFlash('success', $successMsg);
        redirect('/student/trainings');
    }

    public function cancelTraining($trainingId): void {
        $trainingId = (int)$trainingId;
        if (!$trainingId) {
            if (isAjax()) { jsonResponse(['success' => false, 'message' => 'Invalid training program.'], 400); }
            redirect('/student/trainings');
            return;
        }

        $reg = $this->db->fetchOne(
            "SELECT tr.*, t.title, t.start_date, t.registered_count 
             FROM training_registrations tr 
             JOIN trainings t ON tr.training_id = t.id 
             WHERE tr.training_id = ? AND tr.student_id = ? AND tr.status != 'cancelled'",
            [$trainingId, $this->student['id']]
        );

        if (!$reg) {
            if (isAjax()) { jsonResponse(['success' => false, 'message' => 'Active registration not found.'], 404); }
            setFlash('danger', 'Active registration not found.');
            redirect('/student/trainings');
            return;
        }

        $today = date('Y-m-d');
        if ($today >= $reg['start_date']) {
            $msg = 'Training already started. Cancellation not allowed.';
            if (isAjax()) { jsonResponse(['success' => false, 'message' => $msg], 400); }
            setFlash('danger', $msg);
            redirect('/student/trainings');
            return;
        }

        $this->db->update(
            "UPDATE training_registrations SET status = 'cancelled' WHERE id = ?",
            [$reg['id']]
        );

        $this->db->update(
            "UPDATE trainings SET registered_count = GREATEST(0, registered_count - 1) WHERE id = ?",
            [$trainingId]
        );

        $notifCreated = createNotification(
            $_SESSION['user_id'],
            'Training Registration Cancelled',
            'You have cancelled your registration for "' . $reg['title'] . '".',
            'warning',
            'training',
            '/student/trainings',
            false
        );

        if (!$notifCreated) {
            error_log("Training Notification Error: Failed to create cancellation notification for user #" . $_SESSION['user_id'] . " training #" . $trainingId);
        }

        $msg = 'Training registration cancelled successfully.';
        if (isAjax()) {
            jsonResponse([
                'success'     => true,
                'message'     => $msg,
                'training_id' => $trainingId
            ]);
        }

        setFlash('success', $msg);
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

    // Project, Certification, Language, Achievement CRUD
    public function addProject(): void {
        CsrfMiddleware::requireValidToken();
        $data = sanitizeArray($_POST);
        if (empty($data['title'])) { setFlash('danger', 'Project title is required.'); redirectBack(); return; }
        $this->studentModel->addProject($this->student['id'], $data);
        $this->studentModel->updateProfileCompletion($this->student['id']);
        setFlash('success', 'Project added successfully!');
        redirectBack();
    }

    public function deleteProject($id): void {
        $this->studentModel->deleteProject($id, $this->student['id']);
        $this->studentModel->updateProfileCompletion($this->student['id']);
        setFlash('success', 'Project deleted.');
        redirectBack();
    }

    public function addCertification(): void {
        CsrfMiddleware::requireValidToken();
        $data = sanitizeArray($_POST);
        if (empty($data['title'])) { setFlash('danger', 'Certificate title is required.'); redirectBack(); return; }
        $this->studentModel->addCertification($this->student['id'], $data);
        $this->studentModel->updateProfileCompletion($this->student['id']);
        setFlash('success', 'Certification added successfully!');
        redirectBack();
    }

    public function deleteCertification($id): void {
        $this->studentModel->deleteCertification($id, $this->student['id']);
        $this->studentModel->updateProfileCompletion($this->student['id']);
        setFlash('success', 'Certification deleted.');
        redirectBack();
    }

    public function addLanguage(): void {
        CsrfMiddleware::requireValidToken();
        $data = sanitizeArray($_POST);
        if (empty($data['language'])) { setFlash('danger', 'Language is required.'); redirectBack(); return; }
        $this->studentModel->addLanguage($this->student['id'], $data);
        $this->studentModel->updateProfileCompletion($this->student['id']);
        setFlash('success', 'Language added!');
        redirectBack();
    }

    public function deleteLanguage($id): void {
        $this->studentModel->deleteLanguage($id, $this->student['id']);
        $this->studentModel->updateProfileCompletion($this->student['id']);
        setFlash('success', 'Language deleted.');
        redirectBack();
    }

    public function addAchievement(): void {
        CsrfMiddleware::requireValidToken();
        $data = sanitizeArray($_POST);
        if (empty($data['title'])) { setFlash('danger', 'Achievement title is required.'); redirectBack(); return; }
        $this->studentModel->addAchievement($this->student['id'], $data);
        $this->studentModel->updateProfileCompletion($this->student['id']);
        setFlash('success', 'Achievement added!');
        redirectBack();
    }

    public function deleteAchievement($id): void {
        $this->studentModel->deleteAchievement($id, $this->student['id']);
        $this->studentModel->updateProfileCompletion($this->student['id']);
        setFlash('success', 'Achievement deleted.');
        redirectBack();
    }

    public function companies(): void {
        $student = $this->student;
        $pageTitle = 'Companies Directory';
        $search = sanitize($_GET['search'] ?? '');
        
        $where = "c.is_approved = 1";
        $params = [];
        if ($search) {
            $where .= " AND (c.company_name LIKE ? OR c.industry LIKE ? OR c.city LIKE ?)";
            $params = ["%$search%", "%$search%", "%$search%"];
        }
        
        $companies = $this->db->fetchAll("
            SELECT c.*, u.email as hr_email,
                   (SELECT COUNT(*) FROM jobs j WHERE j.company_id = c.id AND j.status = 'active') as open_jobs_count
            FROM companies c
            LEFT JOIN users u ON c.user_id = u.id
            WHERE $where
            ORDER BY c.company_name ASC
        ", $params);

        require_once VIEWS_PATH . '/student/companies.php';
    }

    public function mockTests(): void {
        $student = $this->student;
        $pageTitle = 'Mock Tests & Practice';
        $tests = $this->db->fetchAll("SELECT t.*, (SELECT COUNT(*) FROM mock_test_questions q WHERE q.test_id = t.id) as question_count FROM mock_tests t ORDER BY t.id ASC");
        $results = $this->db->fetchAll(
            "SELECT r.*, t.title as test_title, t.category
             FROM mock_test_results r
             JOIN mock_tests t ON r.test_id = t.id
             WHERE r.student_id = ?
             ORDER BY r.submitted_at DESC",
            [$student['id']]
        );
        require_once VIEWS_PATH . '/student/mock-tests.php';
    }

    public function startMockTest($testId): void {
        $student = $this->student;
        $test = $this->db->fetchOne("SELECT * FROM mock_tests WHERE id = ?", [$testId]);
        if (!$test) {
            setFlash('danger', 'Mock test not found.');
            redirect('/student/mock-tests');
            return;
        }

        $questions = $this->db->fetchAll("SELECT * FROM mock_test_questions WHERE test_id = ? ORDER BY id ASC", [$testId]);
        $pageTitle = $test['title'];
        require_once VIEWS_PATH . '/student/mock-test-session.php';
    }

    public function submitMockTest($testId): void {
        $student = $this->student;
        $test = $this->db->fetchOne("SELECT * FROM mock_tests WHERE id = ?", [$testId]);
        if (!$test) {
            if (isAjax()) { jsonResponse(['success' => false, 'message' => 'Mock test not found.'], 404); }
            redirect('/student/mock-tests');
            return;
        }

        $questions = $this->db->fetchAll("SELECT * FROM mock_test_questions WHERE test_id = ?", [$testId]);
        $userAnswers = $_POST['answers'] ?? [];
        $timeTaken = (int)($_POST['time_taken'] ?? 0);

        $totalQuestions = count($questions);
        $correct = 0;
        $wrong = 0;
        $unanswered = 0;

        foreach ($questions as $q) {
            $qid = $q['id'];
            if (!isset($userAnswers[$qid]) || $userAnswers[$qid] === '' || $userAnswers[$qid] === null) {
                $unanswered++;
            } elseif (strtolower($userAnswers[$qid]) === strtolower($q['correct_option'])) {
                $correct++;
            } else {
                $wrong++;
            }
        }

        $score = $correct * 1;
        $percentage = $totalQuestions > 0 ? round(($correct / $totalQuestions) * 100, 2) : 0.00;

        $resultId = $this->db->insert(
            "INSERT INTO mock_test_results (student_id, test_id, score, total_questions, correct_answers, wrong_answers, unanswered, percentage, time_taken_seconds) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [$student['id'], $testId, $score, $totalQuestions, $correct, $wrong, $unanswered, $percentage, $timeTaken]
        );

        $_SESSION['last_mock_answers_' . $resultId] = $userAnswers;

        if (isAjax()) {
            jsonResponse(['success' => true, 'redirect' => url('/student/mock-test-result/' . $resultId)]);
        } else {
            setFlash('success', 'Mock test submitted successfully!');
            redirect('/student/mock-test-result/' . $resultId);
        }
    }

    public function mockTestResult($resultId): void {
        $student = $this->student;
        $result = $this->db->fetchOne(
            "SELECT r.*, t.title as test_title, t.category, t.duration_minutes
             FROM mock_test_results r
             JOIN mock_tests t ON r.test_id = t.id
             WHERE r.id = ? AND r.student_id = ?",
            [$resultId, $student['id']]
        );

        if (!$result) {
            setFlash('danger', 'Test result not found.');
            redirect('/student/mock-tests');
            return;
        }

        $questions = $this->db->fetchAll("SELECT * FROM mock_test_questions WHERE test_id = ? ORDER BY id ASC", [$result['test_id']]);
        $userAnswers = $_SESSION['last_mock_answers_' . $resultId] ?? [];
        $pageTitle = 'Mock Test Results — ' . $result['test_title'];

        require_once VIEWS_PATH . '/student/mock-test-result.php';
    }

    public function resumeBuilder(): void {
        $student = $this->student;
        $pageTitle = 'Enterprise ATS Resume Generator';
        $projects = $this->studentModel->getProjects($student['id']);
        
        $uploadedCerts = $this->studentModel->getCertificates($student['id']);
        $legacyCerts = $this->studentModel->getCertifications($student['id']);
        
        // Merge certificates into unified array
        $certifications = [];
        foreach ($uploadedCerts as $uc) {
            $certifications[] = [
                'title' => $uc['name'],
                'issuing_org' => $uc['issuing_organization'] ?? '',
                'issue_date' => $uc['issue_date'],
                'credential_id' => $uc['credential_id'],
                'credential_url' => $uc['credential_url']
            ];
        }
        foreach ($legacyCerts as $lc) {
            $certifications[] = [
                'title' => $lc['title'],
                'issuing_org' => $lc['issuing_org'] ?? '',
                'issue_date' => $lc['issue_date'],
                'credential_id' => $lc['credential_id'],
                'credential_url' => $lc['credential_url']
            ];
        }

        $languages = $this->studentModel->getLanguages($student['id']);
        $achievements = $this->studentModel->getAchievements($student['id']);

        $hackathons = array_filter($achievements, fn($a) => ($a['category'] ?? '') === 'Hackathon');
        $workshops = array_filter($achievements, fn($a) => in_array($a['category'] ?? '', ['Workshop', 'Seminar']));
        $competitions = array_filter($achievements, fn($a) => in_array($a['category'] ?? '', ['Coding Competition', 'Project Competition', 'Technical Event', 'Sports', 'Innovation']));

        $trainings = $this->db->fetchAll(
            "SELECT t.*, tr.created_at as registered_at FROM training_registrations tr JOIN trainings t ON tr.training_id = t.id WHERE tr.student_id = ? ORDER BY t.start_date DESC",
            [$student['id']]
        );
        $internships = $this->db->fetchAll(
            "SELECT a.*, j.title as job_title, j.location, j.job_type, c.company_name
             FROM applications a JOIN jobs j ON a.job_id = j.id JOIN companies c ON j.company_id = c.id
             WHERE a.student_id = ? AND a.status IN ('selected', 'interview', 'shortlisted')
             ORDER BY a.applied_at DESC",
            [$student['id']]
        );
        require_once VIEWS_PATH . '/student/resume-builder.php';
    }

    public function saveResumeAccent(): void {
        $color = trim($_POST['accent_color'] ?? '#2563eb');
        if (!preg_match('/^#[a-fA-F0-9]{6}$/', $color)) {
            $color = '#2563eb';
        }
        $this->db->update("UPDATE students SET resume_accent_color = ? WHERE id = ?", [$color, $this->student['id']]);
        if (isAjax()) {
            jsonResponse(['success' => true, 'accent_color' => $color]);
        } else {
            redirect('/student/resume-builder');
        }
    }

    public function recommendations(): void {
        $student = $this->student;
        $pageTitle = 'AI Job Recommendations';
        require_once ROOT_PATH . '/services/JobRecommendationService.php';
        $recommendedJobs = JobRecommendationService::getInstance()->getRecommendedJobs($student, 12);
        require_once VIEWS_PATH . '/student/recommendations.php';
    }

    // ==========================================
    // MODULE 1: INTERVIEW SCHEDULE & CALL LETTER
    // ==========================================
    public function downloadCallLetter($id): void {
        $student = $this->student;
        $interviewId = (int)$id;
        $interview = $this->db->fetchOne(
            "SELECT i.*, j.title as job_title, c.company_name, c.logo as company_logo
             FROM interviews i JOIN jobs j ON i.job_id = j.id JOIN companies c ON i.company_id = c.id
             WHERE i.id = ? AND i.student_id = ?",
            [$interviewId, $student['id']]
        );

        if (!$interview) {
            setFlash('danger', 'Interview schedule not found.');
            redirect('/student/interviews');
            return;
        }

        if (!empty($interview['call_letter_path'])) {
            $filePath = UPLOADS_PATH . '/call_letters/' . $interview['call_letter_path'];
            if (file_exists($filePath)) {
                $ext = getFileExtension($interview['call_letter_path']);
                header('Content-Type: ' . ($ext === 'pdf' ? 'application/pdf' : 'application/octet-stream'));
                header('Content-Disposition: attachment; filename="Call_Letter_' . preg_replace('/[^A-Za-z0-9_]/', '_', $interview['company_name']) . '.' . $ext . '"');
                readfile($filePath);
                exit;
            }
        }

        $pageTitle = 'Call Letter - ' . $interview['company_name'];
        require_once VIEWS_PATH . '/student/call-letter-template.php';
    }

    // ==========================================
    // MODULE 2: ACHIEVEMENTS MODULE
    // ==========================================
    public function achievements(): void {
        $student = $this->student;
        $pageTitle = 'My Achievements';
        $search = sanitize($_GET['search'] ?? '');
        $category = sanitize($_GET['category'] ?? '');

        $achievements = $this->studentModel->getAchievements($student['id'], $search, $category);
        $totalAchievements = count($achievements);
        $verifiedCount = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM student_achievements WHERE student_id = ? AND status = 'verified'", [$student['id']]);
        $pendingCount = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM student_achievements WHERE student_id = ? AND status = 'pending'", [$student['id']]);

        require_once VIEWS_PATH . '/student/achievements.php';
    }

    public function editAchievement(): void {
        CsrfMiddleware::requireValidToken();
        $student = $this->student;
        $achId = (int)($_POST['achievement_id'] ?? 0);
        $title = sanitize($_POST['title'] ?? '');
        $category = sanitize($_POST['category'] ?? 'Others');
        $description = sanitize($_POST['description'] ?? '');
        $achievementDate = sanitize($_POST['achievement_date'] ?? '');
        $organizer = sanitize($_POST['organizer'] ?? '');
        $positionRank = sanitize($_POST['position_rank'] ?? '');

        if (!$achId || empty($title)) {
            setFlash('danger', 'Invalid achievement request.');
            redirect('/student/achievements');
            return;
        }

        $updateData = [
            'title' => $title,
            'category' => $category,
            'description' => $description,
            'achievement_date' => $achievementDate ?: null,
            'organizer' => $organizer,
            'position_rank' => $positionRank,
            'status' => 'pending'
        ];

        if (isset($_FILES['certificate_file']) && $_FILES['certificate_file']['error'] === UPLOAD_ERR_OK) {
            $f = $_FILES['certificate_file'];
            $ext = getFileExtension($f['name']);
            if (in_array($ext, ['pdf', 'jpg', 'jpeg', 'png'])) {
                $dir = UPLOADS_PATH . '/achievements/';
                if (!is_dir($dir)) @mkdir($dir, 0777, true);
                $certFileName = generateFileName($f['name'], 'ach_cert_' . $student['id']);
                move_uploaded_file($f['tmp_name'], $dir . $certFileName);
                $updateData['certificate_file'] = $certFileName;
            }
        }

        if (isset($_FILES['achievement_image']) && $_FILES['achievement_image']['error'] === UPLOAD_ERR_OK) {
            $img = $_FILES['achievement_image'];
            $ext = getFileExtension($img['name']);
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $dir = UPLOADS_PATH . '/achievements/';
                if (!is_dir($dir)) @mkdir($dir, 0777, true);
                $imageFileName = generateFileName($img['name'], 'ach_img_' . $student['id']);
                move_uploaded_file($img['tmp_name'], $dir . $imageFileName);
                $updateData['achievement_image'] = $imageFileName;
            }
        }

        $this->studentModel->updateAchievement($achId, $student['id'], $updateData);
        setFlash('success', 'Achievement updated successfully and submitted for re-verification.');
        redirect('/student/achievements');
    }

    // ==========================================
    // MODULE 3: PLACEMENT CALENDAR
    // ==========================================
    public function calendar(): void {
        $student = $this->student;
        $pageTitle = 'Placement Calendar';
        require_once VIEWS_PATH . '/student/calendar.php';
    }

    public function getCalendarEvents(): void {
        $student = $this->student;
        $startDate = sanitize($_GET['start'] ?? '');
        $endDate = sanitize($_GET['end'] ?? '');

        $events = $this->studentModel->getPlacementCalendarEvents($startDate, $endDate, $student['id']);
        jsonResponse(['success' => true, 'events' => $events]);
    }
}

