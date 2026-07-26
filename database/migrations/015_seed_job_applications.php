<?php
/**
 * Migration 015 — Seed Student Job Applications for Jobs Without Applicants (e.g. microsofrt job)
 */
return function (Database $db): void {
    try {
        // Fetch jobs with 0 applications
        $jobsWithoutApps = $db->fetchAll(
            "SELECT j.id, j.title, j.company_id FROM jobs j WHERE (SELECT COUNT(*) FROM applications a WHERE a.job_id = j.id) = 0"
        );

        $students = $db->fetchAll("SELECT id, first_name, last_name FROM students LIMIT 3");

        if (!empty($jobsWithoutApps) && !empty($students)) {
            $statuses = ['applied', 'shortlisted', 'interview'];
            foreach ($jobsWithoutApps as $j) {
                foreach ($students as $idx => $s) {
                    $status = $statuses[$idx % count($statuses)];
                    // Prevent duplicate insertion
                    $exists = (int)$db->fetchColumn(
                        "SELECT COUNT(*) FROM applications WHERE job_id = ? AND student_id = ?",
                        [$j['id'], $s['id']]
                    );
                    if (!$exists) {
                        $db->insert(
                            "INSERT INTO applications (job_id, student_id, status, applied_at) VALUES (?, ?, ?, NOW())",
                            [$j['id'], $s['id'], $status]
                        );
                    }
                }
            }
        }
    } catch (Exception $e) {
        error_log('Migration 015 Error: ' . $e->getMessage());
    }
};
