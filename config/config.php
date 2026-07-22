<?php
/**
 * TPMS - Application Configuration
 */

// Prevent direct access
if (!defined('TPMS_RUNNING')) {
    define('TPMS_RUNNING', true);
}

// Environment
define('APP_ENV', 'development'); // development | production

// Application
define('APP_NAME', 'TPMS');
define('APP_FULL_NAME', 'Training & Placement Management System');
define('APP_VERSION', '1.0.0');

// Base URL - adjust if needed
define('BASE_URL', '/team1');
define('FULL_URL', 'http://localhost' . BASE_URL);

// Paths
define('ROOT_PATH', dirname(__DIR__));
define('VIEWS_PATH', ROOT_PATH . '/views');
define('CONTROLLERS_PATH', ROOT_PATH . '/controllers');
define('MODELS_PATH', ROOT_PATH . '/models');
define('UPLOADS_PATH', ROOT_PATH . '/uploads');
define('LOGS_PATH', ROOT_PATH . '/logs');

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

// Email settings (configure for your SMTP)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'kishorpanchal402@gmail.com');
define('SMTP_PASSWORD', 'tohb qpud bxgs fxlo');
define('SMTP_FROM_EMAIL', 'kishorpanchal402@gmail.com');
define('SMTP_FROM_NAME', 'TPMS System');

// Pagination
define('RECORDS_PER_PAGE', 10);

// Password policy
define('PASSWORD_MIN_LENGTH', 8);

// OTP settings
define('OTP_LENGTH', 6);
define('OTP_EXPIRY', 600); // 10 minutes

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
