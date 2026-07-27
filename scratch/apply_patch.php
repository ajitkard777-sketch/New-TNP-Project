<?php
define('TPMS_RUNNING', true);

try {
    $pdo = new PDO("mysql:host=localhost;charset=utf8mb4", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `team1` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    echo "Database `team1` initialized.\n";

    // Connect to team1 DB
    $pdo->exec("USE `team1`;");

    // Check if users table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if (!$stmt->fetch()) {
        echo "Importing base database tpms.sql...\n";
        $tpmsSql = file_get_contents(__DIR__ . '/../database/tpms.sql');
        $pdo->exec($tpmsSql);
        echo "tpms.sql imported successfully.\n";
    } else {
        echo "Base tables already exist.\n";
    }

    // Apply patch_fix_mismatches.sql safely line by line or statement by statement
    if (file_exists(__DIR__ . '/../database/patch_fix_mismatches.sql')) {
        echo "Applying patch_fix_mismatches.sql...\n";
        $fixSql = file_get_contents(__DIR__ . '/../database/patch_fix_mismatches.sql');
        $statements = array_filter(array_map('trim', explode(';', $fixSql)));
        foreach ($statements as $stmtSql) {
            if (empty($stmtSql) || str_starts_with($stmtSql, '--')) continue;
            try {
                $pdo->exec($stmtSql);
            } catch (Exception $e) {
                // Ignore statement errors if already present or column missing
            }
        }
    }

    // Apply patch_ai_recommendations.sql
    echo "Applying patch_ai_recommendations.sql...\n";
    $patchSql = file_get_contents(__DIR__ . '/../database/patch_ai_recommendations.sql');
    $pdo->exec($patchSql);

    echo "--- Database Verification ---\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM students LIKE 'preferred_location'");
    echo "preferred_location: " . ($stmt->fetch() ? "EXISTS" : "MISSING") . "\n";

    $stmt = $pdo->query("SHOW COLUMNS FROM students LIKE 'experience_years'");
    echo "experience_years: " . ($stmt->fetch() ? "EXISTS" : "MISSING") . "\n";

    $stmt = $pdo->query("SHOW TABLES LIKE 'job_recommendations'");
    echo "job_recommendations table: " . ($stmt->fetch() ? "EXISTS" : "MISSING") . "\n";

    $studentCount = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
    $jobCount = $pdo->query("SELECT COUNT(*) FROM jobs")->fetchColumn();
    echo "Students in DB: $studentCount\n";
    echo "Jobs in DB: $jobCount\n";

} catch (Exception $e) {
    echo "Patch Execution Error: " . $e->getMessage() . "\n";
}
