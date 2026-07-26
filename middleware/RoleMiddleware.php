<?php
/**
 * TPMS - Role-Based Access Control Middleware
 */

class RoleMiddleware {
    
    /**
     * Require specific role
     */
    public static function requireRole(string $role): void {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== $role) {
            if (isAjax()) {
                jsonResponse(['success' => false, 'message' => 'Unauthorized access.'], 403);
            }
            http_response_code(403);
            require_once VIEWS_PATH . '/errors/403.php';
            exit;
        }
    }

    /**
     * Require one of multiple roles
     */
    public static function requireAnyRole(array $roles): void {
        if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], $roles)) {
            if (isAjax()) {
                jsonResponse(['success' => false, 'message' => 'Unauthorized access.'], 403);
            }
            http_response_code(403);
            require_once VIEWS_PATH . '/errors/403.php';
            exit;
        }
    }

    /**
     * Check if user has role (without redirect)
     */
    public static function hasRole(string $role): bool {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
    }

    /**
     * Check if user is admin
     */
    public static function isAdmin(): bool {
        return self::hasRole('admin');
    }

    /**
     * Check if user is student
     */
    public static function isStudent(): bool {
        return self::hasRole('student');
    }

    /**
     * Check if user is company
     */
    public static function isCompany(): bool {
        return self::hasRole('company');
    }
}
