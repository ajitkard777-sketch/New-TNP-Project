<?php
/**
 * TPMS - Skill Gap Analysis Controller
 * Handles web dashboard view and REST API endpoints for Skill Gap & Course Recommendations
 */

require_once ROOT_PATH . '/models/SkillGapEngine.php';
require_once ROOT_PATH . '/models/Student.php';

class SkillGapController {
    private SkillGapEngine $engine;
    private Student $studentModel;

    public function __construct() {
        $this->engine = new SkillGapEngine();
        $this->studentModel = new Student();
    }

    /**
     * View: Student Skill Gap Analysis Dashboard (/student/skill-gap)
     */
    public function skillGapDashboard(): void {
        AuthMiddleware::requireLogin();
        RoleMiddleware::requireRole('student');

        $userId = $_SESSION['user_id'];
        $student = $this->studentModel->findByUserId($userId);

        if (!$student) {
            setFlash('danger', 'Student profile not found.');
            redirect('/student/dashboard');
        }

        $studentId = (int)$student['id'];

        // Perform analysis
        $insights = $this->engine->getSkillInsights($studentId);
        $missingSkillNames = array_column($insights['missing_skills'], 'skill_name');

        $searchQuery = sanitize($_GET['search'] ?? '');
        $courses = $this->engine->getRecommendedCourses($studentId, $missingSkillNames, $searchQuery);
        $roadmap = $this->engine->generateRoadmap($studentId);

        // Filter course groups
        $enrolledCourses = array_values(array_filter($courses, fn($c) => $c['is_enrolled'] && !$c['is_completed']));
        $completedCourses = array_values(array_filter($courses, fn($c) => $c['is_completed']));

        $currentPage = 'skill-gap';
        $currentRole = 'student';
        $userName = $_SESSION['user_name'] ?? 'Student';
        $userAvatar = !empty($student['profile_photo']) ? uploadUrl('profile_photos/' . $student['profile_photo']) : asset('images/default-avatar.png');

        require_once VIEWS_PATH . '/student/skill-gap.php';
    }

    /**
     * REST API: GET /api/skill-gap/:studentId or /api/skill-gap
     */
    public function apiGetSkillGap(?int $studentIdParam = null): void {
        AuthMiddleware::requireLogin();

        $studentId = $studentIdParam && $studentIdParam > 0 ? $studentIdParam : null;
        if (!$studentId && $_SESSION['user_role'] === 'student') {
            $student = $this->studentModel->findByUserId($_SESSION['user_id']);
            $studentId = $student ? (int)$student['id'] : null;
        }

        if (!$studentId) {
            jsonResponse(['error' => 'Invalid student request'], 400);
            return;
        }

        $insights = $this->engine->getSkillInsights($studentId);
        jsonResponse([
            'success' => true,
            'student_id' => $studentId,
            'insights' => $insights
        ]);
    }

    /**
     * REST API: POST /api/skill-gap/analyze
     */
    public function apiAnalyze(): void {
        AuthMiddleware::requireLogin();

        $studentId = null;
        if ($_SESSION['user_role'] === 'student') {
            $student = $this->studentModel->findByUserId($_SESSION['user_id']);
            $studentId = $student ? (int)$student['id'] : null;
        }

        if (!$studentId) {
            jsonResponse(['error' => 'Unauthorized or missing student profile'], 401);
            return;
        }

        $result = $this->engine->analyzeStudentSkillGap($studentId);
        $insights = $this->engine->getSkillInsights($studentId);

        jsonResponse([
            'success' => true,
            'message' => 'Skill gap analysis updated successfully',
            'result' => $result,
            'insights' => $insights
        ]);
    }

    /**
     * REST API: GET /api/skill-gap/courses
     */
    public function apiGetCourses(): void {
        AuthMiddleware::requireLogin();

        $studentId = null;
        if ($_SESSION['user_role'] === 'student') {
            $student = $this->studentModel->findByUserId($_SESSION['user_id']);
            $studentId = $student ? (int)$student['id'] : null;
        }

        if (!$studentId) {
            jsonResponse(['error' => 'Student missing'], 400);
            return;
        }

        $search = sanitize($_GET['search'] ?? '');
        $insights = $this->engine->getSkillInsights($studentId);
        $missingSkills = array_column($insights['missing_skills'], 'skill_name');

        $courses = $this->engine->getRecommendedCourses($studentId, $missingSkills, $search);

        jsonResponse([
            'success' => true,
            'count' => count($courses),
            'courses' => $courses
        ]);
    }

    /**
     * REST API: POST /api/skill-gap/progress
     */
    public function apiUpdateProgress(): void {
        AuthMiddleware::requireLogin();

        $studentId = null;
        if ($_SESSION['user_role'] === 'student') {
            $student = $this->studentModel->findByUserId($_SESSION['user_id']);
            $studentId = $student ? (int)$student['id'] : null;
        }

        if (!$studentId) {
            jsonResponse(['error' => 'Unauthorized'], 401);
            return;
        }

        $raw = file_get_contents('php://input');
        $json = json_decode($raw, true);

        $courseId = (int)($_POST['course_id'] ?? $json['course_id'] ?? 0);
        $status = sanitize($_POST['status'] ?? $json['status'] ?? 'enrolled');
        $progress = (int)($_POST['progress'] ?? $json['progress'] ?? 50);

        if ($courseId <= 0) {
            jsonResponse(['error' => 'Invalid course_id'], 400);
            return;
        }

        $this->engine->updateCourseProgress($studentId, $courseId, $status, $progress);

        jsonResponse([
            'success' => true,
            'message' => 'Learning progress updated successfully',
            'course_id' => $courseId,
            'status' => $status,
            'progress' => $progress
        ]);
    }

    /**
     * REST API: GET /api/skill-gap/roadmap
     */
    public function apiGetRoadmap(): void {
        AuthMiddleware::requireLogin();

        $studentId = null;
        if ($_SESSION['user_role'] === 'student') {
            $student = $this->studentModel->findByUserId($_SESSION['user_id']);
            $studentId = $student ? (int)$student['id'] : null;
        }

        if (!$studentId) {
            jsonResponse(['error' => 'Student missing'], 400);
            return;
        }

        $roadmap = $this->engine->generateRoadmap($studentId);

        jsonResponse([
            'success' => true,
            'roadmap' => $roadmap
        ]);
    }
}
