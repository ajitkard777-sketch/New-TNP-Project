<?php
/**
 * TPMS - JWT Authentication Middleware (for API routes)
 */

class JwtMiddleware {
    
    /**
     * Authenticate API request via JWT
     */
    public static function authenticate(): ?array {
        $token = self::extractToken();
        
        if (!$token) {
            jsonResponse(['success' => false, 'message' => 'No authentication token provided.'], 401);
            return null;
        }
        
        $payload = verifyJWT($token);
        
        if (!$payload) {
            jsonResponse(['success' => false, 'message' => 'Invalid or expired token.'], 401);
            return null;
        }
        
        // Verify user still exists and is active
        $db = Database::getInstance();
        $user = $db->fetchOne(
            "SELECT id, email, role, status FROM users WHERE id = ? AND status = 'active'",
            [$payload['user_id']]
        );
        
        if (!$user) {
            jsonResponse(['success' => false, 'message' => 'User account not found or inactive.'], 401);
            return null;
        }
        
        return $user;
    }

    /**
     * Extract Bearer token from Authorization header
     */
    private static function extractToken(): ?string {
        $headers = self::getAuthorizationHeader();
        
        if ($headers && preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
            return $matches[1];
        }
        
        // Also check query parameter as fallback
        return $_GET['token'] ?? null;
    }

    /**
     * Get Authorization header
     */
    private static function getAuthorizationHeader(): ?string {
        $headers = null;
        
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER['Authorization']);
        } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers = trim($_SERVER['HTTP_AUTHORIZATION']);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            $requestHeaders = array_combine(
                array_map('ucwords', array_keys($requestHeaders)),
                array_values($requestHeaders)
            );
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        
        return $headers;
    }

    /**
     * Require specific role for API
     */
    public static function requireRole(array $user, string $role): void {
        if ($user['role'] !== $role) {
            jsonResponse(['success' => false, 'message' => 'Insufficient permissions.'], 403);
        }
    }
}
