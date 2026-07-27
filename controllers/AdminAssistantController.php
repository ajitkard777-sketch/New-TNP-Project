<?php
/**
 * TPMS - AI Admin Assistant Controller
 * Handles REST APIs for Admin AI Chatbot, Candidate Recommendations, Reports, & Shortlists
 */

require_once ROOT_PATH . '/models/AIAdminEngine.php';

class AdminAssistantController {
    private AIAdminEngine $engine;
    private Database $db;

    public function __construct() {
        $this->engine = new AIAdminEngine();
        $this->db = Database::getInstance();
    }

    /**
     * Enforce strict admin authorization check
     */
    private function checkAdminAuth(): void {
        AuthMiddleware::requireLogin();
        if ($_SESSION['user_role'] !== 'admin') {
            jsonResponse(['error' => 'Unauthorized admin access required'], 403);
            exit;
        }
    }

    /**
     * POST /api/admin/chat
     */
    public function chat(): void {
        $this->checkAdminAuth();

        $raw = file_get_contents('php://input');
        $json = json_decode($raw, true);
        $message = sanitize($_POST['message'] ?? $json['message'] ?? '');

        if (empty($message)) {
            jsonResponse(['error' => 'Message is required'], 400);
            return;
        }

        $adminUserId = (int)$_SESSION['user_id'];
        $result = $this->engine->processAdminChatMessage($adminUserId, $message);

        jsonResponse([
            'success' => true,
            'message' => $message,
            'response' => $result['response'],
            'type' => $result['type'] ?? 'text',
            'cards' => $result['cards'] ?? [],
            'timestamp' => date('h:i A')
        ]);
    }

    /**
     * POST /api/admin/recommend-students or GET /api/admin/eligible-students/:jobId
     */
    public function recommendStudents(?int $jobIdParam = null): void {
        $this->checkAdminAuth();

        $raw = file_get_contents('php://input');
        $json = json_decode($raw, true);

        $jobId = $jobIdParam ?: (int)($_POST['job_id'] ?? $_GET['job_id'] ?? $json['job_id'] ?? 0);
        if ($jobId <= 0) {
            jsonResponse(['error' => 'Invalid job_id'], 400);
            return;
        }

        $filters = [
            'branch' => sanitize($_GET['branch'] ?? $_POST['branch'] ?? $json['branch'] ?? ''),
            'min_cgpa' => $_GET['min_cgpa'] ?? $_POST['min_cgpa'] ?? $json['min_cgpa'] ?? null,
            'has_resume' => $_GET['has_resume'] ?? $_POST['has_resume'] ?? $json['has_resume'] ?? null,
            'skill_search' => sanitize($_GET['skill_search'] ?? $_POST['skill_search'] ?? $json['skill_search'] ?? '')
        ];

        $result = $this->engine->recommendStudentsForJob($jobId, array_filter($filters));

        jsonResponse([
            'success' => true,
            'job' => $result['job'],
            'total_candidates' => $result['total_students'],
            'students' => $result['students']
        ]);
    }

    /**
     * GET /api/admin/analytics
     */
    public function analytics(): void {
        $this->checkAdminAuth();
        $analytics = $this->engine->getAdminAnalytics();

        jsonResponse([
            'success' => true,
            'analytics' => $analytics
        ]);
    }

    /**
     * GET /api/admin/reports
     */
    public function reports(): void {
        $this->checkAdminAuth();

        $type = sanitize($_GET['type'] ?? 'placements');
        $format = strtolower(sanitize($_GET['format'] ?? 'csv'));

        $report = $this->engine->generateReport($type, $format);

        header('Content-Type: ' . $report['mime']);
        header('Content-Disposition: attachment; filename="' . $report['filename'] . '"');
        echo $report['content'];
        exit;
    }

    /**
     * POST /api/admin/notify-shortlist
     */
    public function notifyShortlist(): void {
        $this->checkAdminAuth();

        $raw = file_get_contents('php://input');
        $json = json_decode($raw, true);

        $jobId = (int)($_POST['job_id'] ?? $json['job_id'] ?? 0);
        $studentIds = $_POST['student_ids'] ?? $json['student_ids'] ?? [];

        if ($jobId <= 0 || empty($studentIds)) {
            jsonResponse(['error' => 'job_id and student_ids array required'], 400);
            return;
        }

        $job = $this->db->fetchOne("SELECT j.title, c.company_name FROM jobs j JOIN companies c ON j.company_id = c.id WHERE j.id = ?", [$jobId]);
        if (!$job) {
            jsonResponse(['error' => 'Job not found'], 404);
            return;
        }

        $notifiedCount = 0;
        foreach ((array)$studentIds as $stuId) {
            $stuId = (int)$stuId;
            $user = $this->db->fetchOne("SELECT user_id FROM students WHERE id = ?", [$stuId]);
            if ($user) {
                // UPSERT Shortlist
                $this->db->query(
                    "INSERT INTO student_shortlists (job_id, student_id, status) VALUES (?, ?, 'invited')
                     ON DUPLICATE KEY UPDATE status = 'invited'",
                    [$jobId, $stuId]
                );

                // Send Notification
                $this->db->query(
                    "INSERT INTO notifications (user_id, title, message, type, category) VALUES (?, ?, ?, ?, ?)",
                    [
                        $user['user_id'],
                        'Interview Invitation - ' . $job['company_name'],
                        "You have been shortlisted and invited for an interview drive: {$job['title']} at {$job['company_name']}.",
                        'info',
                        'interview'
                    ]
                );
                $notifiedCount++;
            }
        }

        jsonResponse([
            'success' => true,
            'message' => "Successfully sent interview invitations to {$notifiedCount} candidates!",
            'notified_count' => $notifiedCount
        ]);
    }
}
