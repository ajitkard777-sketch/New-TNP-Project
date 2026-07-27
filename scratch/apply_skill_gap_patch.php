<?php
define('TPMS_RUNNING', true);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    $sql = file_get_contents(__DIR__ . '/../database/patch_skill_gap.sql');
    $pdo->exec($sql);

    echo "Skill gap patch applied successfully!\n";
    $stmt1 = $pdo->query("SHOW TABLES LIKE 'skill_gap_analysis'");
    $stmt2 = $pdo->query("SHOW TABLES LIKE 'recommended_courses'");
    $stmt3 = $pdo->query("SHOW TABLES LIKE 'student_learning_progress'");

    echo "skill_gap_analysis: " . ($stmt1->fetch() ? "EXISTS" : "MISSING") . "\n";
    echo "recommended_courses: " . ($stmt2->fetch() ? "EXISTS" : "MISSING") . "\n";
    echo "student_learning_progress: " . ($stmt3->fetch() ? "EXISTS" : "MISSING") . "\n";

    $courseCount = (int)$db->fetchColumn("SELECT COUNT(*) FROM recommended_courses");
    echo "Total Seed Courses in Catalog: $courseCount\n";
} catch (Exception $e) {
    echo "Patch Error: " . $e->getMessage() . "\n";
}
