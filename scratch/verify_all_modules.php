<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../models/Company.php';
require_once __DIR__ . '/../models/Job.php';
require_once __DIR__ . '/../models/Message.php';

echo "=================================================================\n";
echo "       TPMS COMPREHENSIVE SYSTEM INTEGRITY & DIAGNOSTIC TEST     \n";
echo "=================================================================\n\n";

$db = Database::getInstance();

$errorsFound = [];
$warningsFound = [];
$testsPassed = 0;

function reportTest($name, $status, $msg = '') {
    global $testsPassed, $errorsFound, $warningsFound;
    if ($status === 'PASS') {
        $testsPassed++;
        echo "  [PASS] {$name} " . ($msg ? "($msg)" : "") . "\n";
    } elseif ($status === 'WARN') {
        $warningsFound[] = "{$name}: {$msg}";
        echo "  [WARN] ⚠️ {$name}: {$msg}\n";
    } else {
        $errorsFound[] = "{$name}: {$msg}";
        echo "  [FAIL] ❌ {$name}: {$msg}\n";
    }
}

// -------------------------------------------------------------
// TEST 1: Database Connection & Table Verification
// -------------------------------------------------------------
echo "1. Database & Table Integrity Check\n";
echo "-------------------------------------------------------------\n";
try {
    $pdo = $db->getConnection();
    reportTest("Database Connection", "PASS", "Connected to team1");

    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $requiredTables = [
        'users', 'students', 'companies', 'jobs', 'applications',
        'interviews', 'trainings', 'training_registrations',
        'universities', 'higher_study_applications',
        'notifications', 'messages', 'activity_logs', 'password_resets',
        'skill_gap_analysis', 'bookmarks'
    ];

    foreach ($requiredTables as $tbl) {
        if (in_array($tbl, $tables)) {
            $count = $db->fetchColumn("SELECT COUNT(*) FROM `{$tbl}`");
            reportTest("Table: {$tbl}", "PASS", "{$count} rows");
        } else {
            reportTest("Table: {$tbl}", "FAIL", "Table missing from database");
        }
    }
} catch (Exception $e) {
    reportTest("Database Integrity", "FAIL", $e->getMessage());
}

// -------------------------------------------------------------
// TEST 2: Controller & Model Instantiation
// -------------------------------------------------------------
echo "\n2. Controller & Model Instantiation Check\n";
echo "-------------------------------------------------------------\n";

$controllers = [
    'AdminController', 'AuthController', 'CompanyController',
    'StudentController', 'InterviewController', 'RecommendationController',
    'SavedJobController', 'SearchController', 'SkillGapController',
    'MessageController', 'ChatController', 'AdminAssistantController'
];

foreach ($controllers as $ctrl) {
    $file = __DIR__ . "/../controllers/{$ctrl}.php";
    if (file_exists($file)) {
        require_once $file;
        if (class_exists($ctrl)) {
            try {
                $instance = new $ctrl();
                reportTest("Controller: {$ctrl}", "PASS", "Instantiated successfully");
            } catch (Throwable $e) {
                reportTest("Controller: {$ctrl}", "FAIL", "Constructor error: " . $e->getMessage());
            }
        } else {
            reportTest("Controller: {$ctrl}", "FAIL", "Class {$ctrl} not defined in file");
        }
    } else {
        reportTest("Controller: {$ctrl}", "FAIL", "File controllers/{$ctrl}.php missing");
    }
}

// -------------------------------------------------------------
// TEST 3: PHP Files Syntax Check (Linting)
// -------------------------------------------------------------
echo "\n3. PHP File Syntax Check\n";
echo "-------------------------------------------------------------\n";

$phpDirectories = ['config', 'controllers', 'models', 'includes', 'middleware', 'views', 'services'];
$syntaxErrors = 0;

foreach ($phpDirectories as $dir) {
    $dirPath = dirname(__DIR__) . '/' . $dir;
    if (!is_dir($dirPath)) continue;

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dirPath));
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $path = $file->getRealPath();
            $cmd = sprintf('C:\xampp\php\php.exe -l "%s"', $path);
            $output = [];
            $returnVar = 0;
            exec($cmd, $output, $returnVar);
            if ($returnVar !== 0) {
                $syntaxErrors++;
                reportTest("Syntax: " . basename($path), "FAIL", implode(" ", $output));
            }
        }
    }
}
if ($syntaxErrors === 0) {
    reportTest("PHP Syntax Linting", "PASS", "All PHP files clean with zero syntax errors");
}

// -------------------------------------------------------------
// TEST 4: Helper Functions & Asset Configuration
// -------------------------------------------------------------
echo "\n4. Helpers & Asset Configuration\n";
echo "-------------------------------------------------------------\n";
$requiredFunctions = ['url', 'asset', 'getFlash', 'setFlash', 'logActivity', 'sanitize', 'isAjax', 'jsonResponse'];
foreach ($requiredFunctions as $fn) {
    if (function_exists($fn)) {
        reportTest("Helper Function: {$fn}()", "PASS");
    } else {
        reportTest("Helper Function: {$fn}()", "FAIL", "Function not defined");
    }
}

// -------------------------------------------------------------
// SUMMARY REPORT
// -------------------------------------------------------------
echo "\n=================================================================\n";
echo "                       DIAGNOSTIC SUMMARY                        \n";
echo "=================================================================\n";
echo "  Total Checks Passed : {$testsPassed}\n";
echo "  Warnings            : " . count($warningsFound) . "\n";
echo "  Errors              : " . count($errorsFound) . "\n";

if (!empty($errorsFound)) {
    echo "\nERRORS DETECTED:\n";
    foreach ($errorsFound as $err) {
        echo " ❌ {$err}\n";
    }
}
echo "=================================================================\n";
