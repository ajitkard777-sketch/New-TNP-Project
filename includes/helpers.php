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
 * Format currency
 */
function formatCurrency(float $amount, string $currency = 'INR'): string {
    if ($currency === 'INR') {
        if ($amount >= 10000000) {
            return '₹' . number_format($amount / 10000000, 2) . ' Cr';
        } elseif ($amount >= 100000) {
            return '₹' . number_format($amount / 100000, 2) . ' LPA';
        } else {
            return '₹' . number_format($amount);
        }
    }
    return $currency . ' ' . number_format($amount, 2);
}

/**
 * Format salary range in LPA (values stored as LPA, e.g. 3.5 = 3.5 LPA)
 */
function formatSalaryRange(?float $min, ?float $max, string $currency = 'INR'): string {
    // Values <= 200 are already in LPA (direct storage)
    $formatLPA = function(float $val): string {
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
 */
function renderPagination(array $pagination, string $baseUrl): string {
    if ($pagination['total_pages'] <= 1) return '';
    
    $html = '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';
    
    // Previous
    $prevDisabled = $pagination['has_prev'] ? '' : ' disabled';
    $prevPage = $pagination['current_page'] - 1;
    $html .= "<li class='page-item{$prevDisabled}'><a class='page-link' href='{$baseUrl}?page={$prevPage}'><i class='fas fa-chevron-left'></i></a></li>";
    
    // Page numbers
    $start = max(1, $pagination['current_page'] - 2);
    $end = min($pagination['total_pages'], $pagination['current_page'] + 2);
    
    if ($start > 1) {
        $html .= "<li class='page-item'><a class='page-link' href='{$baseUrl}?page=1'>1</a></li>";
        if ($start > 2) $html .= "<li class='page-item disabled'><span class='page-link'>...</span></li>";
    }
    
    for ($i = $start; $i <= $end; $i++) {
        $active = $i === $pagination['current_page'] ? ' active' : '';
        $html .= "<li class='page-item{$active}'><a class='page-link' href='{$baseUrl}?page={$i}'>{$i}</a></li>";
    }
    
    if ($end < $pagination['total_pages']) {
        if ($end < $pagination['total_pages'] - 1) $html .= "<li class='page-item disabled'><span class='page-link'>...</span></li>";
        $html .= "<li class='page-item'><a class='page-link' href='{$baseUrl}?page={$pagination['total_pages']}'>{$pagination['total_pages']}</a></li>";
    }
    
    // Next
    $nextDisabled = $pagination['has_next'] ? '' : ' disabled';
    $nextPage = $pagination['current_page'] + 1;
    $html .= "<li class='page-item{$nextDisabled}'><a class='page-link' href='{$baseUrl}?page={$nextPage}'><i class='fas fa-chevron-right'></i></a></li>";
    
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
