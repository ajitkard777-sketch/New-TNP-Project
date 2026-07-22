<?php
/**
 * TPMS - CSRF Protection Middleware
 */

class CsrfMiddleware {
    
    /**
     * Generate CSRF token
     */
    public static function generateToken(): void {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    /**
     * Get current CSRF token
     */
    public static function getToken(): string {
        if (!isset($_SESSION['csrf_token'])) {
            self::generateToken();
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Generate hidden input field
     */
    public static function tokenField(): string {
        return '<input type="hidden" name="csrf_token" value="' . self::getToken() . '">';
    }

    /**
     * Validate CSRF token
     */
    public static function validateToken(): bool {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        
        if (empty($token) || !isset($_SESSION['csrf_token'])) {
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Require valid CSRF token - die if invalid
     */
    public static function requireValidToken(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!self::validateToken()) {
                if (isAjax()) {
                    jsonResponse(['success' => false, 'message' => 'Invalid CSRF token. Please refresh the page.'], 403);
                } else {
                    setFlash('danger', 'Invalid security token. Please try again.');
                    redirectBack();
                }
            }
        }
    }

    /**
     * Regenerate token (call after successful form submission)
     */
    public static function regenerateToken(): void {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}
