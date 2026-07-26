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
     * Set OTP with secure hashing, expiration, cooldown tracking, and resend count increment
     */
    public function setOTP(int $id, string $otp): void {
        $expiresAt = date('Y-m-d H:i:s', time() + OTP_EXPIRY);
        $hashedOtp = hash('sha256', trim($otp));
        $now = date('Y-m-d H:i:s');

        $this->db->update(
            "UPDATE users SET 
                otp = ?, 
                otp_expires_at = ?, 
                otp_last_sent_at = ?, 
                otp_resend_count = otp_resend_count + 1, 
                otp_attempts = 0 
             WHERE id = ?",
            [$hashedOtp, $expiresAt, $now, $id]
        );
    }

    /**
     * Check if OTP can be resent (60-second cooldown rule)
     */
    public function canResendOTP(int $id): array {
        $user = $this->findById($id);
        if (!$user) {
            return ['can_resend' => false, 'seconds_remaining' => 0, 'reason' => 'User not found'];
        }

        if (empty($user['otp_last_sent_at'])) {
            return ['can_resend' => true, 'seconds_remaining' => 0];
        }

        $lastSent = strtotime($user['otp_last_sent_at']);
        $elapsed = time() - $lastSent;
        $cooldown = 60; // 60 seconds cooldown

        if ($elapsed >= $cooldown) {
            return ['can_resend' => true, 'seconds_remaining' => 0];
        } else {
            return ['can_resend' => false, 'seconds_remaining' => $cooldown - $elapsed];
        }
    }

    /**
     * Verify OTP with detailed status return, attempt limits, expiration check, and hash match
     */
    public function verifyOTPResult(int $id, string $otp): array {
        $user = $this->findById($id);
        if (!$user) {
            return ['success' => false, 'reason' => 'User not found.'];
        }

        if (empty($user['otp']) || empty($user['otp_expires_at'])) {
            return ['success' => false, 'reason' => 'No active OTP found. Please request a new OTP.'];
        }

        // Check expiration
        if (strtotime($user['otp_expires_at']) <= time()) {
            return ['success' => false, 'reason' => 'OTP has expired. Please request a new OTP.'];
        }

        // Check max attempt limit (max 5 attempts per generated OTP)
        $maxAttempts = 5;
        if (($user['otp_attempts'] ?? 0) >= $maxAttempts) {
            return ['success' => false, 'reason' => 'Maximum verification attempts exceeded. Please request a new OTP.'];
        }

        // Check hashed match (or legacy plain match)
        $inputHash = hash('sha256', trim($otp));
        $isMatch = hash_equals($user['otp'], $inputHash) || ($user['otp'] === trim($otp));

        if ($isMatch) {
            // Success: clear OTP fields and set email_verified
            $this->db->update(
                "UPDATE users SET 
                    otp = NULL, 
                    otp_expires_at = NULL, 
                    otp_resend_count = 0, 
                    otp_attempts = 0, 
                    otp_last_sent_at = NULL, 
                    email_verified = 1 
                 WHERE id = ?",
                [$id]
            );
            return ['success' => true];
        } else {
            // Increment attempts
            $newAttempts = ($user['otp_attempts'] ?? 0) + 1;
            $this->db->update("UPDATE users SET otp_attempts = ? WHERE id = ?", [$newAttempts, $id]);
            $attemptsRemaining = max(0, $maxAttempts - $newAttempts);
            
            if ($attemptsRemaining === 0) {
                return ['success' => false, 'reason' => 'Invalid OTP. Maximum verification attempts exceeded. Please request a new OTP.'];
            }
            return ['success' => false, 'reason' => "Invalid OTP. You have {$attemptsRemaining} attempt(s) remaining."];
        }
    }

    /**
     * Backward-compatible verifyOTP helper
     */
    public function verifyOTP(int $id, string $otp): bool {
        $result = $this->verifyOTPResult($id, $otp);
        return $result['success'] ?? false;
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

        // Also sync columns in users table
        $this->db->update(
            "UPDATE users SET password_reset_token = ?, password_reset_expiry = ? WHERE id = ?",
            [$token, $expiresAt, $userId]
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
        $this->db->update("UPDATE users SET password_reset_token = NULL, password_reset_expiry = NULL WHERE password_reset_token = ?", [$token]);
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
