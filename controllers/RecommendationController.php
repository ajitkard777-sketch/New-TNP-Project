<?php
/**
 * TPMS - Recommendation Controller
 * Handles student recommendations view, admin analytics view, and REST API endpoints
 */

require_once ROOT_PATH . '/models/RecommendationEngine.php';
require_once ROOT_PATH . '/models/Student.php';

class RecommendationController {
    private RecommendationEngine $engine;
    private Student $studentModel;

    public function __construct() {
        $this->engine = new RecommendationEngine();
        $this->studentModel = new Student();
    }

    /**
     * View: Student Recommendations Dashboard (/student/recommendations)
     */
    public function studentRecommendations(): void {
        AuthMiddleware::requireLogin();
        RoleMiddleware::requireRole('student');

        $userId = $_SESSION['user_id'];
        $student = $this->studentModel->findByUserId($userId);

        if (!$student) {
            setFlash('danger', 'Student profile not found.');
            redirect('/student/dashboard');
        }

        // Regenerate or update recommendations for student
        $this->engine->generateForStudent((int)$student['id']);

        // Collect filters from GET params
        $filters = [
            'min_score' => sanitize($_GET['min_score'] ?? ''),
            'location' => sanitize($_GET['location'] ?? ''),
            'package' => sanitize($_GET['package'] ?? ''),
            'company' => sanitize($_GET['company'] ?? ''),
            'job_type' => sanitize($_GET['type'] ?? ''),
            'work_mode' => sanitize($_GET['work_mode'] ?? ''),
            'search' => sanitize($_GET['search'] ?? ''),
            'sort_by' => sanitize($_GET['sort_by'] ?? 'score')
        ];

        $recommendations = $this->engine->getStudentRecommendations((int)$student['id'], $filters);
        
        // Categorized lists for tabs
        $topRecommendations = array_values(array_filter($recommendations, fn($r) => $r['recommendation_score'] >= 60));
        $recentJobs = array_values($this->engine->getStudentRecommendations((int)$student['id'], array_merge($filters, ['sort_by' => 'recent'])));
        $highestSalaryJobs = array_values($this->engine->getStudentRecommendations((int)$student['id'], array_merge($filters, ['sort_by' => 'salary'])));
        $savedRecommendations = array_values(array_filter($recommendations, fn($r) => (int)$r['is_bookmarked'] === 1));

        $currentPage = 'recommendations';
        $currentRole = 'student';
        $userName = $_SESSION['user_name'] ?? 'Student';
        $userAvatar = !empty($student['profile_photo']) ? uploadUrl('profile_photos/' . $student['profile_photo']) : asset('images/default-avatar.png');

        require_once VIEWS_PATH . '/student/recommendations.php';
    }

    /**
     * View: Admin Recommendation Analytics (/admin/recommendation-analytics)
     */
    public function adminAnalytics(): void {
        AuthMiddleware::requireLogin();
        RoleMiddleware::requireRole('admin');

        $analytics = $this->engine->getAdminAnalytics();

        $currentPage = 'recommendation-analytics';
        $currentRole = 'admin';
        $userName = $_SESSION['user_name'] ?? 'Admin';
        $userAvatar = asset('images/default-avatar.png');

        require_once VIEWS_PATH . '/admin/recommendation-analytics.php';
    }

    /**
     * REST API: GET /api/recommendations/:studentId or /api/recommendations
     */
    public function apiGetRecommendations(?int $studentIdParam = null): void {
        AuthMiddleware::requireLogin();

        $studentId = 0;
        if ($studentIdParam && $studentIdParam > 0) {
            // Admin or student checking specific ID
            $studentId = $studentIdParam;
        } elseif ($_SESSION['user_role'] === 'student') {
            $student = $this->studentModel->findByUserId($_SESSION['user_id']);
            $studentId = $student ? (int)$student['id'] : 0;
        }

        if ($studentId <= 0) {
            jsonResponse(['error' => 'Student not found or invalid student ID'], 404);
            return;
        }

        $filters = [
            'min_score' => sanitize($_GET['min_score'] ?? ''),
            'location' => sanitize($_GET['location'] ?? ''),
            'package' => sanitize($_GET['package'] ?? ''),
            'company' => sanitize($_GET['company'] ?? ''),
            'job_type' => sanitize($_GET['job_type'] ?? ''),
            'work_mode' => sanitize($_GET['work_mode'] ?? ''),
            'search' => sanitize($_GET['search'] ?? '')
        ];

        $recommendations = $this->engine->getStudentRecommendations($studentId, $filters);
        jsonResponse([
            'success' => true,
            'student_id' => $studentId,
            'count' => count($recommendations),
            'recommendations' => $recommendations
        ]);
    }

    /**
     * REST API: POST /api/recommendations/generate
     */
    public function apiGenerateRecommendations(): void {
        AuthMiddleware::requireLogin();

        $targetStudentId = null;
        if (isset($_POST['student_id']) && (int)$_POST['student_id'] > 0) {
            $targetStudentId = (int)$_POST['student_id'];
        } elseif ($_SESSION['user_role'] === 'student') {
            $student = $this->studentModel->findByUserId($_SESSION['user_id']);
            $targetStudentId = $student ? (int)$student['id'] : null;
        }

        if ($targetStudentId) {
            $generated = $this->engine->generateForStudent($targetStudentId);
            jsonResponse([
                'success' => true,
                'message' => "Generated recommendations for student ID $targetStudentId",
                'jobs_processed' => $generated
            ]);
        } else {
            // Bulk generation if admin
            RoleMiddleware::requireRole('admin');
            $generated = $this->engine->generateForAllStudents();
            jsonResponse([
                'success' => true,
                'message' => "Generated recommendations for all active students",
                'total_processed' => $generated
            ]);
        }
    }

    /**
     * REST API: GET /api/recommendations/top
     */
    public function apiGetTop(): void {
        AuthMiddleware::requireLogin();

        $studentId = 0;
        if ($_SESSION['user_role'] === 'student') {
            $student = $this->studentModel->findByUserId($_SESSION['user_id']);
            $studentId = $student ? (int)$student['id'] : 0;
        } elseif (isset($_GET['student_id'])) {
            $studentId = (int)$_GET['student_id'];
        }

        if ($studentId <= 0) {
            jsonResponse(['error' => 'Invalid student request'], 400);
            return;
        }

        $top = $this->engine->getTopRecommendations($studentId, 5);
        jsonResponse([
            'success' => true,
            'top_recommendations' => array_slice($top, 0, 5)
        ]);
    }

    /**
     * REST API: GET /api/recommendations/history
     */
    public function apiGetHistory(): void {
        AuthMiddleware::requireLogin();

        $studentId = 0;
        if ($_SESSION['user_role'] === 'student') {
            $student = $this->studentModel->findByUserId($_SESSION['user_id']);
            $studentId = $student ? (int)$student['id'] : 0;
        } elseif (isset($_GET['student_id'])) {
            $studentId = (int)$_GET['student_id'];
        }

        if ($studentId <= 0) {
            jsonResponse(['error' => 'Invalid student request'], 400);
            return;
        }

        $recs = $this->engine->getStudentRecommendations($studentId);
        jsonResponse([
            'success' => true,
            'total_history' => count($recs),
            'history' => $recs
        ]);
    }

    /**
     * REST API: GET /api/recommendations/analytics
     */
    public function apiGetAnalytics(): void {
        AuthMiddleware::requireLogin();
        RoleMiddleware::requireRole('admin');

        $analytics = $this->engine->getAdminAnalytics();
        jsonResponse([
            'success' => true,
            'analytics' => $analytics
        ]);
    }
}
