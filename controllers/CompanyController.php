<?php
/**
 * TPMS - Company Controller
 */
require_once ROOT_PATH . '/models/Company.php';
require_once ROOT_PATH . '/models/Job.php';
require_once ROOT_PATH . '/models/Student.php';
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
        require_once VIEWS_PATH . '/company/post-job.php';
    }

    public function postJob(): void {
        CsrfMiddleware::requireValidToken();
        if (!$this->company['is_approved']) { setFlash('danger', 'Your company must be approved before posting jobs.'); redirect('/company/dashboard'); return; }
        $data = sanitizeArray($_POST);
        $data['company_id'] = $this->company['id'];
        if (empty($data['title'])) { setFlash('danger', 'Job title is required.'); redirect('/company/post-job'); return; }
        $this->jobModel->create($data);
        logActivity('post_job', 'job', "Posted job: {$data['title']}");
        setFlash('success', 'Job posted successfully! It will be active after admin approval.');
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
        require_once VIEWS_PATH . '/company/edit-job.php';
    }

    public function editJob($id): void {
        CsrfMiddleware::requireValidToken();
        $job = $this->db->fetchOne("SELECT * FROM jobs WHERE id = ? AND company_id = ?", [$id, $this->company['id']]);
        if (!$job) { setFlash('danger', 'Job not found.'); redirect('/company/jobs'); return; }
        $data = sanitizeArray($_POST);
        $this->jobModel->update($id, [
            'title' => $data['title'] ?? $job['title'],
            'description' => $data['description'] ?? $job['description'],
            'job_type' => $data['job_type'] ?? $job['job_type'],
            'work_mode' => $data['work_mode'] ?? $job['work_mode'],
            'location' => $data['location'] ?? $job['location'],
            'salary_min' => $data['salary_min'] ?? $job['salary_min'],
            'salary_max' => $data['salary_max'] ?? $job['salary_max'],
            'openings' => $data['openings'] ?? $job['openings'],
            'skills_required' => $data['skills_required'] ?? $job['skills_required'],
            'eligibility_cgpa' => $data['eligibility_cgpa'] ?? $job['eligibility_cgpa'],
            'eligibility_branches' => $data['eligibility_branches'] ?? $job['eligibility_branches'],
            'eligibility_backlogs' => $data['eligibility_backlogs'] ?? $job['eligibility_backlogs'],
            'application_deadline' => $data['application_deadline'] ?: null,
        ]);
        setFlash('success', 'Job updated successfully!');
        redirect('/company/jobs');
    }

    public function deleteJob($id): void {
        $this->db->delete("DELETE FROM jobs WHERE id = ? AND company_id = ?", [$id, $this->company['id']]);
        setFlash('success', 'Job deleted.');
        redirect('/company/jobs');
    }

    public function viewApplications($jobId): void {
        $job = $this->db->fetchOne("SELECT * FROM jobs WHERE id = ? AND company_id = ?", [$jobId, $this->company['id']]);
        if (!$job) { setFlash('danger', 'Job not found.'); redirect('/company/jobs'); return; }
        $company = $this->company;
        $pageTitle = 'Applications - ' . $job['title'];
        $applications = $this->jobModel->getApplications($jobId);
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
            $this->db->update("UPDATE students SET is_placed = 1, placed_company = ?, placed_package = ?, placed_date = CURDATE() WHERE id = ?",
                [$this->company['company_name'], $job['salary_max'] ?? $job['salary_min'], $app['student_id']]);
            $this->db->insert("INSERT INTO placements (student_id, company_id, job_id, package, placement_date) VALUES (?, ?, ?, ?, CURDATE())",
                [$app['student_id'], $this->company['id'], $app['job_id'], $job['salary_max'] ?? $job['salary_min']]);
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
        $this->db->insert("INSERT INTO interviews (student_id, company_id, job_id, round, interview_date, interview_time, mode, venue, meeting_link, instructions, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'scheduled')",
            [$app['student_id'], $this->company['id'], $app['job_id'], $data['round'] ?? 'Round 1', $data['interview_date'], $data['interview_time'], $data['mode'] ?? 'offline', $data['venue'] ?? null, $data['meeting_link'] ?? null, $data['instructions'] ?? null]);

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
                    $data['mode'] ?? 'offline',
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
}
