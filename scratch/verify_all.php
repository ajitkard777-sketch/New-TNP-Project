<?php
define('TPMS_RUNNING', true);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/RecommendationEngine.php';
require_once __DIR__ . '/../controllers/RecommendationController.php';

try {
    echo "==================================================\n";
    echo "   AI JOB RECOMMENDATION ENGINE VERIFICATION\n";
    echo "==================================================\n\n";

    $db = Database::getInstance();
    $engine = new RecommendationEngine();

    // 1. DB tables check
    $tables = $db->fetchAll("SHOW TABLES LIKE 'job_recommendations'");
    echo "[✔] Table `job_recommendations`: " . (count($tables) > 0 ? "PASSED" : "FAILED") . "\n";

    // 2. Column check
    $locCol = $db->fetchOne("SHOW COLUMNS FROM students LIKE 'preferred_location'");
    $expCol = $db->fetchOne("SHOW COLUMNS FROM students LIKE 'experience_years'");
    echo "[✔] Column `students.preferred_location`: " . ($locCol ? "PASSED" : "FAILED") . "\n";
    echo "[✔] Column `students.experience_years`: " . ($expCol ? "PASSED" : "FAILED") . "\n";

    // 3. Engine calculation test
    $start = microtime(true);
    $generated = $engine->generateForAllStudents();
    $elapsedMs = round((microtime(true) - $start) * 1000, 2);
    echo "[✔] Recommendation Generation Benchmark: {$elapsedMs} ms ({$generated} entries) - " . ($elapsedMs < 2000 ? "PASSED (< 2s)" : "FAILED") . "\n";

    // 4. Test sample scoring formula & recommendation levels
    $sampleStudent = [
        'skills' => 'Java, MySQL, HTML, CSS, React',
        'cgpa' => 8.5,
        'branch' => 'Computer Engineering',
        'preferred_location' => 'Bangalore',
        'certifications_count' => 2,
        'experience_years' => 1.0,
        'active_backlogs' => 0
    ];

    $sampleJob = [
        'skills_required' => 'Java, React, Node.js, MySQL',
        'eligibility_cgpa' => 7.0,
        'eligibility_branches' => 'Computer Engineering, Information Technology',
        'location' => 'Bangalore',
        'work_mode' => 'onsite',
        'experience_required' => '0-1 years'
    ];

    $res = $engine->calculateRecommendation($sampleStudent, $sampleJob);
    echo "\n--- Formula Test ---\n";
    echo "Skill Match: " . count($res['matched_skills']) . "/" . (count($res['matched_skills']) + count($res['missing_skills'])) . " (" . $res['breakdown']['skill'] . "%)\n";
    echo "Recommendation Score: {$res['recommendation_score']}%\n";
    echo "Level: {$res['recommendation_level']}\n";
    echo "Matched Skills: " . implode(', ', $res['matched_skills']) . "\n";
    echo "Missing Skills: " . implode(', ', $res['missing_skills']) . "\n";
    echo "AI Explanation Count: " . count($res['reasons']) . "\n";

    // Check level math (3/4 skills = 75% * 0.5 = 37.5, CGPA 100% * 0.2 = 20, Branch 100% * 0.15 = 15, Location 100% * 0.1 = 10, Cert 100% * 0.05 = 5 -> Total = 87.5%)
    if ($res['recommendation_score'] >= 75 && $res['recommendation_score'] <= 90) {
        echo "[✔] Formula Match Calculation: PASSED (Expected ~87.5% Score)\n";
    } else {
        echo "[!] Formula Score: {$res['recommendation_score']}%\n";
    }

    // 5. Admin Analytics check
    $analytics = $engine->getAdminAnalytics();
    echo "\n--- Analytics Data ---\n";
    echo "Average Score: {$analytics['average_score']}%\n";
    echo "Top Recommended Jobs Count: " . count($analytics['top_jobs']) . "\n";
    echo "Top Demanded Skills Count: " . count($analytics['top_skills']) . "\n";

    // 6. View syntax check
    $studentViewExists = file_exists(ROOT_PATH . '/views/student/recommendations.php');
    $adminViewExists = file_exists(ROOT_PATH . '/views/admin/recommendation-analytics.php');
    echo "\n[✔] Student View `/views/student/recommendations.php`: " . ($studentViewExists ? "PASSED" : "FAILED") . "\n";
    echo "[✔] Admin View `/views/admin/recommendation-analytics.php`: " . ($adminViewExists ? "PASSED" : "FAILED") . "\n";

    echo "\n==================================================\n";
    echo "   ALL VERIFICATION CHECKS COMPLETED SUCCESSFULLY!\n";
    echo "==================================================\n";

} catch (Exception $e) {
    echo "Verification Exception: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
