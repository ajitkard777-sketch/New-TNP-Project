<?php
/**
 * ============================================================
 *  TPMS — includes/db_connection.php  (v3.0)
 *  • Auto-creates tnp_db database
 *  • Bootstraps all 15 tables with IF NOT EXISTS
 *  • Adds new auth columns (status, reset_token, reset_expires,
 *    year, roll_number, hr_name, location) to existing tables
 *  • Seeds 3 default accounts in BOTH users and tpms_users
 * ============================================================
 */

// ── Configuration ─────────────────────────────────────────────
if (!defined('DB_HOST'))     define('DB_HOST',     'localhost');
if (!defined('DB_USER'))     define('DB_USER',     'root');
if (!defined('DB_PASSWORD')) define('DB_PASSWORD', '');
if (!defined('DB_PASS'))     define('DB_PASS',     '');       // PDO alias
if (!defined('DB_NAME'))     define('DB_NAME',     'tnp_db');
if (!defined('DB_PORT'))     define('DB_PORT',      3306);
if (!defined('DB_CHARSET'))  define('DB_CHARSET',   'utf8mb4');

// ── Step 1: Connect WITHOUT database name ─────────────────────
$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, '', DB_PORT);
if ($conn->connect_error) {
    error_log('[TPMS] MySQL connect failed: ' . $conn->connect_error);
    die(json_encode(['status' => 'error', 'message' => 'Cannot connect to MySQL. Is XAMPP running?']));
}

// ── Step 2: Create database if missing ────────────────────────
if (!$conn->query("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
    die(json_encode(['status' => 'error', 'message' => 'Failed to create database. Check MySQL permissions.']));
}

// ── Step 3: Select database ───────────────────────────────────
$conn->select_db(DB_NAME);

// ── Step 4: Charset + timezone ────────────────────────────────
$conn->set_charset(DB_CHARSET);
$conn->query("SET time_zone = '+05:30'");

// ── Step 5: Bootstrap tables, columns and seed ────────────────
_tpms_bootstrap_tables($conn);

// =============================================================
//  HELPERS
// =============================================================

/**
 * Safely add a column to an existing table (idempotent).
 */
function _tpms_add_col(mysqli $db, string $tbl, string $col, string $def): void
{
    $r = $db->query("SHOW COLUMNS FROM `{$tbl}` LIKE '" . $db->real_escape_string($col) . "'");
    if ($r && $r->num_rows === 0) {
        if (!$db->query("ALTER TABLE `{$tbl}` ADD COLUMN `{$col}` {$def}")) {
            error_log("[TPMS] Add column failed – {$tbl}.{$col}: " . $db->error);
        }
    }
}

/**
 * Full table + column + seed bootstrap — called once per request.
 */
function _tpms_bootstrap_tables(mysqli $db): void
{
    $db->query("SET FOREIGN_KEY_CHECKS = 0");

    // ── Table definitions ──────────────────────────────────────
    $tables = [

        // ① tpms_users — simple auth (login.php legacy)
        "CREATE TABLE IF NOT EXISTS `tpms_users` (
            `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `name`       VARCHAR(120) NOT NULL,
            `email`      VARCHAR(180) NOT NULL UNIQUE,
            `password`   VARCHAR(255) NOT NULL,
            `role`       ENUM('admin','student','company') NOT NULL,
            `status`     TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        // ② users — primary auth (full registration system)
        "CREATE TABLE IF NOT EXISTS `users` (
            `id`            INT AUTO_INCREMENT PRIMARY KEY,
            `uid`           VARCHAR(25) NOT NULL UNIQUE,
            `name`          VARCHAR(120) NOT NULL,
            `email`         VARCHAR(160) NOT NULL UNIQUE,
            `password_hash` VARCHAR(255) NOT NULL,
            `role`          ENUM('student','company','admin') NOT NULL,
            `avatar`        VARCHAR(500) DEFAULT NULL,
            `status`        TINYINT(1) NOT NULL DEFAULT 1,
            `reset_token`   VARCHAR(64) DEFAULT NULL,
            `reset_expires` DATETIME DEFAULT NULL,
            `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_email` (`email`),
            INDEX `idx_role`  (`role`),
            INDEX `idx_token` (`reset_token`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        // ③ students
        "CREATE TABLE IF NOT EXISTS `students` (
            `id`                 INT AUTO_INCREMENT PRIMARY KEY,
            `user_id`            INT NOT NULL,
            `student_uid`        VARCHAR(25) NOT NULL UNIQUE,
            `branch`             VARCHAR(120) DEFAULT NULL,
            `cgpa`               DECIMAL(4,2) DEFAULT 0.00,
            `backlogs`           INT DEFAULT 0,
            `phone`              VARCHAR(20) DEFAULT NULL,
            `year`               VARCHAR(20) DEFAULT NULL,
            `roll_number`        VARCHAR(50) DEFAULT NULL,
            `placement_status`   VARCHAR(30) DEFAULT 'In Progress',
            `profile_completion` INT DEFAULT 0,
            `resume_name`        VARCHAR(255) DEFAULT NULL,
            `resume_path`        VARCHAR(500) DEFAULT NULL,
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
            INDEX `idx_student_uid` (`student_uid`),
            INDEX `idx_branch` (`branch`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        // ④ student_skills
        "CREATE TABLE IF NOT EXISTS `student_skills` (
            `id`         INT AUTO_INCREMENT PRIMARY KEY,
            `student_id` INT NOT NULL,
            `skill`      VARCHAR(100) NOT NULL,
            FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        // ⑤ companies
        "CREATE TABLE IF NOT EXISTS `companies` (
            `id`              INT AUTO_INCREMENT PRIMARY KEY,
            `comp_uid`        VARCHAR(25) NOT NULL UNIQUE,
            `user_id`         INT DEFAULT NULL,
            `name`            VARCHAR(160) NOT NULL,
            `website`         VARCHAR(255) DEFAULT NULL,
            `industry`        VARCHAR(120) DEFAULT NULL,
            `contact`         VARCHAR(160) DEFAULT NULL,
            `hr_name`         VARCHAR(120) DEFAULT NULL,
            `location`        VARCHAR(200) DEFAULT NULL,
            `registered_date` DATE DEFAULT NULL,
            `job_count`       INT DEFAULT 0,
            `logo_url`        VARCHAR(500) DEFAULT NULL,
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
            INDEX `idx_comp_uid`  (`comp_uid`),
            INDEX `idx_comp_name` (`name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        // ⑥ jobs
        "CREATE TABLE IF NOT EXISTS `jobs` (
            `id`           INT AUTO_INCREMENT PRIMARY KEY,
            `job_uid`      VARCHAR(25) NOT NULL UNIQUE,
            `company_id`   INT NOT NULL,
            `title`        VARCHAR(200) NOT NULL,
            `package`      VARCHAR(60) DEFAULT NULL,
            `location`     VARCHAR(160) DEFAULT NULL,
            `eligibility`  VARCHAR(500) DEFAULT NULL,
            `deadline`     DATE DEFAULT NULL,
            `status`       VARCHAR(20) DEFAULT 'Active',
            `description`  TEXT DEFAULT NULL,
            `company_logo` VARCHAR(500) DEFAULT NULL,
            `created_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE,
            INDEX `idx_job_uid`    (`job_uid`),
            INDEX `idx_job_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        // ⑦ job_skills
        "CREATE TABLE IF NOT EXISTS `job_skills` (
            `id`     INT AUTO_INCREMENT PRIMARY KEY,
            `job_id` INT NOT NULL,
            `skill`  VARCHAR(100) NOT NULL,
            FOREIGN KEY (`job_id`) REFERENCES `jobs`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        // ⑧ applications
        "CREATE TABLE IF NOT EXISTS `applications` (
            `id`           INT AUTO_INCREMENT PRIMARY KEY,
            `app_uid`      VARCHAR(25) NOT NULL UNIQUE,
            `student_id`   INT NOT NULL,
            `job_id`       INT NOT NULL,
            `applied_date` DATE DEFAULT NULL,
            `status`       VARCHAR(30) DEFAULT 'Applied',
            `created_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`job_id`)     REFERENCES `jobs`(`id`)     ON DELETE CASCADE,
            UNIQUE KEY `unique_application` (`student_id`, `job_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        // ⑨ app_timeline
        "CREATE TABLE IF NOT EXISTS `app_timeline` (
            `id`             INT AUTO_INCREMENT PRIMARY KEY,
            `application_id` INT NOT NULL,
            `stage`          VARCHAR(50) NOT NULL,
            `stage_date`     VARCHAR(120) DEFAULT 'Pending',
            `done`           TINYINT(1) DEFAULT 0,
            `sort_order`     INT DEFAULT 0,
            FOREIGN KEY (`application_id`) REFERENCES `applications`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        // ⑩ training
        "CREATE TABLE IF NOT EXISTS `training` (
            `id`          INT AUTO_INCREMENT PRIMARY KEY,
            `trn_uid`     VARCHAR(25) NOT NULL UNIQUE,
            `title`       VARCHAR(220) NOT NULL,
            `trainer`     VARCHAR(200) DEFAULT NULL,
            `trn_date`    VARCHAR(160) DEFAULT NULL,
            `duration`    VARCHAR(60) DEFAULT NULL,
            `status`      VARCHAR(20) DEFAULT 'Upcoming',
            `description` TEXT DEFAULT NULL,
            `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        // ⑪ student_training
        "CREATE TABLE IF NOT EXISTS `student_training` (
            `id`            INT AUTO_INCREMENT PRIMARY KEY,
            `student_id`    INT NOT NULL,
            `training_id`   INT NOT NULL,
            `registered_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`student_id`)  REFERENCES `students`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`training_id`) REFERENCES `training`(`id`) ON DELETE CASCADE,
            UNIQUE KEY `unique_enrollment` (`student_id`, `training_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        // ⑫ universities
        "CREATE TABLE IF NOT EXISTS `universities` (
            `id`          INT AUTO_INCREMENT PRIMARY KEY,
            `uni_uid`     VARCHAR(25) NOT NULL UNIQUE,
            `name`        VARCHAR(220) NOT NULL,
            `country`     VARCHAR(100) DEFAULT NULL,
            `courses`     TEXT DEFAULT NULL,
            `deadline`    DATE DEFAULT NULL,
            `scholarship` VARCHAR(220) DEFAULT NULL,
            `fees`        VARCHAR(100) DEFAULT NULL,
            `ranking`     VARCHAR(60) DEFAULT NULL,
            `min_cgpa`    DECIMAL(4,2) DEFAULT 0.00,
            `website`     VARCHAR(255) DEFAULT NULL,
            `logo`        VARCHAR(500) DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        // ⑬ university_apps
        "CREATE TABLE IF NOT EXISTS `university_apps` (
            `id`            INT AUTO_INCREMENT PRIMARY KEY,
            `student_id`    INT NOT NULL,
            `university_id` INT NOT NULL,
            `applied_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`student_id`)    REFERENCES `students`(`id`)     ON DELETE CASCADE,
            FOREIGN KEY (`university_id`) REFERENCES `universities`(`id`) ON DELETE CASCADE,
            UNIQUE KEY `unique_uni_app` (`student_id`, `university_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        // ⑭ bookmarked_jobs
        "CREATE TABLE IF NOT EXISTS `bookmarked_jobs` (
            `id`            INT AUTO_INCREMENT PRIMARY KEY,
            `student_id`    INT NOT NULL,
            `job_id`        INT NOT NULL,
            `bookmarked_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`job_id`)     REFERENCES `jobs`(`id`)     ON DELETE CASCADE,
            UNIQUE KEY `unique_bookmark` (`student_id`, `job_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        // ⑮ activities
        "CREATE TABLE IF NOT EXISTS `activities` (
            `id`         INT AUTO_INCREMENT PRIMARY KEY,
            `type`       VARCHAR(50) DEFAULT NULL,
            `text`       VARCHAR(500) NOT NULL,
            `icon`       VARCHAR(50) DEFAULT 'bell',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        // ⑯ password_resets (standalone token table - backup)
        "CREATE TABLE IF NOT EXISTS `password_resets` (
            `id`         INT AUTO_INCREMENT PRIMARY KEY,
            `email`      VARCHAR(180) NOT NULL,
            `token`      VARCHAR(64) NOT NULL UNIQUE,
            `expires_at` DATETIME NOT NULL,
            `used`       TINYINT(1) NOT NULL DEFAULT 0,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_token` (`token`),
            INDEX `idx_email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    ];

    foreach ($tables as $sql) {
        if (!$db->query($sql)) {
            error_log('[TPMS] Table creation error: ' . $db->error);
        }
    }

    // ── Add missing columns to pre-existing tables ─────────────
    _tpms_add_col($db, 'users',     'status',        "TINYINT(1) NOT NULL DEFAULT 1");
    _tpms_add_col($db, 'users',     'reset_token',   "VARCHAR(64) DEFAULT NULL");
    _tpms_add_col($db, 'users',     'reset_expires', "DATETIME DEFAULT NULL");
    _tpms_add_col($db, 'students',  'year',          "VARCHAR(20) DEFAULT NULL");
    _tpms_add_col($db, 'students',  'roll_number',   "VARCHAR(50) DEFAULT NULL");
    _tpms_add_col($db, 'companies', 'hr_name',       "VARCHAR(120) DEFAULT NULL");
    _tpms_add_col($db, 'companies', 'location',      "VARCHAR(200) DEFAULT NULL");

    $db->query("SET FOREIGN_KEY_CHECKS = 1");

    // ── Seed default accounts ──────────────────────────────────
    _tpms_seed_default_users($db);
}

/**
 * Seeds the 3 default accounts into BOTH users and tpms_users tables.
 * Idempotent – checks email before inserting.
 */
function _tpms_seed_default_users(mysqli $db): void
{
    $accounts = [
        [
            'uid'    => 'ADM001',
            'name'   => 'TPO Administrator',
            'email'  => 'admin@tpms.com',
            'pass'   => 'Admin@123',
            'role'   => 'admin',
        ],
        [
            'uid'    => 'STU20260001',
            'name'   => 'Demo Student',
            'email'  => 'student@tpms.com',
            'pass'   => 'Student@123',
            'role'   => 'student',
            'branch' => 'Computer Science',
        ],
        [
            'uid'    => 'COMP20260001',
            'name'   => 'Demo Company',
            'email'  => 'company@tpms.com',
            'pass'   => 'Company@123',
            'role'   => 'company',
        ],
    ];

    // Seed into `users` (primary auth table)
    $uChk = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $uIns = $db->prepare(
        "INSERT INTO users (uid, name, email, password_hash, role) VALUES (?, ?, ?, ?, ?)"
    );

    foreach ($accounts as $a) {
        $uChk->bind_param('s', $a['email']);
        $uChk->execute();
        $uChk->store_result();

        if ($uChk->num_rows === 0) {
            $h = password_hash($a['pass'], PASSWORD_BCRYPT, ['cost' => 12]);
            $uIns->bind_param('sssss', $a['uid'], $a['name'], $a['email'], $h, $a['role']);
            $uIns->execute();
            $uid = $db->insert_id;

            if ($a['role'] === 'student' && $uid) {
                $branch = $db->real_escape_string($a['branch'] ?? 'General');
                $suid   = $db->real_escape_string($a['uid']);
                $db->query("INSERT INTO students (user_id, student_uid, branch) VALUES ({$uid}, '{$suid}', '{$branch}')");
            }
            if ($a['role'] === 'company' && $uid) {
                $cuid = $db->real_escape_string($a['uid']);
                $cname = $db->real_escape_string($a['name']);
                $db->query("INSERT INTO companies (comp_uid, user_id, name, registered_date) VALUES ('{$cuid}', {$uid}, '{$cname}', CURDATE())");
            }
        }
        $uChk->free_result();
    }
    $uChk->close();
    $uIns->close();

    // Seed into `tpms_users` (legacy simple-auth fallback)
    $tChk = $db->prepare("SELECT id FROM tpms_users WHERE email = ? LIMIT 1");
    $tIns = $db->prepare(
        "INSERT INTO tpms_users (name, email, password, role) VALUES (?, ?, ?, ?)"
    );
    foreach ($accounts as $a) {
        $tChk->bind_param('s', $a['email']);
        $tChk->execute();
        $tChk->store_result();
        if ($tChk->num_rows === 0) {
            $h = password_hash($a['pass'], PASSWORD_BCRYPT, ['cost' => 12]);
            $tIns->bind_param('ssss', $a['name'], $a['email'], $h, $a['role']);
            $tIns->execute();
        }
        $tChk->free_result();
    }
    $tChk->close();
    $tIns->close();
}
