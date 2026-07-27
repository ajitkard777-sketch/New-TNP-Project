<?php
define('TPMS_RUNNING', true);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/RecommendationEngine.php';

try {
    $engine = new RecommendationEngine();

    $start = microtime(true);
    $count = $engine->generateForAllStudents();
    $executionTime = round((microtime(true) - $start) * 1000, 2);

    echo "=== Recommendation Engine Verification ===\n";
    echo "Total Recommendations Generated: $count\n";
    echo "Execution Time: {$executionTime} ms (Limit < 2000 ms)\n";

    // Test retrieval for student ID 1
    $db = Database::getInstance();
    $firstStudent = $db->fetchOne("SELECT id, first_name, last_name FROM students LIMIT 1");
    if ($firstStudent) {
        $studentId = (int)$firstStudent['id'];
        $recs = $engine->getStudentRecommendations($studentId);
        echo "Found " . count($recs) . " recommendations for {$firstStudent['first_name']} {$firstStudent['last_name']} (ID: $studentId)\n";
        
        if (!empty($recs)) {
            $top = $recs[0];
            echo "\nTop Recommendation:\n";
            echo "- Job: {$top['title']} at {$top['company_name']}\n";
            echo "- Score: {$top['recommendation_score']}% ({$top['recommendation_level']})\n";
            echo "- Matched Skills: " . implode(', ', $top['matched_skills_array']) . "\n";
            echo "- Missing Skills: " . implode(', ', $top['missing_skills_array']) . "\n";
        }
    }

    $analytics = $engine->getAdminAnalytics();
    echo "\n=== Admin Analytics Summary ===\n";
    echo "Average Score across system: {$analytics['average_score']}%\n";
    echo "Top Demanded Skills: " . implode(', ', array_keys($analytics['top_skills'])) . "\n";

} catch (Exception $e) {
    echo "Engine Error: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
