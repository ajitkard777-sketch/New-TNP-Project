<?php
/**
 * TPMS - Company Interview Scheduling & Candidate Management Controller
 * All SQL queries aligned to actual `interviews` table schema:
 *   id, student_id, company_id, job_id, round, interview_date, interview_time,
 *   mode, venue, meeting_link, instructions, status, result, feedback, created_at, updated_at
 */

require_once ROOT_PATH . '/models/Company.php';
require_once ROOT_PATH . '/models/Student.php';
require_once ROOT_PATH . '/models/Job.php';
require_once ROOT_PATH . '/includes/PdfGenerator.php';

class InterviewController {
    private Database $db;
    private Company $companyModel;
    private Student $studentModel;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->companyModel = new Company();
        $this->studentModel = new Student();
    }

    /**
     * Verify company authentication and approval
     */
    private function getCompanyAuth(): array {
        AuthMiddleware::requireLogin();
        if ($_SESSION['user_role'] !== 'company') {
            jsonResponse(['error' => 'Only authenticated companies can access interview scheduling'], 403);
            exit;
        }
        $company = $this->companyModel->findByUserId($_SESSION['user_id']);
        if (!$company || !$company['is_approved']) {
            jsonResponse(['error' => 'Your company account must be approved to schedule interviews'], 403);
            exit;
        }
        return $company;
    }

    /**
     * POST /api/company/interviews  (Schedule Single or Bulk Interviews)
     * Reads: job_id, student_ids[], interview_date, interview_time, round, mode, venue, meeting_link, instructions
     */
    public function scheduleInterviews(): void {
        $company = $this->getCompanyAuth();

        $raw  = file_get_contents('php://input');
        $json = json_decode($raw, true) ?? [];
        $post = sanitizeArray($_POST);

        $jobId      = (int)($post['job_id']         ?? $json['job_id']         ?? 0);
        $studentIds = (array)($post['student_ids']   ?? $json['student_ids']   ?? []);
        $round      = sanitize($post['interview_round'] ?? $json['interview_round'] ?? $post['round'] ?? $json['round'] ?? 'Round 1');
        $mode       = strtolower(sanitize($post['interview_type'] ?? $json['interview_type'] ?? $post['mode'] ?? $json['mode'] ?? 'offline'));
        // Normalise mode: 'hybrid' not in enum, fall back to 'online'
        if (!in_array($mode, ['online', 'offline'])) {
            $mode = 'online';
        }
        $date         = sanitize($post['interview_date']  ?? $json['interview_date']  ?? '');
        $time         = sanitize($post['interview_time']  ?? $json['interview_time']  ?? $post['start_time'] ?? $json['start_time'] ?? '');
        $venue        = sanitize($post['venue']           ?? $json['venue']           ?? '');
        $meetingLink  = sanitize($post['meeting_link']    ?? $json['meeting_link']    ?? '');
        $instructions = sanitize($post['instructions']    ?? $json['instructions']    ?? '');

        // --- Validation ---
        if ($jobId <= 0 || empty($studentIds)) {
            jsonResponse(['error' => 'Job selection and at least one student candidate are required'], 400);
            return;
        }
        if (empty($date) || empty($time)) {
            jsonResponse(['error' => 'Interview date and time are required'], 400);
            return;
        }
        if (strtotime($date) < strtotime(date('Y-m-d'))) {
            jsonResponse(['error' => 'Interview date cannot be in the past'], 400);
            return;
        }

        $job = $this->db->fetchOne(
            "SELECT title FROM jobs WHERE id = ? AND company_id = ?",
            [$jobId, $company['id']]
        );
        if (!$job) {
            jsonResponse(['error' => 'Job posting not found or not owned by your company'], 404);
            return;
        }

        $createdCount = 0;
        $errors = [];
        foreach ($studentIds as $stuId) {
            $stuId = (int)$stuId;
            $stu = $this->db->fetchOne(
                "SELECT s.id, s.user_id, s.first_name FROM students s WHERE s.id = ?",
                [$stuId]
            );
            if ($stu) {
                try {
                    $invId = $this->db->insert(
                        "INSERT INTO interviews
                            (company_id, job_id, student_id, round, interview_date, interview_time,
                             mode, venue, meeting_link, instructions, status, created_at)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'scheduled', NOW())",
                        [$company['id'], $jobId, $stuId, $round, $date, $time,
                         $mode, $venue ?: null, $meetingLink ?: null, $instructions ?: null]
                    );

                    // Student notification
                    $notifMsg = "Interview scheduled for {$job['title']} by {$company['company_name']}. Date: {$date} at " . date('h:i A', strtotime($time)) . ".";
                    $this->db->query(
                        "INSERT INTO notifications (user_id, title, message, type, category) VALUES (?, ?, ?, ?, ?)",
                        [$stu['user_id'], 'New Interview Scheduled', $notifMsg, 'info', 'interview']
                    );
                    $createdCount++;
                } catch (Exception $e) {
                    error_log("[InterviewController::scheduleInterviews] DB error for student {$stuId}: " . $e->getMessage());
                    $errors[] = "Student ID {$stuId}: " . $e->getMessage();
                }
            }
        }

        if ($createdCount === 0) {
            jsonResponse([
                'error' => 'No interviews could be scheduled. ' . implode('; ', $errors)
            ], 500);
            return;
        }

        jsonResponse([
            'success' => true,
            'message' => "Successfully scheduled interview for {$createdCount} candidate(s)!",
            'created_count' => $createdCount
        ]);
    }

    /**
     * GET /api/company/interviews  (List all interviews for the authenticated company)
     */
    public function getCompanyInterviews(): void {
        $company = $this->getCompanyAuth();

        try {
            $interviews = $this->db->fetchAll(
                "SELECT i.*,
                        s.first_name, s.last_name, s.enrollment_no, s.branch, s.cgpa, s.skills, s.profile_photo,
                        j.title as job_title
                 FROM interviews i
                 JOIN students s ON i.student_id = s.id
                 JOIN jobs     j ON i.job_id     = j.id
                 WHERE i.company_id = ?
                 ORDER BY i.interview_date ASC, i.interview_time ASC",
                [$company['id']]
            );
        } catch (Exception $e) {
            error_log("[InterviewController::getCompanyInterviews] " . $e->getMessage());
            jsonResponse(['error' => 'Failed to fetch interviews: ' . $e->getMessage()], 500);
            return;
        }

        jsonResponse([
            'success' => true,
            'count'   => count($interviews),
            'interviews' => $interviews
        ]);
    }

    /**
     * POST /api/company/interviews/reschedule
     */
    public function reschedule(): void {
        $company = $this->getCompanyAuth();

        $invId   = (int)($_POST['interview_id']   ?? 0);
        $newDate = sanitize($_POST['interview_date'] ?? '');
        $newTime = sanitize($_POST['interview_time'] ?? $_POST['start_time'] ?? '');
        $reason  = sanitize($_POST['reason']         ?? 'Schedule adjustment');

        if ($invId <= 0 || empty($newDate) || empty($newTime)) {
            jsonResponse(['error' => 'Interview ID, new date, and new time are required'], 400);
            return;
        }

        $inv = $this->db->fetchOne(
            "SELECT i.*, s.user_id, j.title as job_title
             FROM interviews i
             JOIN students s ON i.student_id = s.id
             JOIN jobs     j ON i.job_id     = j.id
             WHERE i.id = ? AND i.company_id = ?",
            [$invId, $company['id']]
        );
        if (!$inv) {
            jsonResponse(['error' => 'Interview record not found or access denied'], 404);
            return;
        }

        try {
            $this->db->query(
                "UPDATE interviews
                 SET interview_date = ?, interview_time = ?, status = 'rescheduled', instructions = ?
                 WHERE id = ?",
                [$newDate, $newTime, ($inv['instructions'] ? $inv['instructions'] . "\n[Rescheduled] Reason: {$reason}" : "Rescheduled. Reason: {$reason}"), $invId]
            );

            // Notify student
            $notifMsg = "Your interview for {$inv['job_title']} has been rescheduled to {$newDate} at " . date('h:i A', strtotime($newTime)) . ". Reason: {$reason}";
            $this->db->query(
                "INSERT INTO notifications (user_id, title, message, type, category) VALUES (?, ?, ?, ?, ?)",
                [$inv['user_id'], 'Interview Rescheduled', $notifMsg, 'warning', 'interview']
            );
        } catch (Exception $e) {
            error_log("[InterviewController::reschedule] " . $e->getMessage());
            jsonResponse(['error' => 'Reschedule failed: ' . $e->getMessage()], 500);
            return;
        }

        jsonResponse(['success' => true, 'message' => 'Interview rescheduled successfully!']);
    }

    /**
     * POST /api/company/interviews/cancel
     */
    public function cancel(): void {
        $company = $this->getCompanyAuth();

        $invId  = (int)($_POST['interview_id'] ?? 0);
        $reason = sanitize($_POST['reason'] ?? 'Unavoidable circumstances');

        if ($invId <= 0) {
            jsonResponse(['error' => 'Interview ID is required'], 400);
            return;
        }

        $inv = $this->db->fetchOne(
            "SELECT i.*, s.user_id, j.title as job_title
             FROM interviews i
             JOIN students s ON i.student_id = s.id
             JOIN jobs     j ON i.job_id     = j.id
             WHERE i.id = ? AND i.company_id = ?",
            [$invId, $company['id']]
        );
        if (!$inv) {
            jsonResponse(['error' => 'Interview record not found or access denied'], 404);
            return;
        }

        try {
            $this->db->query(
                "UPDATE interviews SET status = 'cancelled' WHERE id = ?",
                [$invId]
            );

            // Notify student
            $notifMsg = "Your interview for {$inv['job_title']} has been cancelled. Reason: {$reason}";
            $this->db->query(
                "INSERT INTO notifications (user_id, title, message, type, category) VALUES (?, ?, ?, ?, ?)",
                [$inv['user_id'], 'Interview Cancelled', $notifMsg, 'danger', 'interview']
            );
        } catch (Exception $e) {
            error_log("[InterviewController::cancel] " . $e->getMessage());
            jsonResponse(['error' => 'Cancel failed: ' . $e->getMessage()], 500);
            return;
        }

        jsonResponse(['success' => true, 'message' => 'Interview cancelled successfully!']);
    }

    /**
     * POST /api/company/interviews/feedback
     * Writes to interview_feedback table AND updates interviews.result / status
     */
    public function submitFeedback(): void {
        $company = $this->getCompanyAuth();

        $invId       = (int)($_POST['interview_id']          ?? 0);
        $techRating  = (int)($_POST['technical_rating']       ?? 5);
        $commRating  = (int)($_POST['communication_rating']   ?? 5);
        $probRating  = (int)($_POST['problem_solving_rating'] ?? 5);
        $ovRating    = (int)($_POST['overall_rating']         ?? 5);
        $comments    = sanitize($_POST['comments']            ?? '');
        $result      = sanitize($_POST['result']              ?? 'next_round');

        $validResults = ['selected', 'rejected', 'next_round'];
        if (!in_array($result, $validResults)) {
            $result = 'next_round';
        }

        $inv = $this->db->fetchOne(
            "SELECT * FROM interviews WHERE id = ? AND company_id = ?",
            [$invId, $company['id']]
        );
        if (!$inv) {
            jsonResponse(['error' => 'Interview not found or access denied'], 404);
            return;
        }

        try {
            // Upsert into interview_feedback table
            $this->db->query(
                "INSERT INTO interview_feedback
                    (interview_id, student_id, technical_rating, communication_rating,
                     problem_solving_rating, overall_rating, comments, result, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
                 ON DUPLICATE KEY UPDATE
                     technical_rating      = VALUES(technical_rating),
                     communication_rating  = VALUES(communication_rating),
                     problem_solving_rating= VALUES(problem_solving_rating),
                     overall_rating        = VALUES(overall_rating),
                     comments              = VALUES(comments),
                     result                = VALUES(result)",
                [$invId, $inv['student_id'], $techRating, $commRating, $probRating, $ovRating, $comments, $result]
            );

            // Update interviews.result and status using actual enum values
            // interviews.result enum: 'pending','passed','failed'
            // interviews.status enum: 'scheduled','completed','cancelled','rescheduled'
            $newResult = ($result === 'selected') ? 'passed' : (($result === 'rejected') ? 'failed' : 'pending');
            $newStatus = ($result === 'selected' || $result === 'rejected') ? 'completed' : $inv['status'];

            $this->db->query(
                "UPDATE interviews SET result = ?, status = ?, feedback = ? WHERE id = ?",
                [$newResult, $newStatus, $comments, $invId]
            );

            // Notify student
            $stu = $this->db->fetchOne("SELECT user_id FROM students WHERE id = ?", [$inv['student_id']]);
            if ($stu) {
                $statusLabel = $result === 'selected' ? 'Selected 🎉' : ($result === 'rejected' ? 'Rejected' : 'Proceeding to next round');
                $this->db->query(
                    "INSERT INTO notifications (user_id, title, message, type, category) VALUES (?, ?, ?, ?, ?)",
                    [$stu['user_id'], 'Interview Result Updated', "Interview result: {$statusLabel}. {$comments}", $result === 'selected' ? 'success' : ($result === 'rejected' ? 'danger' : 'info'), 'interview']
                );
            }
        } catch (Exception $e) {
            error_log("[InterviewController::submitFeedback] " . $e->getMessage());
            jsonResponse(['error' => 'Feedback save failed: ' . $e->getMessage()], 500);
            return;
        }

        jsonResponse(['success' => true, 'message' => 'Interview feedback and result saved successfully!']);
    }

    /**
     * GET /api/interview/pdf/:id  (Download Interview Call Letter PDF)
     */
    public function downloadPdf(int $invId): void {
        AuthMiddleware::requireLogin();
        if ($invId <= 0) {
            http_response_code(400);
            die("Invalid Interview ID");
        }

        $sql = "SELECT i.*,
                       s.first_name, s.last_name, s.enrollment_no, s.branch, s.cgpa, s.phone, s.skills,
                       u.email,
                       j.title as job_title,
                       c.company_name, c.logo, c.address as company_address, c.contact_email as company_email, c.contact_phone as company_phone
                FROM interviews i
                JOIN students s ON i.student_id = s.id
                JOIN users    u ON s.user_id    = u.id
                JOIN jobs     j ON i.job_id     = j.id
                JOIN companies c ON i.company_id = c.id
                WHERE i.id = ?";

        $interview = $this->db->fetchOne($sql, [$invId]);
        if (!$interview) {
            http_response_code(404);
            die("Interview record not found");
        }

        // Access control
        if ($_SESSION['user_role'] === 'student') {
            $stu = $this->studentModel->findByUserId($_SESSION['user_id']);
            if (!$stu || (int)$interview['student_id'] !== (int)$stu['id']) {
                http_response_code(403);
                die("Unauthorized access to this interview document");
            }
        } elseif ($_SESSION['user_role'] === 'company') {
            $comp = $this->companyModel->findByUserId($_SESSION['user_id']);
            if (!$comp || (int)$interview['company_id'] !== (int)$comp['id']) {
                http_response_code(403);
                die("Unauthorized access to this interview document");
            }
        }

        try {
            $pdfBinary = PdfGenerator::generateInterviewLetterBinary($interview);
        } catch (Exception $e) {
            error_log("[InterviewController::downloadPdf] PDF generation error: " . $e->getMessage());
            http_response_code(500);
            die("PDF generation failed: " . $e->getMessage());
        }

        $filename = 'Interview_Call_Letter_INV-' . str_pad($invId, 6, '0', STR_PAD_LEFT) . '.pdf';
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($pdfBinary));
        header('Cache-Control: no-cache, must-revalidate');
        echo $pdfBinary;
        exit;
    }
}
