<?php
/**
 * TPMS - Saved Jobs Controller
 * Handles REST APIs for Save to Playlist / Bookmarks feature
 */

require_once ROOT_PATH . '/models/Student.php';

class SavedJobController {
    private Database $db;
    private Student $studentModel;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->studentModel = new Student();
    }

    /**
     * Helper to retrieve authenticated student ID
     */
    private function getAuthenticatedStudentId(): ?int {
        if (!AuthMiddleware::isLoggedIn()) return null;
        if ($_SESSION['user_role'] !== 'student') return null;
        $student = $this->studentModel->findByUserId($_SESSION['user_id']);
        return $student ? (int)$student['id'] : null;
    }

    /**
     * POST /api/saved-jobs
     * Save a job to student's playlist / saved jobs
     */
    public function saveJob(): void {
        AuthMiddleware::requireLogin();
        $studentId = $this->getAuthenticatedStudentId();

        if (!$studentId) {
            jsonResponse(['success' => false, 'error' => 'Unauthorized or student profile missing'], 401);
            return;
        }

        // Parse input from POST or JSON payload
        $rawInput = file_get_contents('php://input');
        $json = json_decode($rawInput, true);
        
        $jobId = (int)($_POST['job_id'] ?? $json['job_id'] ?? 0);

        if ($jobId <= 0) {
            jsonResponse(['success' => false, 'error' => 'Invalid or missing job_id parameter'], 400);
            return;
        }

        // Verify job exists and is active
        $job = $this->db->fetchOne("SELECT id, title FROM jobs WHERE id = ?", [$jobId]);
        if (!$job) {
            jsonResponse(['success' => false, 'error' => 'Job not found'], 444);
            return;
        }

        // Check if already saved
        $existing = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM saved_jobs WHERE student_id = ? AND job_id = ?", [$studentId, $jobId]);
        
        if ($existing > 0) {
            jsonResponse([
                'success' => true,
                'already_saved' => true,
                'saved' => true,
                'message' => 'Job is already saved in your playlist'
            ]);
            return;
        }

        // Insert into saved_jobs table (and sync with bookmarks for backwards compatibility)
        $this->db->query("INSERT INTO saved_jobs (student_id, job_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE created_at = NOW()", [$studentId, $jobId]);
        $this->db->query("INSERT IGNORE INTO bookmarks (student_id, job_id) VALUES (?, ?)", [$studentId, $jobId]);

        jsonResponse([
            'success' => true,
            'saved' => true,
            'job_id' => $jobId,
            'message' => 'Job saved to playlist successfully'
        ], 201);
    }

    /**
     * GET /api/saved-jobs/:studentId
     * Fetch saved jobs list for a student
     */
    public function getSavedJobs(?int $studentIdParam = null): void {
        AuthMiddleware::requireLogin();
        $authStudentId = $this->getAuthenticatedStudentId();

        $studentId = $studentIdParam && $studentIdParam > 0 ? $studentIdParam : $authStudentId;

        if (!$studentId) {
            jsonResponse(['success' => false, 'error' => 'Invalid student ID or unauthenticated'], 400);
            return;
        }

        // Fetch saved jobs joined with job and company details
        $sql = "SELECT sj.id as saved_id, sj.created_at as saved_at,
                       j.id as job_id, j.title, j.description, j.job_type, j.work_mode, j.location, j.salary_min, j.salary_max, j.openings, j.skills_required, j.eligibility_cgpa, j.application_deadline,
                       c.company_name, c.logo
                FROM saved_jobs sj
                JOIN jobs j ON sj.job_id = j.id
                JOIN companies c ON j.company_id = c.id
                WHERE sj.student_id = ? AND j.status = 'active'
                ORDER BY sj.created_at DESC";

        $savedJobs = $this->db->fetchAll($sql, [$studentId]);

        jsonResponse([
            'success' => true,
            'student_id' => $studentId,
            'count' => count($savedJobs),
            'saved_jobs' => $savedJobs
        ]);
    }

    /**
     * DELETE /api/saved-jobs/:studentId/:jobId or DELETE /api/saved-jobs
     * Remove / unsave a job from playlist
     */
    public function unsaveJob(?int $studentIdParam = null, ?int $jobIdParam = null): void {
        AuthMiddleware::requireLogin();
        $authStudentId = $this->getAuthenticatedStudentId();

        $rawInput = file_get_contents('php://input');
        $json = json_decode($rawInput, true);

        $studentId = $studentIdParam && $studentIdParam > 0 ? $studentIdParam : $authStudentId;
        $jobId = $jobIdParam && $jobIdParam > 0 ? $jobIdParam : (int)($_GET['job_id'] ?? $_POST['job_id'] ?? $json['job_id'] ?? 0);

        if (!$studentId || $jobId <= 0) {
            jsonResponse(['success' => false, 'error' => 'Invalid student ID or job ID'], 400);
            return;
        }

        // Delete from saved_jobs and bookmarks
        $this->db->query("DELETE FROM saved_jobs WHERE student_id = ? AND job_id = ?", [$studentId, $jobId]);
        $this->db->query("DELETE FROM bookmarks WHERE student_id = ? AND job_id = ?", [$studentId, $jobId]);

        jsonResponse([
            'success' => true,
            'saved' => false,
            'job_id' => $jobId,
            'message' => 'Job removed from saved playlist'
        ]);
    }
}
