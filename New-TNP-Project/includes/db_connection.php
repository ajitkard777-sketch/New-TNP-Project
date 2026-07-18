<?php
/**
 * ============================================================
 *  Training & Placement Management System (TPMS)
 *  File    : includes/db_connection.php
 *  Purpose : Establish a secure MySQL database connection
 *            using MySQLi. Include this file on every page
 *            that needs database access.
 *  Usage   : require_once __DIR__ . '/../includes/db_connection.php';
 * ============================================================
 */

// ── Database Configuration ────────────────────────────────────
define('DB_HOST',     'localhost');   // Database host
define('DB_USER',     'root');        // MySQL username  (change for production)
define('DB_PASSWORD', '');            // MySQL password  (change for production)
define('DB_NAME',     'tnp_db');      // Database name
define('DB_PORT',      3306);         // MySQL port (default: 3306)
define('DB_CHARSET',  'utf8mb4');     // Character set
// ─────────────────────────────────────────────────────────────

// ── Create Connection ─────────────────────────────────────────
$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, DB_PORT);

// ── Connection Error Handling ─────────────────────────────────
if ($conn->connect_error) {
    // In production: log error instead of exposing it
    error_log("Database Connection Failed: " . $conn->connect_error);

    // Show a user-friendly message (never expose raw errors publicly)
    die(json_encode([
        'status'  => 'error',
        'message' => 'Database connection failed. Please contact the administrator.'
    ]));
}

// ── Set Character Set ─────────────────────────────────────────
if (!$conn->set_charset(DB_CHARSET)) {
    error_log("Error setting charset " . DB_CHARSET . ": " . $conn->error);
}

// ── Optional: Timezone Sync ───────────────────────────────────
// Keeps PHP and MySQL timestamps in sync (IST - Indian Standard Time)
$conn->query("SET time_zone = '+05:30'");

// ── Connection Successful ─────────────────────────────────────
// $conn is now available globally wherever this file is included.
?>
