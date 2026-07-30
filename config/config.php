<?php
/**
 * TPMS - Application Configuration
 */

// Prevent direct access
if (!defined('TPMS_RUNNING')) {
    define('TPMS_RUNNING', true);
}

// Paths
define('ROOT_PATH', dirname(__DIR__));
define('VIEWS_PATH', ROOT_PATH . '/views');
define('CONTROLLERS_PATH', ROOT_PATH . '/controllers');
define('MODELS_PATH', ROOT_PATH . '/models');
define('UPLOADS_PATH', ROOT_PATH . '/uploads');
define('LOGS_PATH', ROOT_PATH . '/logs');

// Load .env file if present
if (file_exists(ROOT_PATH . '/.env')) {
    $envLines = file(ROOT_PATH . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($envLines as $line) {
        $line = trim($line);
        if (empty($line) || str_starts_with($line, '#')) continue;
        if (str_contains($line, '=')) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, " \t\n\r\0\x0B\"'");
            if (!array_key_exists($key, $_ENV) && !array_key_exists($key, $_SERVER)) {
                putenv("{$key}={$value}");
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
    }
}

// Environment helper
function env(string $key, mixed $default = null): mixed {
    $value = getenv($key);
    if ($value === false) {
        return $_ENV[$key] ?? $_SERVER[$key] ?? $default;
    }
    return $value;
}

// Environment
define('APP_ENV', env('APP_ENV', 'development')); // development | production

// Application
define('APP_NAME', env('APP_NAME', 'TPMS'));
define('APP_FULL_NAME', env('APP_FULL_NAME', 'Training & Placement Management System'));
define('APP_VERSION', '1.0.0');

// Base URL - adjust if needed
define('BASE_URL', env('BASE_URL', '/TNP'));
define('FULL_URL', 'http://localhost' . BASE_URL);

// Upload settings
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('ALLOWED_IMAGE_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('ALLOWED_DOC_TYPES', ['application/pdf']);
define('ALLOWED_DOC_EXTENSIONS', ['pdf']);

// Session settings
define('SESSION_LIFETIME', 7200); // 2 hours
define('SESSION_NAME', 'TPMS_SESSION');
define('REMEMBER_ME_DURATION', 30 * 24 * 3600); // 30 days

// JWT settings
define('JWT_SECRET', 'tpms_jwt_secret_key_2024_change_this_in_production');
define('JWT_EXPIRY', 86400); // 24 hours
define('JWT_ALGORITHM', 'HS256');

// Email settings — Brevo SMTP (configure in .env)
define('SMTP_HOST',       env('SMTP_HOST',       'smtp-relay.brevo.com'));
define('SMTP_PORT',       (int)env('SMTP_PORT',   587));
define('SMTP_USERNAME',   env('SMTP_USERNAME',   ''));
define('SMTP_PASSWORD',   env('SMTP_PASSWORD',   ''));
define('SMTP_FROM_EMAIL', env('SMTP_FROM_EMAIL', ''));
define('SMTP_FROM_NAME',  env('SMTP_FROM_NAME',  'TPMS System'));
// Retry on temporary SMTP failures (e.g. network hiccup)
define('SMTP_RETRY_ATTEMPTS', (int)env('SMTP_RETRY_ATTEMPTS', 2));
define('SMTP_RETRY_DELAY_MS', (int)env('SMTP_RETRY_DELAY_MS', 1200));

// Pagination
define('RECORDS_PER_PAGE', 10);

// Password policy
define('PASSWORD_MIN_LENGTH', 8);

// OTP settings
define('OTP_LENGTH', 6);
define('OTP_EXPIRY', 600); // 10 minutes

// AI settings (Groq & Gemini)
define('AI_PROVIDER', env('AI_PROVIDER', 'groq'));
define('GROQ_API_KEY', env('GROQ_API_KEY', ''));
define('GROQ_MODEL', env('GROQ_MODEL', 'llama-3.3-70b-versatile'));
define('GROQ_API_URL', 'https://api.groq.com/openai/v1/chat/completions');

define('GEMINI_API_KEY', env('GEMINI_API_KEY', ''));
define('GEMINI_MODEL', env('GEMINI_MODEL', 'gemini-2.0-flash'));
define('GEMINI_API_URL', 'https://generativelanguage.googleapis.com/v1beta/models/' . env('GEMINI_MODEL', 'gemini-2.0-flash') . ':generateContent');

// Branches available
define('BRANCHES', [
    'Computer Science',
    'Information Technology',
    'Electronics',
    'Electrical',
    'Mechanical',
    'Civil',
    'Chemical',
    'Biotechnology',
    'Aerospace',
    'Automobile'
]);

// Job types
define('JOB_TYPES', [
    'full-time' => 'Full Time',
    'internship' => 'Internship',
    'part-time' => 'Part Time',
    'contract' => 'Contract'
]);

// Application statuses
define('APPLICATION_STATUSES', [
    'applied' => 'Applied',
    'shortlisted' => 'Shortlisted',
    'interview' => 'Interview',
    'selected' => 'Selected',
    'rejected' => 'Rejected',
    'withdrawn' => 'Withdrawn'
]);

// Error reporting based on environment
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Timezone
date_default_timezone_set('Asia/Kolkata');
