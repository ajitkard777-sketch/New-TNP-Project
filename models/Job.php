<?php
/**
 * TPMS - Job Model
 */
class Job {
    private Database $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function findById(int $id): ?array { return $this->db->fetchOne("SELECT j.*, c.company_name, c.logo, c.city as company_city FROM jobs j JOIN companies c ON j.company_id = c.id WHERE j.id = ?", [$id]); }

    public function create(array $data): int {
        return $this->db->insert("INSERT INTO jobs (company_id, title, description, job_type, work_mode, location, salary_min, salary_max, openings, skills_required, eligibility_cgpa, eligibility_branches, eligibility_backlogs, experience_required, application_deadline, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [$data['company_id'], $data['title'], $data['description'] ?? '', $data['job_type'] ?? 'full-time', $data['work_mode'] ?? 'onsite', $data['location'] ?? '', $data['salary_min'] ?? 0, $data['salary_max'] ?? 0, $data['openings'] ?? 1, $data['skills_required'] ?? '', $data['eligibility_cgpa'] ?? 0, $data['eligibility_branches'] ?? '', $data['eligibility_backlogs'] ?? 0, $data['experience_required'] ?? '', $data['application_deadline'] ?? null, $data['status'] ?? 'pending']);
    }

    public function update(int $id, array $data): int {
        $fields = []; $values = [];
        foreach ($data as $k => $v) { $fields[] = "`{$k}` = ?"; $values[] = $v; }
        $values[] = $id;
        return $this->db->update("UPDATE jobs SET " . implode(', ', $fields) . " WHERE id = ?", $values);
    }

    public function delete(int $id): int { return $this->db->delete("DELETE FROM jobs WHERE id = ?", [$id]); }

    public function getByCompany(int $companyId): array {
        return $this->db->fetchAll("SELECT j.*, (SELECT COUNT(*) FROM applications a WHERE a.job_id = j.id) as application_count FROM jobs j WHERE j.company_id = ? ORDER BY j.created_at DESC", [$companyId]);
    }

    public function getApplications(int $jobId): array {
        return $this->db->fetchAll("SELECT a.*, s.first_name, s.last_name, s.branch, s.cgpa, s.phone, s.profile_photo, s.resume_path, u.email FROM applications a JOIN students s ON a.student_id = s.id JOIN users u ON s.user_id = u.id WHERE a.job_id = ? ORDER BY a.applied_at DESC", [$jobId]);
    }

    public function updateApplicationStatus(int $appId, string $status): int {
        return $this->db->update("UPDATE applications SET status = ? WHERE id = ?", [$status, $appId]);
    }

    public function getActiveCount(): int { return (int)$this->db->fetchColumn("SELECT COUNT(*) FROM jobs WHERE status = 'active'"); }
    public function getTotalCount(): int { return (int)$this->db->fetchColumn("SELECT COUNT(*) FROM jobs"); }
}
