<?php
/**
 * TPMS - Helper Functions
 */

/**
 * Redirect to a URL
 */
function redirect(string $path): void {
    header('Location: ' . BASE_URL . $path);
    exit;
}

/**
 * Redirect back to previous page
 */
function redirectBack(): void {
    $referer = $_SERVER['HTTP_REFERER'] ?? BASE_URL . '/';
    header('Location: ' . $referer);
    exit;
}

/**
 * Set flash message
 */
function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear flash message
 */
function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Sanitize input
 */
function sanitize(string $input): string {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitize array of inputs
 */
function sanitizeArray(array $data): array {
    $sanitized = [];
    foreach ($data as $key => $value) {
        if (is_array($value)) {
            $sanitized[$key] = sanitizeArray($value);
        } else {
            $sanitized[$key] = sanitize((string)$value);
        }
    }
    return $sanitized;
}

/**
 * Check if request is AJAX
 */
function isAjax(): bool {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Send JSON response
 */
function jsonResponse(array $data, int $statusCode = 200): void {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Generate URL
 */
function url(string $path = ''): string {
    return BASE_URL . '/' . ltrim($path, '/');
}

/**
 * Asset URL helper
 */
function asset(string $path): string {
    $basePath = BASE_URL . '/assets/' . ltrim($path, '/');
    $realPath = ROOT_PATH . '/assets/' . ltrim($path, '/');
    if (file_exists($realPath)) {
        return $basePath . '?v=' . filemtime($realPath);
    }
    return $basePath;
}

/**
 * Upload URL helper
 */
function uploadUrl(string $path): string {
    if (empty($path)) return asset('images/default-avatar.png');
    return BASE_URL . '/uploads/' . ltrim($path, '/');
}

/**
 * Resolve target URL for a notification dynamically
 */
function getNotificationUrl(array $n, ?string $role = null): string {
    if (!empty($n['link'])) {
        // Ensure standard relative or full URL
        if (strpos($n['link'], 'http://') === 0 || strpos($n['link'], 'https://') === 0) {
            return $n['link'];
        }
        if (strpos($n['link'], BASE_URL) === 0) {
            return $n['link'];
        }
        return url(ltrim($n['link'], '/'));
    }
    $role = $role ?? ($_SESSION['user_role'] ?? 'student');
    $category = $n['category'] ?? 'system';
    $refId = (int)($n['reference_id'] ?? 0);

    switch ($category) {
        case 'job':
            if ($role === 'company') {
                return $refId ? url("/company/applications/{$refId}") : url("/company/jobs");
            }
            if ($role === 'admin') {
                return url("/admin/jobs");
            }
            return url("/student/jobs");

        case 'interview':
            if ($role === 'admin') return url("/admin/interviews");
            if ($role === 'company') return url("/company/interviews");
            return url("/student/interviews");

        case 'placement':
            return ($role === 'admin') ? url("/admin/placements") : url("/student/profile");

        case 'training':
            return ($role === 'admin') ? url("/admin/trainings") : url("/student/trainings");

        case 'higher-studies':
            return ($role === 'admin') ? url("/admin/higher-studies") : url("/student/higher-studies");

        case 'approval':
            return ($role === 'admin') ? url("/admin/approvals") : url("/{$role}/dashboard");

        case 'announcement':
        case 'system':
        default:
            return url("/{$role}/notifications");
    }
}

/**
 * Format date
 */
function formatDate(string $date, string $format = 'd M Y'): string {
    return date($format, strtotime($date));
}

/**
 * Format datetime
 */
function formatDateTime(string $datetime, string $format = 'd M Y, h:i A'): string {
    return date($format, strtotime($datetime));
}

/**
 * Format package / salary amount in LPA (Lakhs Per Annum)
 */
function formatPackage(?float $amount): string {
    if (empty($amount) || $amount <= 0) {
        return 'N/A';
    }
    // Auto-convert raw Rupees to LPA if stored in legacy format (>= 1000)
    if ($amount >= 1000) {
        $amount = $amount / 100000;
    }
    return number_format($amount, $amount == floor($amount) ? 0 : 2) . ' LPA';
}

/**
 * Format currency — outputs LPA format cleanly without Rupee symbol
 */
function formatCurrency(float $amount, string $currency = 'INR'): string {
    if ($amount >= 10000000) {
        return number_format($amount / 10000000, 2) . ' Cr';
    } elseif ($amount >= 1000) {
        return number_format($amount / 100000, 2) . ' LPA';
    }
    return number_format($amount, $amount == floor($amount) ? 0 : 2) . ' LPA';
}

/**
 * Format salary range in LPA (values stored as LPA, e.g. 3.5 = 3.5 LPA)
 */
function formatSalaryRange(?float $min, ?float $max, string $currency = 'INR'): string {
    $formatLPA = function(float $val): string {
        if ($val >= 1000) {
            $val = $val / 100000;
        }
        return number_format($val, $val == floor($val) ? 0 : 2) . ' LPA';
    };

    if ($min && $max && $min != $max) {
        return $formatLPA($min) . ' – ' . $formatLPA($max);
    } elseif ($min && $max && $min == $max) {
        return $formatLPA($min);
    } elseif ($min) {
        return $formatLPA($min) . '+';
    } elseif ($max) {
        return 'Up to ' . $formatLPA($max);
    }
    return 'Not Disclosed';
}


/**
 * Time ago format
 */
function timeAgo(string $datetime): string {
    $time = strtotime($datetime);
    $diff = time() - $time;
    
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . ' min ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    if ($diff < 2592000) return floor($diff / 604800) . ' weeks ago';
    
    return formatDate($datetime);
}

/**
 * Generate random string
 */
function generateRandomString(int $length = 32): string {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Generate OTP
 */
function generateOTP(int $length = 6): string {
    return str_pad((string)random_int(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
}

/**
 * Validate email
 */
function isValidEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate strong password
 */
function isStrongPassword(string $password): bool {
    // At least 8 chars, 1 uppercase, 1 lowercase, 1 number, 1 special char
    return strlen($password) >= PASSWORD_MIN_LENGTH &&
           preg_match('/[A-Z]/', $password) &&
           preg_match('/[a-z]/', $password) &&
           preg_match('/[0-9]/', $password) &&
           preg_match('/[^A-Za-z0-9]/', $password);
}

/**
 * Get file extension
 */
function getFileExtension(string $filename): string {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

/**
 * Generate unique filename
 */
function generateFileName(string $originalName, string $prefix = ''): string {
    $ext = getFileExtension($originalName);
    $name = $prefix ? $prefix . '_' : '';
    $name .= time() . '_' . bin2hex(random_bytes(8));
    return $name . '.' . $ext;
}

/**
 * Get current user ID
 */
function getCurrentUserId(): ?int {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user role
 */
function getCurrentUserRole(): ?string {
    return $_SESSION['user_role'] ?? null;
}

/**
 * Check if current user has role
 */
function hasRole(string $role): bool {
    return getCurrentUserRole() === $role;
}

/**
 * Log activity
 */
function logActivity(string $action, string $module, string $description = ''): void {
    try {
        $db = Database::getInstance();
        $db->insert(
            "INSERT INTO activity_logs (user_id, action, module, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?)",
            [
                getCurrentUserId(),
                $action,
                $module,
                $description,
                $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
                $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
            ]
        );
    } catch (Exception $e) {
        error_log("Activity Log Error: " . $e->getMessage());
    }
}

/**
 * Calculate profile completion percentage
 */
function calculateProfileCompletion(array $student): int {
    $fields = [
        'first_name', 'last_name', 'phone', 'dob', 'gender',
        'address', 'city', 'state', 'branch', 'enrollment_no',
        'tenth_percentage', 'twelfth_percentage', 'degree', 'cgpa',
        'skills', 'bio', 'profile_photo', 'resume_path'
    ];
    
    $filled = 0;
    foreach ($fields as $field) {
        if (!empty($student[$field])) {
            $filled++;
        }
    }
    
    return (int)round(($filled / count($fields)) * 100);
}

/**
 * Get status badge class
 */
function getStatusBadgeClass(string $status): string {
    $classes = [
        'active' => 'bg-success',
        'inactive' => 'bg-secondary',
        'pending' => 'bg-warning text-dark',
        'blocked' => 'bg-danger',
        'applied' => 'bg-info',
        'shortlisted' => 'bg-primary',
        'interview' => 'bg-warning text-dark',
        'selected' => 'bg-success',
        'rejected' => 'bg-danger',
        'withdrawn' => 'bg-secondary',
        'scheduled' => 'bg-info',
        'completed' => 'bg-success',
        'cancelled' => 'bg-danger',
        'upcoming' => 'bg-info',
        'ongoing' => 'bg-primary',
        'offered' => 'bg-info',
        'accepted' => 'bg-success',
        'declined' => 'bg-danger',
        'joined' => 'bg-success',
        'draft' => 'bg-secondary',
        'expired' => 'bg-dark',
        'closed' => 'bg-secondary',
        'registered' => 'bg-info',
        'attended' => 'bg-primary',
        'dropped' => 'bg-danger',
        'present' => 'bg-success',
        'absent' => 'bg-danger',
        'late' => 'bg-warning text-dark',
        'passed' => 'bg-success',
        'failed' => 'bg-danger',
        'rescheduled' => 'bg-warning text-dark',
    ];
    
    return $classes[$status] ?? 'bg-secondary';
}

/**
 * Truncate text
 */
function truncateText(string $text, int $length = 100): string {
    if (strlen($text) <= $length) return $text;
    return substr($text, 0, $length) . '...';
}

/**
 * Get greeting based on time
 */
function getGreeting(): string {
    $hour = (int)date('G');
    if ($hour < 12) return 'Good Morning';
    if ($hour < 17) return 'Good Afternoon';
    return 'Good Evening';
}

/**
 * Create JWT token
 */
function createJWT(array $payload): string {
    $header = json_encode(['typ' => 'JWT', 'alg' => JWT_ALGORITHM]);
    $payload['iat'] = time();
    $payload['exp'] = time() + JWT_EXPIRY;
    $payload = json_encode($payload);
    
    $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
    $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
    
    $signature = hash_hmac('sha256', $base64Header . '.' . $base64Payload, JWT_SECRET, true);
    $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
    
    return $base64Header . '.' . $base64Payload . '.' . $base64Signature;
}

/**
 * Verify JWT token
 */
function verifyJWT(string $token): ?array {
    $parts = explode('.', $token);
    if (count($parts) !== 3) return null;
    
    [$base64Header, $base64Payload, $base64Signature] = $parts;
    
    $signature = hash_hmac('sha256', $base64Header . '.' . $base64Payload, JWT_SECRET, true);
    $expectedSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
    
    if (!hash_equals($expectedSignature, $base64Signature)) return null;
    
    $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $base64Payload)), true);
    
    if (!$payload || !isset($payload['exp']) || $payload['exp'] < time()) return null;
    
    return $payload;
}

/**
 * Pagination helper
 */
function getPagination(int $totalRecords, int $currentPage, int $perPage = RECORDS_PER_PAGE): array {
    $totalPages = max(1, ceil($totalRecords / $perPage));
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $perPage;
    
    return [
        'total_records' => $totalRecords,
        'total_pages' => $totalPages,
        'current_page' => $currentPage,
        'per_page' => $perPage,
        'offset' => $offset,
        'has_prev' => $currentPage > 1,
        'has_next' => $currentPage < $totalPages,
    ];
}

/**
 * Render pagination HTML
 * @param array  $pagination  Result from getPagination()
 * @param string $baseUrl     Base URL (without query string)
 * @param array  $extraParams Additional GET params to preserve (e.g. ['status'=>'placed','search'=>'foo'])
 */
function renderPagination(array $pagination, string $baseUrl, array $extraParams = []): string {
    if ($pagination['total_pages'] <= 1) return '';

    // Build a query string that includes any extra params
    $buildUrl = function(int $page) use ($baseUrl, $extraParams): string {
        $params = array_merge($extraParams, ['page' => $page]);
        return $baseUrl . '?' . http_build_query($params);
    };

    $html = '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';

    // Previous
    $prevDisabled = $pagination['has_prev'] ? '' : ' disabled';
    $html .= "<li class='page-item{$prevDisabled}'><a class='page-link' href='" . $buildUrl($pagination['current_page'] - 1) . "'><i class='fas fa-chevron-left'></i></a></li>";

    // Page numbers
    $start = max(1, $pagination['current_page'] - 2);
    $end = min($pagination['total_pages'], $pagination['current_page'] + 2);

    if ($start > 1) {
        $html .= "<li class='page-item'><a class='page-link' href='" . $buildUrl(1) . "'>1</a></li>";
        if ($start > 2) $html .= "<li class='page-item disabled'><span class='page-link'>...</span></li>";
    }

    for ($i = $start; $i <= $end; $i++) {
        $active = $i === $pagination['current_page'] ? ' active' : '';
        $html .= "<li class='page-item{$active}'><a class='page-link' href='" . $buildUrl($i) . "'>{$i}</a></li>";
    }

    if ($end < $pagination['total_pages']) {
        if ($end < $pagination['total_pages'] - 1) $html .= "<li class='page-item disabled'><span class='page-link'>...</span></li>";
        $html .= "<li class='page-item'><a class='page-link' href='" . $buildUrl($pagination['total_pages']) . "'>{$pagination['total_pages']}</a></li>";
    }

    // Next
    $nextDisabled = $pagination['has_next'] ? '' : ' disabled';
    $html .= "<li class='page-item{$nextDisabled}'><a class='page-link' href='" . $buildUrl($pagination['current_page'] + 1) . "'><i class='fas fa-chevron-right'></i></a></li>";

    $html .= '</ul></nav>';
    return $html;
}

/**
 * Format file size
 */
function formatFileSize(int $bytes): string {
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = 0;
    while ($bytes >= 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, 2) . ' ' . $units[$i];
}

/**
 * Get DB name constant
 */
if (!defined('DB_NAME')) {
    define('DB_NAME', 'team1');
}

/**
 * TPMS - Centralized Validator Class
 * All validation rules live here — used by controllers (server-side)
 * and mirrored in assets/js/validation.js (client-side).
 */
class Validator {

    /**
     * Validate a 10-digit Indian phone number.
     * Accepts digits only, must be exactly 10 digits.
     */
    public static function phone(string $value): array {
        $value = trim($value);
        if ($value === '') {
            return ['valid' => false, 'message' => 'Phone number is required.'];
        }
        if (!ctype_digit($value)) {
            return ['valid' => false, 'message' => 'Phone number must contain digits only (no spaces, dashes, or symbols).'];
        }
        if (strlen($value) !== 10) {
            return ['valid' => false, 'message' => 'Phone number must be exactly 10 digits.'];
        }
        return ['valid' => true, 'message' => ''];
    }

    /**
     * Validate a 6-digit Indian PIN code.
     */
    public static function pincode(string $value): array {
        $value = trim($value);
        if ($value === '') {
            return ['valid' => true, 'message' => '']; // optional field
        }
        if (!ctype_digit($value)) {
            return ['valid' => false, 'message' => 'PIN code must contain digits only.'];
        }
        if (strlen($value) !== 6) {
            return ['valid' => false, 'message' => 'PIN code must be exactly 6 digits.'];
        }
        return ['valid' => true, 'message' => ''];
    }

    /**
     * Validate a city name.
     * Allows: letters (including Unicode), spaces, hyphens, apostrophes.
     * Length: 2–50 characters.
     */
    public static function city(string $value, bool $required = false): array {
        $value = trim($value);
        if ($value === '') {
            if ($required) return ['valid' => false, 'message' => 'City is required.'];
            return ['valid' => true, 'message' => ''];
        }
        if (mb_strlen($value, 'UTF-8') < 2) {
            return ['valid' => false, 'message' => 'City name must be at least 2 characters.'];
        }
        if (mb_strlen($value, 'UTF-8') > 50) {
            return ['valid' => false, 'message' => 'City name must not exceed 50 characters.'];
        }
        if (!preg_match("/^[\p{L}\s\-']+$/u", $value)) {
            return ['valid' => false, 'message' => 'City can only contain letters, spaces, hyphens, and apostrophes (no numbers or special characters).'];
        }
        return ['valid' => true, 'message' => ''];
    }

    /**
     * Validate a state name.
     * Allows: letters (including Unicode), spaces, hyphens, apostrophes.
     * Length: 2–50 characters.
     */
    public static function state(string $value, bool $required = false): array {
        $value = trim($value);
        if ($value === '') {
            if ($required) return ['valid' => false, 'message' => 'State is required.'];
            return ['valid' => true, 'message' => ''];
        }
        if (mb_strlen($value, 'UTF-8') < 2) {
            return ['valid' => false, 'message' => 'State name must be at least 2 characters.'];
        }
        if (mb_strlen($value, 'UTF-8') > 50) {
            return ['valid' => false, 'message' => 'State name must not exceed 50 characters.'];
        }
        if (!preg_match("/^[\p{L}\s\-']+$/u", $value)) {
            return ['valid' => false, 'message' => 'State can only contain letters, spaces, hyphens, and apostrophes (no numbers or special characters).'];
        }
        return ['valid' => true, 'message' => ''];
    }

    /**
     * Validate a country name.
     * Allows: letters (including Unicode), spaces, hyphens, apostrophes.
     * Length: 2–50 characters.
     */
    public static function country(string $value, bool $required = false): array {
        $value = trim($value);
        if ($value === '') {
            if ($required) return ['valid' => false, 'message' => 'Country is required.'];
            return ['valid' => true, 'message' => ''];
        }
        if (mb_strlen($value, 'UTF-8') < 2) {
            return ['valid' => false, 'message' => 'Country name must be at least 2 characters.'];
        }
        if (mb_strlen($value, 'UTF-8') > 50) {
            return ['valid' => false, 'message' => 'Country name must not exceed 50 characters.'];
        }
        if (!preg_match("/^[\p{L}\s\-']+$/u", $value)) {
            return ['valid' => false, 'message' => 'Country can only contain letters, spaces, hyphens, and apostrophes (no numbers or special characters).'];
        }
        return ['valid' => true, 'message' => ''];
    }

    /**
     * Validate an address field.
     * Min 10, max 250 characters after trimming.
     */
    public static function address(string $value): array {
        $value = trim($value);
        if ($value === '') {
            return ['valid' => true, 'message' => '']; // optional field
        }
        if (strlen($value) < 10) {
            return ['valid' => false, 'message' => 'Address must be at least 10 characters.'];
        }
        if (strlen($value) > 250) {
            return ['valid' => false, 'message' => 'Address must not exceed 250 characters.'];
        }
        return ['valid' => true, 'message' => ''];
    }

    /**
     * Validate a URL (required).
     * Must start with http:// or https://.
     */
    public static function projectUrl(string $value): array {
        $value = trim($value);
        if ($value === '') {
            return ['valid' => false, 'message' => 'Project URL is required (e.g. https://github.com/user/project).'];
        }
        if (!preg_match('/^https?:\/\/.+/i', $value)) {
            return ['valid' => false, 'message' => 'Project URL must start with http:// or https://.'];
        }
        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            return ['valid' => false, 'message' => 'Please enter a valid URL (e.g. https://github.com/user/project).'];
        }
        return ['valid' => true, 'message' => ''];
    }

    /**
     * Validate a Meeting Link (required).
     * Must start with http:// or https://.
     */
    public static function meetingLink(string $value): array {
        $value = trim($value);
        if ($value === '') {
            return ['valid' => false, 'message' => 'Meeting link is required (e.g. https://meet.google.com/abc-defg-hij).'];
        }
        if (!preg_match('/^https?:\/\/.+/i', $value)) {
            return ['valid' => false, 'message' => 'Meeting link must start with http:// or https://.'];
        }
        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            return ['valid' => false, 'message' => 'Please enter a valid meeting URL (e.g. https://meet.google.com/abc-defg-hij).'];
        }
        return ['valid' => true, 'message' => ''];
    }

    /**
     * Validate an optional URL field (blank is allowed, but if provided must be valid).
     */
    public static function optionalUrl(string $value, string $fieldLabel = 'URL'): array {
        $value = trim($value);
        if ($value === '') {
            return ['valid' => true, 'message' => ''];
        }
        if (!preg_match('/^https?:\/\/.+/i', $value)) {
            return ['valid' => false, 'message' => "{$fieldLabel} must start with http:// or https://."];
        }
        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            return ['valid' => false, 'message' => "Please enter a valid {$fieldLabel}."];
        }
        return ['valid' => true, 'message' => ''];
    }

    /**
     * Validate and normalize a skills string (comma-separated).
     * Max 300 characters, deduplicated (case-insensitive).
     * Returns ['valid', 'message', 'normalized'] — normalized is the cleaned value.
     */
    public static function skills(string $value): array {
        $value = trim($value);
        if ($value === '') {
            return ['valid' => true, 'message' => '', 'normalized' => ''];
        }
        if (strlen($value) > 300) {
            return ['valid' => false, 'message' => 'Skills must not exceed 300 characters.', 'normalized' => $value];
        }
        // Deduplicate
        $parts = array_map('trim', explode(',', $value));
        $parts = array_filter($parts, fn($p) => $p !== '');
        $seen  = [];
        $unique = [];
        foreach ($parts as $skill) {
            $key = strtolower($skill);
            if (!in_array($key, $seen)) {
                $seen[] = $key;
                $unique[] = $skill;
            }
        }
        $normalized = implode(', ', $unique);
        return ['valid' => true, 'message' => '', 'normalized' => $normalized];
    }

    /**
     * Validate a bio / about me field.
     * Required, min 20, max 500 characters.
     */
    public static function bio(string $value): array {
        $value = trim($value);
        if ($value === '') {
            return ['valid' => true, 'message' => '']; // optional — not required by registration
        }
        if (strlen($value) < 20) {
            return ['valid' => false, 'message' => 'Bio must be at least 20 characters.'];
        }
        if (strlen($value) > 500) {
            return ['valid' => false, 'message' => 'Bio must not exceed 500 characters.'];
        }
        return ['valid' => true, 'message' => ''];
    }

    /**
     * Validate an achievement description.
     * Max 500 characters.
     */
    public static function achievement(string $value): array {
        $value = trim($value);
        if (strlen($value) > 500) {
            return ['valid' => false, 'message' => 'Achievement description must not exceed 500 characters.'];
        }
        return ['valid' => true, 'message' => ''];
    }

    /**
     * Validate a language name.
     * Letters, spaces, hyphens only; 2–50 characters.
     */
    public static function languageName(string $value): array {
        $value = trim($value);
        if ($value === '') {
            return ['valid' => false, 'message' => 'Language name is required.'];
        }
        if (strlen($value) < 2) {
            return ['valid' => false, 'message' => 'Language name must be at least 2 characters.'];
        }
        if (strlen($value) > 50) {
            return ['valid' => false, 'message' => 'Language name must not exceed 50 characters.'];
        }
        if (!preg_match("/^[\p{L}\s\-]+$/u", $value)) {
            return ['valid' => false, 'message' => 'Language name can only contain letters, spaces, and hyphens.'];
        }
        return ['valid' => true, 'message' => ''];
    }

    /**
     * Validate a general short text field (name, title, etc.).
     * @param int $min Minimum length (default 1)
     * @param int $max Maximum length (default 150)
     */
    public static function text(string $value, string $label, int $min = 1, int $max = 150, bool $required = true): array {
        $value = trim($value);
        if ($value === '') {
            if ($required) {
                return ['valid' => false, 'message' => "{$label} is required."];
            }
            return ['valid' => true, 'message' => ''];
        }
        if (strlen($value) < $min) {
            return ['valid' => false, 'message' => "{$label} must be at least {$min} characters."];
        }
        if (strlen($value) > $max) {
            return ['valid' => false, 'message' => "{$label} must not exceed {$max} characters."];
        }
        return ['valid' => true, 'message' => ''];
    }

    /**
     * Validate an email address (required).
     */
    public static function email(string $value, string $label = 'Email'): array {
        $value = trim($value);
        if ($value === '') {
            return ['valid' => false, 'message' => "{$label} is required."];
        }
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return ['valid' => false, 'message' => "Please enter a valid {$label} address."];
        }
        return ['valid' => true, 'message' => ''];
    }

    /**
     * Validate a date string (Y-m-d).
     */
    public static function date(string $value, string $label = 'Date', bool $mustBeFuture = true): array {
        $value = trim($value);
        if ($value === '') {
            return ['valid' => false, 'message' => "{$label} is required."];
        }
        $d = DateTime::createFromFormat('Y-m-d', $value);
        if (!($d && $d->format('Y-m-d') === $value)) {
            return ['valid' => false, 'message' => "Please enter a valid date for {$label}."];
        }
        if ($mustBeFuture) {
            $today = new DateTime('today');
            if ($d < $today) {
                return ['valid' => false, 'message' => "{$label} cannot be in the past."];
            }
        }
        return ['valid' => true, 'message' => ''];
    }

    /**
     * Validate a numeric value (float/int).
     */
    public static function numeric(mixed $value, string $label, float $min = 0, ?float $max = null): array {
        if ($value === null || trim((string)$value) === '') {
            return ['valid' => false, 'message' => "{$label} is required."];
        }
        if (!is_numeric($value)) {
            return ['valid' => false, 'message' => "{$label} must be a valid number."];
        }
        $num = (float)$value;
        if ($num < $min) {
            return ['valid' => false, 'message' => "{$label} cannot be less than {$min}."];
        }
        if ($max !== null && $num > $max) {
            return ['valid' => false, 'message' => "{$label} cannot exceed {$max}."];
        }
        return ['valid' => true, 'message' => ''];
    }

    /**
     * Validate an integer value.
     */
    public static function integer(mixed $value, string $label, int $min = 0): array {
        if ($value === null || trim((string)$value) === '') {
            return ['valid' => false, 'message' => "{$label} is required."];
        }
        if (!filter_var($value, FILTER_VALIDATE_INT) && $value !== '0' && $value !== 0) {
            return ['valid' => false, 'message' => "{$label} must be a valid whole number."];
        }
        $num = (int)$value;
        if ($num < $min) {
            return ['valid' => false, 'message' => "{$label} must be at least {$min}."];
        }
        return ['valid' => true, 'message' => ''];
    }

    /**
     * Sanitize a single input value: trim + htmlspecialchars + strip dangerous tags.
     * Use instead of raw sanitize() when you also want to strip_tags.
     */
    public static function sanitizeInput(string $value): string {
        $value = trim($value);
        $value = strip_tags($value);
        return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Run multiple validations and collect errors.
     * @param array $rules  [ 'field' => result_array, ... ]  (results from static methods above)
     * @return array        Flat list of error strings (empty = all valid)
     */
    public static function collectErrors(array $rules): array {
        $errors = [];
        foreach ($rules as $result) {
            if (!$result['valid']) {
                $errors[] = $result['message'];
            }
        }
        return $errors;
    }
}
