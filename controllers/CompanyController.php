<?php
/**
 * TPMS - Company Controller
 */
require_once ROOT_PATH . '/models/Company.php';
require_once ROOT_PATH . '/models/Job.php';
require_once ROOT_PATH . '/models/Student.php';
require_once ROOT_PATH . '/models/Recommendation.php';
require_once ROOT_PATH . '/includes/Mailer.php';

class CompanyController {
    private Company $companyModel;
    private Job $jobModel;
    private Database $db;
    private ?array $company;

    public function __construct() {
        $this->companyModel = new Company();
        $this->jobModel = new Job();
        $this->db = Database::getInstance();
        $this->company = $this->companyModel->findByUserId($_SESSION['user_id']);
    }

    public function dashboard(): void {
        $company = $this->company;
        $pageTitle = 'Company Dashboard';
        $totalJobs = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM jobs WHERE company_id = ?", [$company['id']]);
        $activeJobs = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM jobs WHERE company_id = ? AND status = 'active'", [$company['id']]);
        $totalApplications = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM applications a JOIN jobs j ON a.job_id = j.id WHERE j.company_id = ?", [$company['id']]);
        $uniqueApplicantsCount = (int)$this->db->fetchColumn("SELECT COUNT(DISTINCT a.student_id) FROM applications a JOIN jobs j ON a.job_id = j.id WHERE j.company_id = ?", [$company['id']]);
        $shortlisted = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM applications a JOIN jobs j ON a.job_id = j.id WHERE j.company_id = ? AND a.status = 'shortlisted'", [$company['id']]);
        $selected = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM applications a JOIN jobs j ON a.job_id = j.id WHERE j.company_id = ? AND a.status = 'selected'", [$company['id']]);
        $interviewCount = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM interviews WHERE company_id = ? AND status = 'scheduled'", [$company['id']]);
        $recentApps = $this->db->fetchAll("SELECT a.*, s.first_name, s.last_name, s.branch, s.profile_photo, j.title as job_title FROM applications a JOIN students s ON a.student_id = s.id JOIN jobs j ON a.job_id = j.id WHERE j.company_id = ? ORDER BY a.applied_at DESC LIMIT 10", [$company['id']]);
        $jobs = $this->jobModel->getByCompany($company['id']);
        $notifications = $this->db->fetchAll("SELECT * FROM notifications WHERE user_id = ? OR is_global = 1 ORDER BY created_at DESC LIMIT 5", [$_SESSION['user_id']]);
        require_once VIEWS_PATH . '/company/dashboard.php';
    }

    public function profile(): void {
        $company = $this->company;
        $pageTitle = 'Company Profile';
        require_once VIEWS_PATH . '/company/profile.php';
    }

    public function updateProfile(): void {
        CsrfMiddleware::requireValidToken();
        $data = sanitizeArray($_POST);
        $updateData = [
            'company_name' => $data['company_name'] ?? $this->company['company_name'],
            'industry' => $data['industry'] ?? null,
            'website' => $data['website'] ?? null,
            'description' => $data['description'] ?? null,
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'state' => $data['state'] ?? null,
            'country' => $data['country'] ?? 'India',
            'contact_person' => $data['contact_person'] ?? null,
            'contact_phone' => $data['contact_phone'] ?? null,
            'contact_email' => $data['contact_email'] ?? null,
            'company_type' => $data['company_type'] ?? null,
            'employee_count' => $data['employee_count'] ?? null,
            'established_year' => $data['established_year'] ?: null,
        ];

        // Validate location fields (city, state, country)
        $errors = [];
        if (isset($data['city']) && $data['city'] !== '') {
            $cRes = Validator::city($data['city'], false);
            if (!$cRes['valid']) $errors[] = $cRes['message'];
        }
        if (isset($data['state']) && $data['state'] !== '') {
            $sRes = Validator::state($data['state'], false);
            if (!$sRes['valid']) $errors[] = $sRes['message'];
        }
        if (isset($data['country']) && $data['country'] !== '') {
            $coRes = Validator::country($data['country'], false);
            if (!$coRes['valid']) $errors[] = $coRes['message'];
        }

        if (!empty($errors)) {
            setFlash('danger', '<strong>Validation Error:</strong><br>' . implode('<br>', $errors));
            redirect('/company/profile');
            return;
        }

        // Logo upload
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['logo'];
            $mime = mime_content_type($file['tmp_name']);
            if (in_array($mime, ALLOWED_IMAGE_TYPES) && $file['size'] <= MAX_FILE_SIZE) {
                if ($this->company['logo']) { $old = UPLOADS_PATH . '/company/' . $this->company['logo']; if (file_exists($old)) unlink($old); }
                $fn = generateFileName($file['name'], 'company_' . $this->company['id']);
                move_uploaded_file($file['tmp_name'], UPLOADS_PATH . '/company/' . $fn);
                $updateData['logo'] = $fn;
            }
        }

        $this->companyModel->updateByUserId($_SESSION['user_id'], $updateData);
        logActivity('update_profile', 'company', 'Company updated profile');
        setFlash('success', 'Profile updated successfully!');
        redirect('/company/profile');
    }

    public function postJobPage(): void {
        $company = $this->company;
        $pageTitle = 'Post New Job';
        $oldInput = $_SESSION['old_job_input'] ?? null;
        unset($_SESSION['old_job_input']);
        require_once VIEWS_PATH . '/company/post-job.php';
    }

    public function postJob(): void {
        CsrfMiddleware::requireValidToken();
        if (!$this->company['is_approved']) {
            setFlash('danger', 'Your company must be approved before posting jobs.');
            redirect('/company/dashboard');
            return;
        }

        $data = sanitizeArray($_POST);
        $errors = $this->validateJobData($data);

        if (!empty($errors)) {
            $_SESSION['old_job_input'] = $_POST;
            setFlash('danger', '<strong>Please correct the following errors:</strong><br>' . implode('<br>', $errors));
            redirect('/company/post-job');
            return;
        }

        $data['company_id'] = $this->company['id'];
        $data['status'] = 'pending';

        // Salary LPA normalization if raw rupees passed
        if (isset($data['salary_min']) && (float)$data['salary_min'] >= 1000) { $data['salary_min'] = (float)$data['salary_min'] / 100000; }
        if (isset($data['salary_max']) && (float)$data['salary_max'] >= 1000) { $data['salary_max'] = (float)$data['salary_max'] / 100000; }

        // Clean skills (deduplicate)
        $skRes = Validator::skills($data['skills_required']);
        $data['skills_required'] = $skRes['normalized'] ?? $data['skills_required'];

        $newJobId = $this->jobModel->create($data);
        logActivity('post_job', 'job', "Posted job: {$data['title']}");

        if ($newJobId) {
            try { (new Recommendation())->recomputeForJob($newJobId); } catch (\Throwable $e) { /* non-fatal */ }

            // Company in-app notification
            $this->db->insert(
                "INSERT INTO notifications (user_id, title, message, type, category, reference_id, link) VALUES (?, ?, ?, 'info', 'job', ?, ?)",
                [$_SESSION['user_id'], 'Job Submitted for Review', "Your job posting '{$data['title']}' has been submitted and is awaiting admin approval.", $newJobId, url('/company/jobs')]
            );
            // Admin in-app notification
            $adminUser = $this->db->fetchOne("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
            if ($adminUser) {
                $this->db->insert(
                    "INSERT INTO notifications (user_id, title, message, type, category, reference_id, link) VALUES (?, ?, ?, 'warning', 'job', ?, ?)",
                    [$adminUser['id'], 'New Job Pending Approval 📋', "Company '{$this->company['company_name']}' posted a new job: '{$data['title']}'. Approval required.", $newJobId, url('/admin/jobs')]
                );
            }
        }

        setFlash('success', 'Job posted successfully! All mandatory details recorded. It will be active after admin approval.');
        redirect('/company/jobs');
    }

    public function jobs(): void {
        $company = $this->company;
        $pageTitle = 'Manage Jobs';
        $jobs = $this->jobModel->getByCompany($company['id']);
        require_once VIEWS_PATH . '/company/jobs.php';
    }

    public function editJobPage($id): void {
        $job = $this->db->fetchOne("SELECT * FROM jobs WHERE id = ? AND company_id = ?", [$id, $this->company['id']]);
        if (!$job) { setFlash('danger', 'Job not found.'); redirect('/company/jobs'); return; }
        $company = $this->company;
        $pageTitle = 'Edit Job';
        $oldInput = $_SESSION['old_job_input_' . $id] ?? null;
        unset($_SESSION['old_job_input_' . $id]);
        require_once VIEWS_PATH . '/company/edit-job.php';
    }

    public function editJob($id): void {
        CsrfMiddleware::requireValidToken();
        $job = $this->db->fetchOne("SELECT * FROM jobs WHERE id = ? AND company_id = ?", [$id, $this->company['id']]);
        if (!$job) { setFlash('danger', 'Job not found.'); redirect('/company/jobs'); return; }
        
        $data = sanitizeArray($_POST);
        $errors = $this->validateJobData($data);

        if (!empty($errors)) {
            $_SESSION['old_job_input_' . $id] = $_POST;
            setFlash('danger', '<strong>Please correct the following errors:</strong><br>' . implode('<br>', $errors));
            redirect('/company/edit-job/' . $id);
            return;
        }

        $salMin = (float)$data['salary_min'];
        $salMax = (float)$data['salary_max'];
        if ($salMin >= 1000) $salMin /= 100000;
        if ($salMax >= 1000) $salMax /= 100000;

        $skRes = Validator::skills($data['skills_required']);
        $skillsNorm = $skRes['normalized'] ?? $data['skills_required'];

        $this->jobModel->update($id, [
            'title' => $data['title'],
            'description' => $data['description'],
            'job_type' => $data['job_type'],
            'work_mode' => $data['work_mode'],
            'location' => $data['location'],
            'salary_min' => $salMin,
            'salary_max' => $salMax,
            'openings' => $data['openings'],
            'skills_required' => $skillsNorm,
            'experience_required' => $data['experience_required'],
            'qualification' => $data['qualification'],
            'eligibility_cgpa' => $data['eligibility_cgpa'],
            'eligibility_branches' => $data['eligibility_branches'],
            'passing_year' => $data['passing_year'],
            'eligibility_backlogs' => $data['eligibility_backlogs'],
            'selection_process' => $data['selection_process'],
            'application_deadline' => $data['application_deadline'],
            'joining_date' => $data['joining_date'],
            'contact_person' => $data['contact_person'],
            'contact_email' => $data['contact_email'],
            'contact_phone' => $data['contact_phone'],
            'website' => $data['website'],
        ]);

        try { (new Recommendation())->recomputeForJob($id); } catch (\Throwable $e) { /* non-fatal */ }

        setFlash('success', 'Job updated successfully!');
        redirect('/company/jobs');
    }

    /**
     * Validate all job posting input fields
     */
    private function validateJobData(array $data): array {
        $errors = [];

        // Title
        $titleRes = Validator::text($data['title'] ?? '', 'Job title', 3, 150, true);
        if (!$titleRes['valid']) $errors[] = $titleRes['message'];

        // Job Type
        if (empty($data['job_type']) || !array_key_exists($data['job_type'], JOB_TYPES)) {
            $errors[] = 'Please select a valid job type.';
        }

        // Work Mode
        if (empty($data['work_mode']) || !in_array($data['work_mode'], ['onsite', 'remote', 'hybrid'])) {
            $errors[] = 'Please select a valid work mode.';
        }

        // Location
        $locRes = Validator::text($data['location'] ?? '', 'Location', 2, 150, true);
        if (!$locRes['valid']) $errors[] = $locRes['message'];

        // Vacancies / Openings
        $openRes = Validator::integer($data['openings'] ?? null, 'Number of vacancies', 1);
        if (!$openRes['valid']) $errors[] = $openRes['message'];

        // Salary Min
        $salMinRes = Validator::numeric($data['salary_min'] ?? null, 'Minimum salary', 0);
        if (!$salMinRes['valid']) $errors[] = $salMinRes['message'];

        // Salary Max
        $salMinVal = is_numeric($data['salary_min'] ?? null) ? (float)$data['salary_min'] : 0;
        $salMaxRes = Validator::numeric($data['salary_max'] ?? null, 'Maximum salary', $salMinVal);
        if (!$salMaxRes['valid']) $errors[] = $salMaxRes['message'];

        // Application Deadline
        $deadRes = Validator::date($data['application_deadline'] ?? '', 'Application deadline', true);
        if (!$deadRes['valid']) $errors[] = $deadRes['message'];

        // Expected Joining Date
        $joinRes = Validator::date($data['joining_date'] ?? '', 'Expected joining date', true);
        if (!$joinRes['valid']) $errors[] = $joinRes['message'];

        // Skills Required
        $skRes = Validator::skills($data['skills_required'] ?? '');
        if (empty($data['skills_required']) || !$skRes['valid'] || empty($skRes['normalized'])) {
            $errors[] = 'Required skills field is mandatory.';
        }

        // Experience Required
        $expRes = Validator::text($data['experience_required'] ?? '', 'Experience required', 2, 100, true);
        if (!$expRes['valid']) $errors[] = $expRes['message'];

        // Qualification
        $qualRes = Validator::text($data['qualification'] ?? '', 'Qualification', 2, 100, true);
        if (!$qualRes['valid']) $errors[] = $qualRes['message'];

        // Eligible Branches (Optional)
        $branchRes = Validator::text($data['eligibility_branches'] ?? '', 'Eligible branches', 2, 200, false);
        if (!$branchRes['valid']) $errors[] = $branchRes['message'];

        // Passing Year
        $passRes = Validator::text($data['passing_year'] ?? '', 'Passing year', 2, 50, true);
        if (!$passRes['valid']) $errors[] = $passRes['message'];

        // Eligibility CGPA
        $cgpaRes = Validator::numeric($data['eligibility_cgpa'] ?? null, 'Minimum CGPA', 0, 10);
        if (!$cgpaRes['valid']) $errors[] = $cgpaRes['message'];

        // Eligibility Backlogs
        $backRes = Validator::integer($data['eligibility_backlogs'] ?? null, 'Max active backlogs', 0);
        if (!$backRes['valid']) $errors[] = $backRes['message'];

        // Selection Process
        $selRes = Validator::text($data['selection_process'] ?? '', 'Selection process', 10, 1000, true);
        if (!$selRes['valid']) $errors[] = $selRes['message'];

        // Contact Person
        $cpRes = Validator::text($data['contact_person'] ?? '', 'Contact person', 2, 100, true);
        if (!$cpRes['valid']) $errors[] = $cpRes['message'];

        // Contact Email
        $ceRes = Validator::email($data['contact_email'] ?? '', 'Contact email');
        if (!$ceRes['valid']) $errors[] = $ceRes['message'];

        // Contact Phone
        $phoneRes = Validator::phone($data['contact_phone'] ?? '');
        if (!$phoneRes['valid']) $errors[] = $phoneRes['message'];

        // Company Website / URL
        $webRes = Validator::projectUrl($data['website'] ?? '');
        if (!$webRes['valid']) $errors[] = $webRes['message'];

        // Description
        $descRes = Validator::text($data['description'] ?? '', 'Job description', 20, 2000, true);
        if (!$descRes['valid']) $errors[] = $descRes['message'];

        return $errors;
    }

    public function deleteJob($id): void {
        $job = $this->jobModel->findById($id);
        if ($job && $job['company_id'] == $this->company['id']) {
            $this->db->delete("DELETE FROM jobs WHERE id = ? AND company_id = ?", [$id, $this->company['id']]);
            $this->db->insert(
                "INSERT INTO notifications (user_id, title, message, type, category, link) VALUES (?, ?, ?, 'warning', 'job', ?)",
                [$_SESSION['user_id'], 'Job Posting Deleted', "Your job posting '{$job['title']}' has been deleted.", url('/company/jobs')]
            );
        }
        setFlash('success', 'Job deleted.');
        redirect('/company/jobs');
    }

    public function viewApplications($jobId = null): void {
        $company = $this->company;
        if (empty($jobId) || $jobId === 'all' || (int)$jobId === 0) {
            $job = ['id' => 0, 'title' => 'All Jobs'];
            $pageTitle = 'Applications — All Jobs';
            $applications = $this->db->fetchAll(
                "SELECT a.*, s.first_name, s.last_name, u.email, s.phone, s.branch, s.cgpa, s.profile_photo, s.resume_path, s.user_id, j.title as job_title
                 FROM applications a
                 JOIN students s ON a.student_id = s.id
                 JOIN users u ON s.user_id = u.id
                 JOIN jobs j ON a.job_id = j.id
                 WHERE j.company_id = ?
                 ORDER BY a.applied_at DESC",
                [$company['id']]
            );
        } else {
            $job = $this->db->fetchOne("SELECT * FROM jobs WHERE id = ? AND company_id = ?", [(int)$jobId, $company['id']]);
            if (!$job) { setFlash('danger', 'Job not found.'); redirect('/company/jobs'); return; }
            $pageTitle = 'Applications — ' . $job['title'];
            $applications = $this->jobModel->getApplications((int)$jobId);
        }
        require_once VIEWS_PATH . '/company/applications.php';
    }

    public function updateApplicationStatus($appId): void {
        $status = sanitize($_POST['status'] ?? '');
        $validStatuses = ['applied', 'shortlisted', 'interview', 'selected', 'rejected'];
        if (!in_array($status, $validStatuses)) { setFlash('danger', 'Invalid status.'); redirect('/company/jobs'); return; }

        $app = $this->db->fetchOne("SELECT a.*, j.company_id, j.title as job_title FROM applications a JOIN jobs j ON a.job_id = j.id WHERE a.id = ?", [$appId]);
        if (!$app || $app['company_id'] != $this->company['id']) { setFlash('danger', 'Application not found.'); redirect('/company/jobs'); return; }

        $this->jobModel->updateApplicationStatus($appId, $status);

        // Mark student as placed if selected
        if ($status === 'selected') {
            $job = $this->db->fetchOne("SELECT * FROM jobs WHERE id = ?", [$app['job_id']]);
            $placedPkg = (float)($job['salary_max'] ?? $job['salary_min'] ?? 0);
            if ($placedPkg >= 1000) $placedPkg /= 100000;

            $this->db->update("UPDATE students SET is_placed = 1, placed_company = ?, placed_package = ?, placed_date = CURDATE() WHERE id = ?",
                [$this->company['company_name'], $placedPkg, $app['student_id']]);
            $this->db->insert("INSERT INTO placements (student_id, company_id, job_id, package, placement_date) VALUES (?, ?, ?, ?, CURDATE())",
                [$app['student_id'], $this->company['id'], $app['job_id'], $placedPkg]);
        }


        // Fetch interview details if any interview exists for this student & job
        $interviewDetails = $this->db->fetchOne(
            "SELECT * FROM interviews WHERE student_id = ? AND job_id = ? AND status != 'cancelled' ORDER BY id DESC LIMIT 1",
            [$app['student_id'], $app['job_id']]
        );

        // Notify student (in-app + email)
        $student = $this->db->fetchOne("SELECT s.user_id, u.email, s.first_name, s.last_name FROM students s JOIN users u ON s.user_id = u.id WHERE s.id = ?", [$app['student_id']]);
        $emailSent = false;

        if ($student) {
            $notifTitle = match($status) {
                'selected'    => 'Selected for Position! 🎉',
                'shortlisted' => 'Application Shortlisted ⭐',
                'interview'   => 'Interview Scheduled 📅',
                'rejected'    => 'Application Update',
                default       => 'Application Status Updated'
            };
            $notifMsg = match($status) {
                'selected'    => "Congratulations! You have been selected for {$app['job_title']} at {$this->company['company_name']}.",
                'shortlisted' => "Great news! Your application for {$app['job_title']} at {$this->company['company_name']} has been shortlisted.",
                'interview'   => "An interview has been scheduled for your application for {$app['job_title']} at {$this->company['company_name']}.",
                'rejected'    => "Update regarding your application for {$app['job_title']} at {$this->company['company_name']}.",
                default       => "Your application status for {$app['job_title']} has been updated to {$status}."
            };
            $notifType = match($status) {
                'selected'    => 'success',
                'rejected'    => 'danger',
                'shortlisted' => 'warning',
                default       => 'info'
            };

            // 1. Insert In-App Notification (with direct link to student applications)
            $this->db->insert(
                "INSERT INTO notifications (user_id, title, message, type, category, link) VALUES (?, ?, ?, ?, 'job', ?)",
                [$student['user_id'], $notifTitle, $notifMsg, $notifType, url('/student/applications')]
            );

            // 2. Send Notification Email to Student
            $emailSent = Mailer::sendApplicationStatus(
                $student['email'],
                $student['first_name'] . ' ' . $student['last_name'],
                $app['job_title'],
                $this->company['company_name'],
                $status,
                $interviewDetails ?: null
            );
        }

        $studentName  = $student ? ($student['first_name'] . ' ' . $student['last_name']) : 'Student';
        $statusCap    = ucfirst($status);
        $feedbackMsg  = "Application status updated to {$statusCap} for {$studentName}.";

        if (isAjax()) {
            jsonResponse([
                'success'    => true,
                'message'    => $feedbackMsg,
                'email_sent' => $emailSent
            ]);
        }

        setFlash('success', $feedbackMsg);
        redirect('/company/applications/' . $app['job_id']);
    }

    public function scheduleInterview($appId): void {
        CsrfMiddleware::requireValidToken();
        $app = $this->db->fetchOne("SELECT a.*, j.company_id FROM applications a JOIN jobs j ON a.job_id = j.id WHERE a.id = ?", [$appId]);
        if (!$app || $app['company_id'] != $this->company['id']) { setFlash('danger', 'Not found.'); redirect('/company/jobs'); return; }

        $data = sanitizeArray($_POST);

        // Validate Meeting Link (Mandatory & must be valid URL)
        $linkRes = Validator::meetingLink($data['meeting_link'] ?? '');
        if (!$linkRes['valid']) {
            setFlash('danger', $linkRes['message']);
            redirect('/company/applications/' . $app['job_id']);
            return;
        }

        $this->db->insert("INSERT INTO interviews (student_id, company_id, job_id, round, interview_date, interview_time, mode, venue, meeting_link, instructions, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'scheduled')",
            [$app['student_id'], $this->company['id'], $app['job_id'], $data['round'] ?? 'Round 1', $data['interview_date'], $data['interview_time'], 'online', $data['venue'] ?? null, trim($data['meeting_link']), $data['instructions'] ?? null]);

        $this->jobModel->updateApplicationStatus($appId, 'interview');

        $job = $this->db->fetchOne("SELECT * FROM jobs WHERE id = ?", [$app['job_id']]);
        $student = $this->db->fetchOne("SELECT s.user_id, u.email, s.first_name, s.last_name FROM students s JOIN users u ON s.user_id = u.id WHERE s.id = ?", [$app['student_id']]);
        $emailSent = false;

        if ($student) {
            $this->db->insert("INSERT INTO notifications (user_id, title, message, type, category, link) VALUES (?, ?, ?, ?, 'interview', ?)",
                [$student['user_id'], 'Interview Scheduled 📅', "An interview for {$job['title']} has been scheduled on " . formatDate($data['interview_date']) . ".", 'info', url('/student/interviews')]);
            
            $emailSent = Mailer::sendInterviewScheduled(
                $student['email'],
                $student['first_name'] . ' ' . $student['last_name'],
                $job['title'] ?? 'Position',
                $this->company['company_name'],
                $data['interview_date'],
                $data['interview_time'],
                $data['mode'] ?? 'offline',
                $data['venue'] ?? ''
            );
        }

        setFlash('success', 'Interview scheduled successfully.');
        redirect('/company/applications/' . $app['job_id']);
    }

    public function interviews(): void {
        $company = $this->company;
        $pageTitle = 'Interviews';
        $interviews = $this->db->fetchAll("SELECT i.*, j.title as job_title, s.first_name, s.last_name, s.branch FROM interviews i JOIN jobs j ON i.job_id = j.id JOIN students s ON i.student_id = s.id WHERE i.company_id = ? ORDER BY i.interview_date DESC", [$company['id']]);
        require_once VIEWS_PATH . '/company/interviews.php';
    }

    public function updateInterviewResult($id): void {
        $result = sanitize($_POST['result'] ?? '');
        if (!in_array($result, ['pending', 'passed', 'failed'])) { setFlash('danger', 'Invalid result.'); redirect('/company/interviews'); return; }
        $interview = $this->db->fetchOne("SELECT * FROM interviews WHERE id = ? AND company_id = ?", [$id, $this->company['id']]);
        if (!$interview) { setFlash('danger', 'Interview not found.'); redirect('/company/interviews'); return; }
        $this->db->update("UPDATE interviews SET result = ?, status = 'completed' WHERE id = ?", [$result, $id]);
        
        $appId = $this->db->fetchColumn("SELECT id FROM applications WHERE student_id = ? AND job_id = ?", [$interview['student_id'], $interview['job_id']]);
        if ($result === 'passed') { $this->jobModel->updateApplicationStatus($appId, 'shortlisted'); }
        if ($result === 'failed') { $this->jobModel->updateApplicationStatus($appId, 'rejected'); }
        
        $student = $this->db->fetchOne("SELECT s.user_id, u.email, s.first_name, s.last_name FROM students s JOIN users u ON s.user_id = u.id WHERE s.id = ?", [$interview['student_id']]);
        $interviewJob = $this->db->fetchOne("SELECT title FROM jobs WHERE id = ?", [$interview['job_id']]);
        $emailSent = false;

        if ($student) {
            $notifType = ($result === 'passed') ? 'success' : 'danger';
            $this->db->insert("INSERT INTO notifications (user_id, title, message, type, category, link) VALUES (?, ?, ?, ?, 'interview', ?)",
                [$student['user_id'], 'Interview Result Updated', "Result for your interview for {$interviewJob['title']}: " . ucfirst($result), $notifType, url('/student/interviews')]);
            
            $emailSent = Mailer::sendInterviewResult(
                $student['email'],
                $student['first_name'] . ' ' . $student['last_name'],
                $interviewJob['title'] ?? 'Position',
                $this->company['company_name'],
                $result
            );
        }

        setFlash('success', 'Interview result updated.');
        redirect('/company/interviews');
    }

    public function updateInterview($id): void {
        CsrfMiddleware::requireValidToken();
        $interview = $this->db->fetchOne("SELECT * FROM interviews WHERE id = ? AND company_id = ?", [$id, $this->company['id']]);
        if (!$interview) { setFlash('danger', 'Interview not found.'); redirect('/company/interviews'); return; }

        $data = sanitizeArray($_POST);

        // Validate Meeting Link (Mandatory & must be valid URL)
        $linkRes = Validator::meetingLink($data['meeting_link'] ?? '');
        if (!$linkRes['valid']) {
            setFlash('danger', $linkRes['message']);
            redirect('/company/interviews');
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

        $student = $this->db->fetchOne(
            "SELECT s.user_id, s.first_name, s.last_name, u.email 
             FROM students s JOIN users u ON s.user_id = u.id WHERE s.id = ?",
            [$interview['student_id']]
        );
        $job = $this->db->fetchOne("SELECT title FROM jobs WHERE id = ?", [$interview['job_id']]);
        $emailSent = false;

        if ($student) {
            $this->db->insert("INSERT INTO notifications (user_id, title, message, type, category, link) VALUES (?, ?, ?, 'info', 'interview', ?)",
                [$student['user_id'], 'Interview Rescheduled 📅', "Your interview for '{$job['title']}' has been rescheduled for " . formatDate($data['interview_date']) . " at " . date('h:i A', strtotime($data['interview_time'])), url('/student/interviews')]);
            
            if (!empty($student['email']) && filter_var($student['email'], FILTER_VALIDATE_EMAIL)) {
                $emailSent = Mailer::sendInterviewScheduled(
                    $student['email'],
                    $student['first_name'] . ' ' . $student['last_name'],
                    $job['title'] ?? 'Position',
                    $this->company['company_name'],
                    $data['interview_date'],
                    $data['interview_time'],
                    $mode,
                    $data['venue'] ?? ''
                );
            }
        }

        setFlash('success', 'Interview rescheduled successfully.');
        redirect('/company/interviews');
    }

    public function cancelInterview($id): void {
        CsrfMiddleware::requireValidToken();
        $interview = $this->db->fetchOne("SELECT * FROM interviews WHERE id = ? AND company_id = ?", [$id, $this->company['id']]);
        if (!$interview) { setFlash('danger', 'Interview not found.'); redirect('/company/interviews'); return; }

        $this->db->update("UPDATE interviews SET status = 'cancelled' WHERE id = ?", [$id]);

        $student = $this->db->fetchOne(
            "SELECT s.user_id, s.first_name, s.last_name, u.email 
             FROM students s JOIN users u ON s.user_id = u.id WHERE s.id = ?",
            [$interview['student_id']]
        );
        $job = $this->db->fetchOne("SELECT title FROM jobs WHERE id = ?", [$interview['job_id']]);
        $emailSent = false;

        if ($student) {
            $this->db->insert("INSERT INTO notifications (user_id, title, message, type, category, link) VALUES (?, ?, ?, 'warning', 'interview', ?)",
                [$student['user_id'], 'Interview Cancelled ❌', "Your interview for '{$job['title']}' scheduled for " . formatDate($interview['interview_date']) . " has been cancelled.", url('/student/interviews')]);
            
            if (!empty($student['email']) && filter_var($student['email'], FILTER_VALIDATE_EMAIL)) {
                $emailSent = Mailer::sendInterviewResult(
                    $student['email'],
                    $student['first_name'] . ' ' . $student['last_name'],
                    $job['title'] ?? 'Position',
                    $this->company['company_name'],
                    'cancelled'
                );
            }
        }

        setFlash('success', 'Interview cancelled.');
        redirect('/company/interviews');
    }

    // =====================================================================
    // VIEW APPLICANT — Full student profile for company recruiters
    // Authorization: student must have applied to one of this company's jobs
    // =====================================================================
    // =====================================================================
    // AI RECOMMENDATIONS — Dedicated page showing top students per job
    // =====================================================================
    public function recommendations(): void {
        $company   = $this->company;
        $pageTitle = 'AI Recommendations — ' . $company['company_name'];
        $recoModel = new Recommendation();

        // Fetch grouped top students per job
        $recommendations = $recoModel->getTopStudentsForCompany($company['id'], 8);

        // Stats widget
        $stats = $recoModel->getCompanyRecommendationStats($company['id']);

        // Recompute if triggered
        if (isset($_GET['refresh'])) {
            $jobs = $this->db->fetchAll(
                "SELECT id FROM jobs WHERE company_id = ? AND status = 'active'",
                [$company['id']]
            );
            foreach ($jobs as $j) {
                $recoModel->recomputeForJob($j['id']);
            }
            setFlash('success', 'AI recommendations refreshed successfully!');
            redirect('/company/recommendations');
            return;
        }

        require_once VIEWS_PATH . '/company/recommendations.php';
    }

    public function notifications(): void {
        $pageTitle = 'Notifications';
        $notifications = $this->db->fetchAll(
            "SELECT * FROM notifications WHERE user_id = ? OR is_global = 1 ORDER BY created_at DESC LIMIT 50",
            [$_SESSION['user_id']]
        );
        require_once VIEWS_PATH . '/company/notifications.php';
    }

    public function viewApplicant($studentId): void {
        require_once ROOT_PATH . '/models/Student.php';
        $studentModel = new Student();
        $studentId    = (int)$studentId;

        // ── Authorization: verify the student has applied to at least one of this company's jobs OR is AI-recommended ──
        $hasApplied = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM applications a
             JOIN jobs j ON a.job_id = j.id
             WHERE a.student_id = ? AND j.company_id = ?",
            [$studentId, $this->company['id']]
        );
        $isRecommended = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM job_recommendations jr
             JOIN jobs j ON jr.job_id = j.id
             WHERE jr.student_id = ? AND j.company_id = ?",
            [$studentId, $this->company['id']]
        );
        if (!$hasApplied && !$isRecommended) {
            setFlash('danger', 'You are not authorized to view this applicant profile.');
            redirect('/company/jobs');
            return;
        }

        // ── Fetch all profile data ──
        $student = $studentModel->findById($studentId);
        if (!$student) {
            setFlash('danger', 'Student profile not found.');
            redirect('/company/jobs');
            return;
        }

        $projects       = $studentModel->getProjects($studentId);
        $certifications = $studentModel->getCertifications($studentId);
        $languages      = $studentModel->getLanguages($studentId);
        $achievements   = $studentModel->getAchievements($studentId);

        // Documents — stored under user_id in documents table
        $documents = $this->db->fetchAll(
            "SELECT * FROM documents WHERE user_id = ? ORDER BY created_at DESC",
            [$student['user_id']]
        );

        // This company's applications from the student (for the action panel)
        $applications = $this->db->fetchAll(
            "SELECT a.*, j.title as job_title, j.id as job_id
             FROM applications a JOIN jobs j ON a.job_id = j.id
             WHERE a.student_id = ? AND j.company_id = ? ORDER BY a.applied_at DESC",
            [$studentId, $this->company['id']]
        );

        // Most recent application for quick actions
        $latestApp = $applications[0] ?? null;

        // Profile completion
        $profileCompletion = calculateProfileCompletion($student);

        // AI Match Score — simple heuristic based on skills overlap with company jobs
        $aiMatchScore = $this->computeMatchScore($student, $this->company['id']);

        $company   = $this->company;
        $pageTitle = $student['first_name'] . ' ' . $student['last_name'] . ' — Applicant Profile';
        require_once VIEWS_PATH . '/company/view-applicant.php';
    }

    /**
     * Compute a simple AI-style match score (0–100) based on skills overlap
     * between the student's skills and the skills required by this company's jobs.
     */
    private function computeMatchScore(array $student, int $companyId): int {
        $studentSkills = array_map('trim', array_map('strtolower', explode(',', $student['skills'] ?? '')));
        $studentSkills = array_filter($studentSkills);

        if (empty($studentSkills)) return 0;

        // Gather required skills across all company jobs
        $rows = $this->db->fetchAll("SELECT skills_required FROM jobs WHERE company_id = ? AND status = 'active'", [$companyId]);
        $jobSkills = [];
        foreach ($rows as $row) {
            foreach (explode(',', $row['skills_required'] ?? '') as $sk) {
                $sk = strtolower(trim($sk));
                if ($sk) $jobSkills[] = $sk;
            }
        }
        $jobSkills = array_unique($jobSkills);
        if (empty($jobSkills)) return 50; // neutral

        $matched = 0;
        foreach ($studentSkills as $sk) {
            foreach ($jobSkills as $jsk) {
                if (str_contains($jsk, $sk) || str_contains($sk, $jsk)) { $matched++; break; }
            }
        }
        $score = (int)min(100, round(($matched / count($jobSkills)) * 100));
        // Boost score based on CGPA
        if (($student['cgpa'] ?? 0) >= 8.0) $score = min(100, $score + 10);
        elseif (($student['cgpa'] ?? 0) >= 7.0) $score = min(100, $score + 5);
        return max(10, $score); // minimum display score of 10
    }

    // =====================================================================
    // SERVE DOCUMENT — Secure, role-gated file serving for companies
    // Prevents direct URL access; only allows access if authorized
    // Route: /company/serve-document/{docId}
    // =====================================================================
    public function serveDocument($docId): void {
        $doc = $this->db->fetchOne("SELECT * FROM documents WHERE id = ?", [$docId]);
        if (!$doc) { http_response_code(404); echo 'Document not found.'; exit; }

        // Authorization: the document owner must have applied to one of this company's jobs
        $studentRecord = $this->db->fetchOne(
            "SELECT s.id FROM students s WHERE s.user_id = ?",
            [$doc['user_id']]
        );
        if (!$studentRecord) { http_response_code(403); echo 'Forbidden.'; exit; }

        $hasApplied = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM applications a JOIN jobs j ON a.job_id = j.id
             WHERE a.student_id = ? AND j.company_id = ?",
            [$studentRecord['id'], $this->company['id']]
        );
        if (!$hasApplied) { http_response_code(403); echo 'Access denied.'; exit; }

        $filePath = UPLOADS_PATH . '/' . $doc['file_path'];
        if (!file_exists($filePath)) { http_response_code(404); echo 'File not found.'; exit; }

        $mime = $doc['mime_type'] ?: mime_content_type($filePath);
        $disposition = (strpos($mime, 'pdf') !== false) ? 'inline' : 'attachment';

        header('Content-Type: ' . $mime);
        header('Content-Disposition: ' . $disposition . '; filename="' . $doc['original_name'] . '"');
        header('Content-Length: ' . filesize($filePath));
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, no-cache');
        readfile($filePath);
        exit;
    }

    // =====================================================================
    // SERVE RESUME — Secure resume serving for companies
    // Route: /company/serve-resume/{studentId}
    // =====================================================================
    public function serveResume($studentId): void {
        // Authorization check
        $hasApplied = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM applications a JOIN jobs j ON a.job_id = j.id
             WHERE a.student_id = ? AND j.company_id = ?",
            [$studentId, $this->company['id']]
        );
        if (!$hasApplied) { http_response_code(403); echo 'Access denied.'; exit; }

        $student = $this->db->fetchOne("SELECT resume_path, resume_original_name FROM students WHERE id = ?", [$studentId]);
        if (!$student || !$student['resume_path']) { http_response_code(404); echo 'Resume not found.'; exit; }

        $filePath = UPLOADS_PATH . '/resume/' . $student['resume_path'];
        if (!file_exists($filePath)) { http_response_code(404); echo 'File not found.'; exit; }

        $download = isset($_GET['download']);
        $disposition = $download ? 'attachment' : 'inline';
        $filename = $student['resume_original_name'] ?: $student['resume_path'];

        header('Content-Type: application/pdf');
        header('Content-Disposition: ' . $disposition . '; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filePath));
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, no-cache');
        readfile($filePath);
        exit;
    }
}

