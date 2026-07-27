<?php
/**
 * TPMS - Front Controller / Router
 * All requests are routed through this file
 */

session_name('TPMS_SESSION');
session_start();

// Load configuration
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

// Load middleware
require_once __DIR__ . '/middleware/AuthMiddleware.php';
require_once __DIR__ . '/middleware/CsrfMiddleware.php';
require_once __DIR__ . '/middleware/RoleMiddleware.php';

// Load helpers
require_once __DIR__ . '/includes/helpers.php';

// Generate CSRF token if not exists
CsrfMiddleware::generateToken();

// Get the URL
$url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : '';
$url = filter_var($url, FILTER_SANITIZE_URL);
$urlParts = $url ? explode('/', $url) : [];

// =====================================================
// ROUTING
// =====================================================

// API Routes
if (isset($urlParts[0]) && $urlParts[0] === 'api') {
    $isBinaryRoute = (
        (($urlParts[1] ?? '') === 'application' && ($urlParts[2] ?? '') === 'pdf') ||
        (($urlParts[1] ?? '') === 'interview' && ($urlParts[2] ?? '') === 'pdf') ||
        (($urlParts[1] ?? '') === 'admin' && ($urlParts[2] ?? '') === 'reports')
    );
    if (!$isBinaryRoute) {
        header('Content-Type: application/json');
    }
    if (($urlParts[1] ?? '') === 'theme' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $theme = sanitize($_POST['theme'] ?? 'light');
        $allowed = ['light', 'dark', 'blue', 'purple', 'emerald', 'sunset', 'midnight', 'glassmorphism'];
        if (in_array($theme, $allowed)) {
            $_SESSION['user_theme'] = $theme;
            setcookie('tpms_theme', $theme, time() + 31536000, '/', '', false, false);
            if (AuthMiddleware::isLoggedIn() && isset($_SESSION['user_id'])) {
                $db = Database::getInstance();
                $db->update("UPDATE users SET theme_preference = ? WHERE id = ?", [$theme, $_SESSION['user_id']]);
            }
            jsonResponse(['success' => true, 'theme' => $theme]);
            exit;
        }
    } elseif (($urlParts[1] ?? '') === 'recommendations') {
        require_once __DIR__ . '/controllers/RecommendationController.php';
        $recController = new RecommendationController();
        $subAction = $urlParts[2] ?? '';

        if ($subAction === 'generate' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $recController->apiGenerateRecommendations();
        } elseif ($subAction === 'top') {
            $recController->apiGetTop();
        } elseif ($subAction === 'history') {
            $recController->apiGetHistory();
        } elseif ($subAction === 'analytics') {
            $recController->apiGetAnalytics();
        } else {
            $studentIdParam = is_numeric($subAction) ? (int)$subAction : null;
            $recController->apiGetRecommendations($studentIdParam);
        }
        exit;
    } elseif (($urlParts[1] ?? '') === 'saved-jobs') {
        require_once __DIR__ . '/controllers/SavedJobController.php';
        $savedController = new SavedJobController();
        $param1 = $urlParts[2] ?? null;
        $param2 = $urlParts[3] ?? null;
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'POST') {
            $savedController->saveJob();
        } elseif ($method === 'DELETE') {
            $studentIdParam = is_numeric($param1) ? (int)$param1 : null;
            $jobIdParam = is_numeric($param2) ? (int)$param2 : null;
            $savedController->unsaveJob($studentIdParam, $jobIdParam);
        } else {
            $studentIdParam = is_numeric($param1) ? (int)$param1 : null;
            $savedController->getSavedJobs($studentIdParam);
        }
        exit;
    } elseif (($urlParts[1] ?? '') === 'chat') {
        require_once __DIR__ . '/controllers/ChatController.php';
        $chatController = new ChatController();
        $sub = $urlParts[2] ?? '';

        if ($sub === 'recommend') {
            $chatController->getRecommendations();
        } elseif ($sub === 'history') {
            $chatController->getHistory();
        } elseif ($sub === 'suggestions') {
            $chatController->getSuggestions();
        } else {
            $chatController->sendMessage();
        }
        exit;
    } elseif (($urlParts[1] ?? '') === 'skill-gap') {
        require_once __DIR__ . '/controllers/SkillGapController.php';
        $sgCtrl = new SkillGapController();
        $sub = $urlParts[2] ?? '';

        if ($sub === 'analyze') {
            $sgCtrl->apiAnalyze();
        } elseif ($sub === 'courses') {
            $sgCtrl->apiGetCourses();
        } elseif ($sub === 'progress') {
            $sgCtrl->apiUpdateProgress();
        } elseif ($sub === 'roadmap') {
            $sgCtrl->apiGetRoadmap();
        } else {
            $studentIdParam = is_numeric($sub) ? (int)$sub : null;
            $sgCtrl->apiGetSkillGap($studentIdParam);
        }
        exit;
    } elseif (($urlParts[1] ?? '') === 'admin') {
        require_once __DIR__ . '/controllers/AdminAssistantController.php';
        $adminAst = new AdminAssistantController();
        $sub = $urlParts[2] ?? '';

        if ($sub === 'chat') {
            $adminAst->chat();
        } elseif ($sub === 'recommend-students') {
            $adminAst->recommendStudents();
        } elseif ($sub === 'analytics') {
            $adminAst->analytics();
        } elseif ($sub === 'reports') {
            $adminAst->reports();
        } elseif ($sub === 'eligible-students') {
            $jobId = is_numeric($urlParts[3] ?? null) ? (int)$urlParts[3] : 0;
            $adminAst->recommendStudents($jobId);
        } elseif ($sub === 'notify-shortlist') {
            $adminAst->notifyShortlist();
        } else {
            jsonResponse(['error' => 'Invalid admin API endpoint'], 400);
        }
        exit;
    } elseif (($urlParts[1] ?? '') === 'company' && ($urlParts[2] ?? '') === 'interviews') {
        require_once __DIR__ . '/controllers/InterviewController.php';
        $invCtrl = new InterviewController();
        $sub = $urlParts[3] ?? '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($sub === 'reschedule') {
                $invCtrl->reschedule();
            } elseif ($sub === 'cancel') {
                $invCtrl->cancel();
            } elseif ($sub === 'feedback') {
                $invCtrl->submitFeedback();
            } else {
                $invCtrl->scheduleInterviews();
            }
        } else {
            $invCtrl->getCompanyInterviews();
        }
    } elseif (($urlParts[1] ?? '') === 'interview' && ($urlParts[2] ?? '') === 'pdf') {
        require_once __DIR__ . '/controllers/InterviewController.php';
        $invCtrl = new InterviewController();
        $invId = is_numeric($urlParts[3] ?? null) ? (int)$urlParts[3] : 0;
        $invCtrl->downloadPdf($invId);
        exit;
    }
    jsonResponse(['error' => 'Not found'], 404);
    exit;
}

// Determine controller and action
$page = $urlParts[0] ?? 'home';
$action = $urlParts[1] ?? 'index';
$param = $urlParts[2] ?? null;
$param2 = $urlParts[3] ?? null;

// Route map
switch ($page) {
    // ========================
    // PUBLIC / AUTH ROUTES
    // ========================
    case 'home':
    case '':
        if (AuthMiddleware::isLoggedIn()) {
            $role = $_SESSION['user_role'];
            redirect("/{$role}/dashboard");
        }
        require_once __DIR__ . '/controllers/AuthController.php';
        $controller = new AuthController();
        $controller->loginPage();
        break;

    case 'login':
        require_once __DIR__ . '/controllers/AuthController.php';
        $controller = new AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->login();
        } else {
            $controller->loginPage();
        }
        break;

    case 'register':
        require_once __DIR__ . '/controllers/AuthController.php';
        $controller = new AuthController();
        if ($action === 'student') {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller->registerStudent();
            } else {
                $controller->registerStudentPage();
            }
        } elseif ($action === 'company') {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller->registerCompany();
            } else {
                $controller->registerCompanyPage();
            }
        } else {
            $controller->registerStudentPage();
        }
        break;

    case 'forgot-password':
        require_once __DIR__ . '/controllers/AuthController.php';
        $controller = new AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->forgotPassword();
        } else {
            $controller->forgotPasswordPage();
        }
        break;

    case 'reset-password':
        require_once __DIR__ . '/controllers/AuthController.php';
        $controller = new AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->resetPassword();
        } else {
            $controller->resetPasswordPage();
        }
        break;

    case 'verify-email':
    case 'verify-otp':
        require_once __DIR__ . '/controllers/AuthController.php';
        $controller = new AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->verifyEmail();
        } else {
            $controller->verifyEmailPage();
        }
        break;

    case 'resend-otp':
        require_once __DIR__ . '/controllers/AuthController.php';
        $controller = new AuthController();
        $controller->resendOTP();
        break;

    case 'logout':
        require_once __DIR__ . '/controllers/AuthController.php';
        $controller = new AuthController();
        $controller->logout();
        break;

    // ========================
    // STUDENT ROUTES
    // ========================
    case 'student':
        AuthMiddleware::requireLogin();
        RoleMiddleware::requireRole('student');
        require_once __DIR__ . '/controllers/StudentController.php';
        $controller = new StudentController();

        switch ($action) {
            case 'dashboard': $controller->dashboard(); break;
            case 'profile':
                if ($param === 'edit') {
                    $_SERVER['REQUEST_METHOD'] === 'POST' ? $controller->updateProfile() : $controller->editProfile();
                } else {
                    $controller->profile();
                }
                break;
            case 'upload-photo': $controller->uploadPhoto(); break;
            case 'upload-resume': $controller->uploadResume(); break;
            case 'delete-resume': $controller->deleteResume(); break;
            case 'download-resume': $controller->downloadResume(); break;
            case 'preview-resume': $controller->previewResume(); break;
            case 'upload-document': $controller->uploadDocument(); break;
            case 'delete-document': $controller->deleteDocument($param); break;
            case 'jobs': $controller->jobs(); break;
            case 'apply': $controller->applyJob($param); break;
            case 'withdraw': $controller->withdrawApplication($param); break;
            case 'applications': $controller->applications(); break;
            case 'recommendations':
                require_once __DIR__ . '/controllers/RecommendationController.php';
                $recController = new RecommendationController();
                $recController->studentRecommendations();
                break;
            case 'skill-gap':
                require_once __DIR__ . '/controllers/SkillGapController.php';
                $sgController = new SkillGapController();
                $sgController->skillGapDashboard();
                break;
            case 'trainings': $controller->trainings(); break;
            case 'register-training': $controller->registerTraining($param); break;
            case 'higher-studies': $controller->higherStudies(); break;
            case 'register-higher-study': $controller->registerHigherStudy(); break;
            case 'notifications': $controller->notifications(); break;
            case 'interviews': $controller->interviews(); break;
            case 'bookmarks': $controller->bookmarks(); break;
            case 'bookmark': $controller->toggleBookmark($param); break;
            case 'change-password':
                $_SERVER['REQUEST_METHOD'] === 'POST' ? $controller->changePassword() : $controller->changePasswordPage();
                break;
            case 'add-project': $controller->addProject(); break;
            case 'delete-project': $controller->deleteProject($param); break;
            case 'add-certification': $controller->addCertification(); break;
            case 'delete-certification': $controller->deleteCertification($param); break;
            case 'add-language': $controller->addLanguage(); break;
            case 'delete-language': $controller->deleteLanguage($param); break;
            case 'add-achievement': $controller->addAchievement(); break;
            case 'delete-achievement': $controller->deleteAchievement($param); break;
            default: $controller->dashboard(); break;
        }
        break;

    // ========================
    // COMPANY ROUTES
    // ========================
    case 'company':
        AuthMiddleware::requireLogin();
        RoleMiddleware::requireRole('company');
        require_once __DIR__ . '/controllers/CompanyController.php';
        $controller = new CompanyController();

        switch ($action) {
            case 'dashboard': $controller->dashboard(); break;
            case 'profile':
                $_SERVER['REQUEST_METHOD'] === 'POST' ? $controller->updateProfile() : $controller->profile();
                break;
            case 'post-job':
                $_SERVER['REQUEST_METHOD'] === 'POST' ? $controller->postJob() : $controller->postJobPage();
                break;
            case 'jobs': $controller->jobs(); break;
            case 'edit-job':
                $_SERVER['REQUEST_METHOD'] === 'POST' ? $controller->editJob($param) : $controller->editJobPage($param);
                break;
            case 'delete-job': $controller->deleteJob($param); break;
            case 'applications': $controller->viewApplications($param); break;
            case 'update-application':
                $controller->updateApplicationStatus($param);
                break;
            case 'schedule-interview':
                $controller->scheduleInterview($param);
                break;
            case 'edit-interview':
                $controller->updateInterview($param);
                break;
            case 'cancel-interview':
                $controller->cancelInterview($param);
                break;
            case 'interviews': $controller->interviews(); break;
            case 'interview-result': $controller->updateInterviewResult($param); break;
            case 'notifications': $controller->notifications(); break;
            default: $controller->dashboard(); break;
        }
        break;

    // ========================
    // ADMIN ROUTES
    // ========================
    case 'admin':
        AuthMiddleware::requireLogin();
        RoleMiddleware::requireRole('admin');
        require_once __DIR__ . '/controllers/AdminController.php';
        $controller = new AdminController();

        switch ($action) {
            case 'dashboard': $controller->dashboard(); break;
            case 'students': $controller->students(); break;
            case 'view-student': $controller->viewStudent($param); break;
            case 'update-student-status': $controller->updateStudentStatus($param); break;
            case 'mark-placed': $controller->markPlaced($param); break;
            case 'delete-student': $controller->deleteStudent($param); break;
            case 'companies': $controller->companies(); break;
            case 'approve-company': $controller->approveCompany($param); break;
            case 'reject-company': $controller->rejectCompany($param); break;
            case 'delete-company': $controller->deleteCompany($param); break;
            case 'jobs': $controller->jobs(); break;
            case 'approve-job': $controller->approveJob($param); break;
            case 'close-job': $controller->closeJob($param); break;
            case 'delete-job': $controller->deleteJob($param); break;
            case 'placements': $controller->placements(); break;
            case 'trainings': $controller->trainings(); break;
            case 'create-training': $controller->createTraining(); break;
            case 'delete-training': $controller->deleteTraining($param); break;
            case 'higher-studies': $controller->higherStudies(); break;
            case 'update-higher-study': $controller->updateHigherStudyStatus($param); break;
            case 'create-university': $controller->createUniversity(); break;
            case 'interviews': $controller->interviews(); break;
            case 'schedule-interview': $controller->scheduleInterview(); break;
            case 'edit-interview': $controller->updateInterview($param); break;
            case 'cancel-interview': $controller->cancelInterview($param); break;
            case 'interview-result': $controller->updateInterviewResult($param); break;
            case 'approvals': $controller->approvals(); break;
            case 'notifications': $controller->notifications(); break;
            case 'send-notification': $controller->sendNotification(); break;
            case 'faculty': $controller->faculty(); break;
            case 'create-faculty': $controller->createFaculty(); break;
            case 'delete-faculty': $controller->deleteFaculty($param); break;
            case 'reports': $controller->reports(); break;
            case 'recommendation-analytics':
                require_once __DIR__ . '/controllers/RecommendationController.php';
                $recController = new RecommendationController();
                $recController->adminAnalytics();
                break;
            case 'logs': $controller->logs(); break;
            case 'settings': $controller->settings(); break;
            default: $controller->dashboard(); break;
        }
        break;

    // ========================
    // SEARCH
    // ========================
    case 'search':
        AuthMiddleware::requireLogin();
        require_once __DIR__ . '/controllers/SearchController.php';
        $controller = new SearchController();
        $controller->globalSearch();
        break;

    // ========================
    // NOTIFICATIONS API (AJAX)
    // ========================
    case 'notifications':
        AuthMiddleware::requireLogin();
        require_once __DIR__ . '/controllers/NotificationController.php';
        $controller = new NotificationController();
        if ($action === 'fetch') {
            $controller->fetchUnread();
        } elseif ($action === 'mark-read') {
            $controller->markRead($param);
        } elseif ($action === 'mark-all-read') {
            $controller->markAllRead();
        } elseif ($action === 'count') {
            $controller->getUnreadCount();
        }
        break;

    // ========================
    // 404
    // ========================
    default:
        http_response_code(404);
        require_once VIEWS_PATH . '/errors/404.php';
        break;
}
