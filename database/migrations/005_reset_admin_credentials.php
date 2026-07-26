<?php
/**
 * Migration 005 — Force-reset default accounts and unblock admin
 *
 * Ensures:
 *  - admin@tpms.com    password is 'Admin@123'
 *  - student@tpms.com  password is 'Student@123'
 *  - company@tpms.com  password is 'Company@123'
 *  - Resets login_attempts = 0, locked_until = NULL, status = 'active'
 */
return function (Database $db): void {
    $pdo = $db->getConnection();

    $adminHash   = password_hash('Admin@123',   PASSWORD_BCRYPT, ['cost' => 10]);
    $studentHash = password_hash('Student@123', PASSWORD_BCRYPT, ['cost' => 10]);
    $companyHash = password_hash('Company@123', PASSWORD_BCRYPT, ['cost' => 10]);

    // Force update admin account password & status
    $stmt = $pdo->prepare(
        "UPDATE users
         SET password = ?, status = 'active', login_attempts = 0, locked_until = NULL, email_verified = 1
         WHERE email = 'admin@tpms.com'"
    );
    $stmt->execute([$adminHash]);

    // If admin@tpms.com doesn't exist at all, insert it
    if ($stmt->rowCount() === 0) {
        $check = $pdo->prepare("SELECT id FROM users WHERE email = 'admin@tpms.com'");
        $check->execute();
        if (!$check->fetchColumn()) {
            $pdo->prepare(
                "INSERT INTO users (email, password, role, status, email_verified)
                 VALUES ('admin@tpms.com', ?, 'admin', 'active', 1)"
            )->execute([$adminHash]);
        }
    }

    // Force update student account
    $stmt = $pdo->prepare(
        "UPDATE users
         SET password = ?, status = 'active', login_attempts = 0, locked_until = NULL, email_verified = 1
         WHERE email = 'student@tpms.com'"
    );
    $stmt->execute([$studentHash]);

    // Force update company account
    $stmt = $pdo->prepare(
        "UPDATE users
         SET password = ?, status = 'active', login_attempts = 0, locked_until = NULL, email_verified = 1
         WHERE email = 'company@tpms.com'"
    );
    $stmt->execute([$companyHash]);
};
