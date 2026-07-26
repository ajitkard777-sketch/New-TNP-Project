<?php
/**
 * Migration 013 — Enhance Notifications Table and Seed Initial Production Data
 */
return function (Database $db): void {
    try {
        // 1. Add company_name column if missing
        $cols = $db->fetchAll("SHOW COLUMNS FROM `notifications` LIKE 'company_name'");
        if (empty($cols)) {
            $db->query("ALTER TABLE `notifications` ADD COLUMN `company_name` VARCHAR(255) NULL AFTER `category`");
        }

        // 2. Add link column if missing
        $colsLink = $db->fetchAll("SHOW COLUMNS FROM `notifications` LIKE 'link'");
        if (empty($colsLink)) {
            $db->query("ALTER TABLE `notifications` ADD COLUMN `link` VARCHAR(500) NULL AFTER `company_name`");
        }

        // 3. Ensure indexes exist
        $indexes = $db->fetchAll("SHOW INDEX FROM `notifications` WHERE Key_name = 'idx_notif_search'");
        if (empty($indexes)) {
            $db->query("ALTER TABLE `notifications` ADD INDEX `idx_notif_search` (`category`, `type`, `is_read`)");
        }

        // 4. Seed realistic notifications for active students if total notification count < 10
        $totalNotifs = (int)$db->fetchColumn("SELECT COUNT(*) FROM notifications");
        if ($totalNotifs < 5) {
            $students = $db->fetchAll("SELECT id FROM users WHERE role = 'student'");
            $sampleNotifs = [
                [
                    'title' => 'New Placement Drive: Senior Software Engineer',
                    'message' => 'TechCorp Solutions has posted a new drive for Senior Software Engineer with CTC up to 12 LPA. Application deadline is next week.',
                    'type' => 'info',
                    'category' => 'job',
                    'company_name' => 'TechCorp Solutions',
                    'link' => '/student/jobs'
                ],
                [
                    'title' => 'Technical Interview Scheduled',
                    'message' => 'Your technical round interview with InnovateTech has been scheduled for tomorrow at 10:00 AM via Google Meet.',
                    'type' => 'warning',
                    'category' => 'interview',
                    'company_name' => 'InnovateTech',
                    'link' => '/student/interviews'
                ],
                [
                    'title' => 'Application Status Update: Shortlisted!',
                    'message' => 'Congratulations! You have been shortlisted for Round 2 (Coding Test) by DataSystems Inc.',
                    'type' => 'success',
                    'category' => 'placement',
                    'company_name' => 'DataSystems Inc.',
                    'link' => '/student/applications'
                ],
                [
                    'title' => 'Upcoming Workshop: Full-Stack Web Development',
                    'message' => 'T&P Cell is organizing a hands-on 3-day boot camp on Modern Web Frameworks & Cloud Deployment.',
                    'type' => 'announcement',
                    'category' => 'training',
                    'company_name' => 'T&P Training Cell',
                    'link' => '/student/trainings'
                ]
            ];

            foreach ($students as $student) {
                foreach ($sampleNotifs as $sn) {
                    $db->insert(
                        "INSERT INTO notifications (user_id, title, message, type, category, company_name, link, is_read, is_global) VALUES (?, ?, ?, ?, ?, ?, ?, 0, 0)",
                        [$student['id'], $sn['title'], $sn['message'], $sn['type'], $sn['category'], $sn['company_name'], $sn['link']]
                    );
                }
            }

            // Also insert global announcement
            $db->insert(
                "INSERT INTO notifications (user_id, title, message, type, category, company_name, link, is_read, is_global) VALUES (NULL, 'Campus Recruitment Drive 2026 Orientation', 'All final year students are requested to attend the mandatory placement orientation session in the Main Auditorium.', 'info', 'announcement', 'T&P Cell', '/student/dashboard', 0, 1)"
            );
        }
    } catch (Exception $e) {
        error_log('Migration 013 Error: ' . $e->getMessage());
    }
};
