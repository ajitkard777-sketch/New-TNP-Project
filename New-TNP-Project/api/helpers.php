<?php
/**
 * ============================================================
 *  TPMS — api/helpers.php
 *  Shared utility functions for all API endpoints.
 *  NOT directly web-accessible (no define guard needed here
 *  since it outputs nothing when accessed directly).
 * ============================================================
 */

// ── Session Helpers ───────────────────────────────────────────

/**
 * Start session safely (only if not already started).
 */
if (!function_exists('tpms_session_start')) {
    function tpms_session_start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => 0,
                'path'     => '/',
                'secure'   => false,    // Set true in production with HTTPS
                'httponly' => true,
                'samesite' => 'Strict',
            ]);
            session_start();
        }
    }
}

/**
 * Require authenticated session. Exits with 401 if not logged in.
 */
function requireAuth(): array
{
    tpms_session_start();
    if (empty($_SESSION['user'])) {
        respond(['success' => false, 'message' => 'Authentication required. Please log in.'], 401);
    }
    return $_SESSION['user'];
}

/**
 * Require specific role(s). Exits with 403 if role doesn't match.
 * @param string|array $roles
 */
function requireRole(string|array $roles): array
{
    $user  = requireAuth();
    $roles = (array) $roles;
    if (!in_array($user['role'], $roles, true)) {
        respond(['success' => false, 'message' => 'Access denied. Insufficient permissions.'], 403);
    }
    return $user;
}

// ── CSRF Helpers ──────────────────────────────────────────────

/**
 * Generate (or return existing) CSRF token for the session.
 */
function getCSRFToken(): string
{
    tpms_session_start();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate the CSRF token from POST body or request header.
 * Exits with 403 on mismatch.
 */
function validateCSRF(): void
{
    tpms_session_start();
    $token = $_POST['csrf_token']
        ?? getallheaders()['X-CSRF-Token']
        ?? getallheaders()['X-Csrf-Token']
        ?? '';

    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        respond(['success' => false, 'message' => 'Invalid security token. Please refresh and try again.'], 403);
    }
}

// ── Response Helpers ──────────────────────────────────────────

/**
 * Send JSON response and exit.
 */
function respond(array $data, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Set required HTTP headers for API endpoints.
 */
function setApiHeaders(): void
{
    header('Content-Type: application/json; charset=UTF-8');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Cache-Control: no-store, no-cache');
}

// ── Sanitization ──────────────────────────────────────────────

/**
 * Sanitize a string for safe HTML output (XSS prevention).
 */
function xss(mixed $value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Get and sanitize a POST field.
 */
function post(string $key, mixed $default = ''): string
{
    return trim($_POST[$key] ?? $default);
}

/**
 * Get and sanitize a GET field.
 */
function get_param(string $key, mixed $default = ''): string
{
    return trim($_GET[$key] ?? $default);
}

// ── Validation ────────────────────────────────────────────────

/**
 * Validate email format.
 */
function isValidEmail(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate password strength (min 8 chars, 1 uppercase, 1 digit).
 */
function isStrongPassword(string $pass): bool
{
    return strlen($pass) >= 8
        && preg_match('/[A-Z]/', $pass)
        && preg_match('/[0-9]/', $pass);
}

// ── ID Generation ─────────────────────────────────────────────

/**
 * Generate a collision-resistant UID with a given prefix.
 * e.g. generateUID('JOB') → 'JOB174920384'
 */
function generateUID(string $prefix): string
{
    return strtoupper($prefix) . substr((string)microtime(true) * 10000, -8);
}

// ── Time Helpers ──────────────────────────────────────────────

/**
 * Return a human-readable "time ago" string.
 */
function timeAgo(string $datetime): string
{
    $now  = new DateTime();
    $then = new DateTime($datetime);
    $diff = $now->diff($then);

    if ($diff->y > 0) return $diff->y . ' year'  . ($diff->y > 1 ? 's' : '') . ' ago';
    if ($diff->m > 0) return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    if ($diff->d > 0) return $diff->d . ' day'   . ($diff->d > 1 ? 's' : '') . ' ago';
    if ($diff->h > 0) return $diff->h . ' hour'  . ($diff->h > 1 ? 's' : '') . ' ago';
    if ($diff->i > 0) return $diff->i . ' min'   . ($diff->i > 1 ? 's' : '') . ' ago';
    return 'Just now';
}
