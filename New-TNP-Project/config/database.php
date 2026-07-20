<?php
/**
 * ============================================================
 *  TPMS — config/database.php
 *  PDO Database Connection Singleton (PHP 8+).
 *
 *  FIX: Auto-creates 'tnp_db' database if it does not exist,
 *  then reconnects with the database selected.
 *  This eliminates "Unknown database 'tnp_db'" (HY000/1049).
 * ============================================================
 */

// ── Constants (guard against duplicate define from db_connection.php) ─
if (!defined('DB_HOST'))    define('DB_HOST',    'localhost');
if (!defined('DB_USER'))    define('DB_USER',    'root');
if (!defined('DB_PASS'))    define('DB_PASS',    '');
if (!defined('DB_NAME'))    define('DB_NAME',    'tnp_db');
if (!defined('DB_CHARSET')) define('DB_CHARSET',  'utf8mb4');
if (!defined('DB_PORT'))    define('DB_PORT',     3306);
// back-compat alias used by includes/db_connection.php
if (!defined('DB_PASSWORD')) define('DB_PASSWORD', DB_PASS);

// ── Internal: ensure the database exists ─────────────────────
function _tpms_ensure_db_exists(): void
{
    static $done = false;
    if ($done) return;
    $done = true;

    try {
        // Connect without a dbname to avoid 1049
        $dsn = sprintf('mysql:host=%s;port=%d;charset=%s', DB_HOST, DB_PORT, DB_CHARSET);
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        $pdo->exec(
            "CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "`
             CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
        );
    } catch (PDOException $e) {
        error_log('[TPMS] Cannot connect to MySQL: ' . $e->getMessage());
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Cannot connect to MySQL server. Is XAMPP/MySQL running?',
        ]);
        exit;
    }
}

/**
 * Returns a shared PDO instance pointing at DB_NAME.
 * Automatically creates the database on first call.
 */
function getPDO(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        _tpms_ensure_db_exists();   // create DB if missing

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            $pdo->exec("SET time_zone = '+05:30'");
        } catch (PDOException $e) {
            error_log('[TPMS] DB Error: ' . $e->getMessage());
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Database connection failed. Please check your MySQL configuration.',
            ]);
            exit;
        }
    }

    return $pdo;
}
