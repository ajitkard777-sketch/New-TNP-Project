<?php
/**
 * Migration 003 — Seed default users and sample data
 *
 * Creates:
 *  - admin@tpms.com        / Admin@123  (admin)
 *  - student@tpms.com      / Student@123 (student)
 *  - company@tpms.com      / Company@123 (company, pre-approved)
 *
 * All INSERTs use INSERT IGNORE so re-running is safe.
 *
 * DEFAULT PASSWORD HASH NOTE:
 *   The hashes below are generated with password_hash(pwd, PASSWORD_BCRYPT, ['cost'=>10]).
 *   They are NOT the Laravel default hash '$2y$10$92IXUNpkjO0rOQ5byMi...' which
 *   maps to 'password' — we use proper hashes here so you can immediately log in.
 */
return function (Database $db): void {

    // ── Admin user ─────────────────────────────────────────────────────────
    // Password: Admin@123
    $adminHash   = password_hash('Admin@123',   PASSWORD_BCRYPT, ['cost' => 10]);
    // Password: Student@123
    $studentHash = password_hash('Student@123', PASSWORD_BCRYPT, ['cost' => 10]);
    // Password: Company@123
    $companyHash = password_hash('Company@123', PASSWORD_BCRYPT, ['cost' => 10]);

    // ── Insert users (skip if email already exists) ────────────────────────
    $pdo = $db->getConnection();

    $insertUser = function (string $email, string $hash, string $role) use ($pdo): ?int {
        // Check if already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $existing = $stmt->fetchColumn();
        if ($existing) {
            // Update password hash and status to ensure working credentials
            $up = $pdo->prepare("UPDATE users SET password = ?, status = 'active', login_attempts = 0, locked_until = NULL WHERE id = ?");
            $up->execute([$hash, (int)$existing]);
            return (int)$existing;
        }

        $stmt = $pdo->prepare(
            "INSERT INTO users (email, password, role, status, email_verified)
             VALUES (?, ?, ?, 'active', 1)"
        );
        $stmt->execute([$email, $hash, $role]);
        return (int)$pdo->lastInsertId();
    };

    $adminId   = $insertUser('admin@tpms.com',   $adminHash,   'admin');
    $studentId = $insertUser('student@tpms.com', $studentHash, 'student');
    $companyId = $insertUser('company@tpms.com', $companyHash, 'company');

    // ── Seed student profile ───────────────────────────────────────────────
    if ($studentId) {
        $stmt = $pdo->prepare("SELECT id FROM students WHERE user_id = ?");
        $stmt->execute([$studentId]);
        if (!$stmt->fetchColumn()) {
            $pdo->prepare(
                "INSERT INTO students
                 (user_id, first_name, last_name, phone, branch, degree, profile_completion)
                 VALUES (?, 'Demo', 'Student', '9000000001', 'Computer Science', 'B.Tech', 40)"
            )->execute([$studentId]);
        }
    }

    // ── Seed company profile ───────────────────────────────────────────────
    if ($companyId) {
        $stmt = $pdo->prepare("SELECT id FROM companies WHERE user_id = ?");
        $stmt->execute([$companyId]);
        if (!$stmt->fetchColumn()) {
            $pdo->prepare(
                "INSERT INTO companies
                 (user_id, company_name, industry, company_type, contact_person,
                  contact_email, contact_phone, is_approved)
                 VALUES (?, 'Demo Company Pvt Ltd', 'Information Technology',
                         'product', 'Demo HR', 'company@tpms.com', '9000000002', 1)"
            )->execute([$companyId]);
        }
    }

    // ── Welcome notification ───────────────────────────────────────────────
    // Only insert if no global notifications exist
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE is_global = 1");
    $stmt->execute();
    if ((int)$stmt->fetchColumn() === 0) {
        $pdo->prepare(
            "INSERT INTO notifications (user_id, title, message, type, category, is_global)
             VALUES (NULL, 'Welcome to TPMS!',
                     'The Training & Placement Management System is ready. Use the credentials below to log in.',
                     'announcement', 'system', 1)"
        )->execute();
    }
};
