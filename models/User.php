<?php
/**
 * TPMS - User Model
 */

class User {
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Find user by ID
     */
    public function findById(int $id): ?array {
        return $this->db->fetchOne("SELECT * FROM users WHERE id = ?", [$id]);
    }

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?array {
        return $this->db->fetchOne("SELECT * FROM users WHERE email = ?", [$email]);
    }

    /**
     * Check if email exists
     */
    public function emailExists(string $email): bool {
        return (bool)$this->db->fetchColumn("SELECT COUNT(*) FROM users WHERE email = ?", [$email]);
    }

    /**
     * Create user
     */
    public function create(array $data): int {
        return $this->db->insert(
            "INSERT INTO users (email, password, role, status, email_verified) VALUES (?, ?, ?, ?, ?)",
            [
                $data['email'],
                password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 10]),
                $data['role'],
                $data['status'] ?? 'pending',
                $data['email_verified'] ?? 0
            ]
        );
    }

    /**
     * Update user
     */
    public function update(int $id, array $data): int {
        $fields = [];
        $values = [];
        foreach ($data as $key => $value) {
            $fields[] = "`{$key}` = ?";
            $values[] = $value;
        }
        $values[] = $id;
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
        return $this->db->update($sql, $values);
    }

    /**
     * Delete user
     */
    public function delete(int $id): int {
        return $this->db->delete("DELETE FROM users WHERE id = ?", [$id]);
    }

    /**
     * Verify password
     */
    public function verifyPassword(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }

    /**
     * Update password
     */
    public function updatePassword(int $id, string $newPassword): int {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 10]);
        return $this->db->update("UPDATE users SET password = ? WHERE id = ?", [$hash, $id]);
    }

    /**
     * Set remember token
     */
    public function setRememberToken(int $id, string $token): void {
        $this->db->update("UPDATE users SET remember_token = ? WHERE id = ?", [$token, $id]);
    }

    /**
     * Clear remember token
     */
    public function clearRememberToken(int $id): void {
        $this->db->update("UPDATE users SET remember_token = NULL WHERE id = ?", [$id]);
    }

    /**
     * Generate & Save cryptographically secure 6-digit OTP
     */
    public function generateAndSaveOTP(int $userId): string {
        $otp = sprintf('%06d', random_int(100000, 999999));
        $expiresAt = date('Y-m-d H:i:s', time() + 600); // 10 minutes
        $now = date('Y-m-d H:i:s');

        $this->db->update(
            "UPDATE users SET otp = ?, otp_expires_at = ?, otp_last_sent_at = ?, otp_attempts = 0 WHERE id = ?",
            [$otp, $expiresAt, $now, $userId]
        );
        return $otp;
    }

    /**
     * Set OTP (legacy alias)
     */
    public function setOTP(int $id, string $otp): void {
        $expiresAt = date('Y-m-d H:i:s', time() + 600); // 10 minutes
        $now = date('Y-m-d H:i:s');
        $this->db->update(
            "UPDATE users SET otp = ?, otp_expires_at = ?, otp_last_sent_at = ?, otp_attempts = 0 WHERE id = ?",
            [$otp, $expiresAt, $now, $id]
        );
    }

    /**
     * Check if user can resend OTP (60s cooldown)
     */
    public function canResendOTP(int $userId): array {
        $user = $this->findById($userId);
        if (!$user) return ['allowed' => false, 'wait' => 0, 'message' => 'User not found.'];

        if (!empty($user['otp_last_sent_at'])) {
            $elapsed = time() - strtotime($user['otp_last_sent_at']);
            if ($elapsed < 60) {
                $wait = 60 - $elapsed;
                return ['allowed' => false, 'wait' => $wait, 'message' => "Please wait {$wait} second(s) before requesting a new OTP."];
            }
        }
        return ['allowed' => true, 'wait' => 0];
    }

    /**
     * Verify OTP with attempt limits, expiry checks, and role activation
     */
    public function verifyOTP(int $id, string $otp): array {
        $user = $this->findById($id);
        if (!$user) {
            return ['success' => false, 'message' => 'User account not found.'];
        }

        if ((int)$user['email_verified'] === 1) {
            return ['success' => true, 'already_verified' => true, 'user' => $user, 'message' => 'Email is already verified.'];
        }

        if ((int)$user['otp_attempts'] >= 5) {
            return ['success' => false, 'message' => 'Too many failed attempts. Please click "Resend OTP" to generate a new code.'];
        }

        if (empty($user['otp']) || empty($user['otp_expires_at'])) {
            return ['success' => false, 'message' => 'No active OTP found. Please click "Resend OTP".'];
        }

        if (strtotime($user['otp_expires_at']) < time()) {
            return ['success' => false, 'message' => 'OTP has expired. Please click "Resend OTP" to generate a new code.'];
        }

        if ($user['otp'] !== trim($otp)) {
            $newAttempts = (int)$user['otp_attempts'] + 1;
            $this->db->update("UPDATE users SET otp_attempts = ? WHERE id = ?", [$newAttempts, $id]);
            $remaining = max(0, 5 - $newAttempts);
            $msg = ($remaining > 0)
                ? "Invalid OTP code. {$remaining} attempt(s) remaining."
                : "Invalid OTP code. Max attempts reached. Please click Resend OTP.";
            return ['success' => false, 'message' => $msg];
        }

        // Verification Success!
        $newStatus = ($user['role'] === 'student') ? 'active' : 'pending'; // Company stays pending for Admin approval
        $this->db->update(
            "UPDATE users SET otp = NULL, otp_expires_at = NULL, otp_attempts = 0, email_verified = 1, status = ? WHERE id = ?",
            [$newStatus, $id]
        );

        $updatedUser = $this->findById($id);
        return [
            'success' => true,
            'user' => $updatedUser,
            'message' => ($user['role'] === 'company')
                ? 'Email verified successfully! Your company account is awaiting admin approval.'
                : 'Email verified successfully! Your account is now active.'
        ];
    }

    /**
     * Create password reset token
     */
    public function createPasswordReset(int $userId, string $token): void {
        // Invalidate old tokens
        $this->db->update(
            "UPDATE password_resets SET used = 1 WHERE user_id = ?",
            [$userId]
        );
        
        $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 hour
        $this->db->insert(
            "INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)",
            [$userId, $token, $expiresAt]
        );
    }

    /**
     * Verify password reset token
     */
    public function verifyPasswordReset(string $token): ?array {
        return $this->db->fetchOne(
            "SELECT pr.*, u.email FROM password_resets pr 
             JOIN users u ON pr.user_id = u.id 
             WHERE pr.token = ? AND pr.used = 0 AND pr.expires_at > NOW()",
            [$token]
        );
    }

    /**
     * Mark password reset as used
     */
    public function usePasswordReset(string $token): void {
        $this->db->update("UPDATE password_resets SET used = 1 WHERE token = ?", [$token]);
    }

    /**
     * Update last login
     */
    public function updateLastLogin(int $id): void {
        $this->db->update("UPDATE users SET last_login = NOW(), login_attempts = 0 WHERE id = ?", [$id]);
    }

    /**
     * Increment login attempts
     */
    public function incrementLoginAttempts(int $id): void {
        $this->db->update(
            "UPDATE users SET login_attempts = login_attempts + 1 WHERE id = ?",
            [$id]
        );
        
        // Lock after 5 failed attempts
        $user = $this->findById($id);
        if ($user && $user['login_attempts'] >= 5) {
            $lockedUntil = date('Y-m-d H:i:s', time() + 1800); // 30 min lock
            $this->db->update(
                "UPDATE users SET locked_until = ? WHERE id = ?",
                [$lockedUntil, $id]
            );
        }
    }

    /**
     * Check if account is locked
     */
    public function isLocked(int $id): bool {
        $user = $this->findById($id);
        return $user && $user['locked_until'] && strtotime($user['locked_until']) > time();
    }

    /**
     * Get all users by role with pagination
     */
    public function getByRole(string $role, int $offset = 0, int $limit = RECORDS_PER_PAGE, string $search = ''): array {
        $params = [$role];
        $searchClause = '';
        
        if ($search) {
            $searchClause = " AND (u.email LIKE ?)";
            $params[] = "%{$search}%";
        }
        
        $params[] = $limit;
        $params[] = $offset;
        
        return $this->db->fetchAll(
            "SELECT u.* FROM users u WHERE u.role = ?{$searchClause} ORDER BY u.created_at DESC LIMIT ? OFFSET ?",
            $params
        );
    }

    /**
     * Count users by role
     */
    public function countByRole(string $role, string $search = ''): int {
        $params = [$role];
        $searchClause = '';
        
        if ($search) {
            $searchClause = " AND (email LIKE ?)";
            $params[] = "%{$search}%";
        }
        
        return (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM users WHERE role = ?{$searchClause}",
            $params
        );
    }

    /**
     * Activate user
     */
    public function activate(int $id): int {
        return $this->db->update("UPDATE users SET status = 'active' WHERE id = ?", [$id]);
    }

    /**
     * Deactivate user
     */
    public function deactivate(int $id): int {
        return $this->db->update("UPDATE users SET status = 'inactive' WHERE id = ?", [$id]);
    }

    /**
     * Block user
     */
    public function block(int $id): int {
        return $this->db->update("UPDATE users SET status = 'blocked' WHERE id = ?", [$id]);
    }
}
