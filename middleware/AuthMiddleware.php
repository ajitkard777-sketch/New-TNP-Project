<?php
/**
 * TPMS - Authentication Middleware
 */

class AuthMiddleware {
    
    /**
     * Check if user is logged in
     */
    public static function isLoggedIn(): bool {
        return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
    }

    /**
     * Require login - redirect to login if not authenticated
     */
    public static function requireLogin(): void {
        if (!self::isLoggedIn()) {
            // Check remember me cookie
            if (isset($_COOKIE['tpms_remember'])) {
                $token = $_COOKIE['tpms_remember'];
                $db = Database::getInstance();
                $user = $db->fetchOne(
                    "SELECT id, email, role, status FROM users WHERE remember_token = ? AND status = 'active'",
                    [$token]
                );
                
                if ($user) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = $user['role'];
                    
                    // Update last login
                    $db->update("UPDATE users SET last_login = NOW() WHERE id = ?", [$user['id']]);
                    return;
                }
            }
            
            setFlash('warning', 'Please login to access this page.');
            redirect('/login');
        }
        
        // Check if user is still active
        $db = Database::getInstance();
        $user = $db->fetchOne(
            "SELECT status FROM users WHERE id = ?",
            [$_SESSION['user_id']]
        );
        
        if (!$user || $user['status'] !== 'active') {
            session_destroy();
            setFlash('danger', 'Your account has been deactivated. Please contact admin.');
            redirect('/login');
        }
    }

    /**
     * Guest only - redirect to dashboard if already logged in
     */
    public static function requireGuest(): void {
        if (self::isLoggedIn()) {
            $role = $_SESSION['user_role'];
            redirect("/{$role}/dashboard");
        }
    }

    /**
     * Get current user data
     */
    public static function getCurrentUser(): ?array {
        if (!self::isLoggedIn()) return null;
        
        $db = Database::getInstance();
        return $db->fetchOne("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
    }

    /**
     * Get current user profile (student/company/admin specific)
     */
    public static function getCurrentProfile(): ?array {
        if (!self::isLoggedIn()) return null;
        
        $db = Database::getInstance();
        $role = $_SESSION['user_role'];
        
        switch ($role) {
            case 'student':
                return $db->fetchOne(
                    "SELECT s.*, u.email FROM students s JOIN users u ON s.user_id = u.id WHERE s.user_id = ?",
                    [$_SESSION['user_id']]
                );
            case 'company':
                return $db->fetchOne(
                    "SELECT c.*, u.email FROM companies c JOIN users u ON c.user_id = u.id WHERE c.user_id = ?",
                    [$_SESSION['user_id']]
                );
            case 'admin':
                return $db->fetchOne(
                    "SELECT u.id, u.email, u.role, u.status, u.created_at FROM users u WHERE u.id = ? AND u.role = 'admin'",
                    [$_SESSION['user_id']]
                );
            default:
                return null;
        }
    }
}
