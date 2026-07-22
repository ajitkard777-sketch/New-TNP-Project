<?php
/**
 * TPMS - Student Model
 */

class Student {
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findById(int $id): ?array {
        return $this->db->fetchOne("SELECT s.*, u.email, u.status as user_status, u.created_at as registered_at FROM students s JOIN users u ON s.user_id = u.id WHERE s.id = ?", [$id]);
    }

    public function findByUserId(int $userId): ?array {
        return $this->db->fetchOne("SELECT s.*, u.email, u.status as user_status FROM students s JOIN users u ON s.user_id = u.id WHERE s.user_id = ?", [$userId]);
    }

    public function getAll(int $offset = 0, int $limit = RECORDS_PER_PAGE, string $search = '', string $branch = '', string $status = ''): array {
        $params = [];
        $where = "1=1";
        if ($search) { $where .= " AND (s.first_name LIKE ? OR s.last_name LIKE ? OR u.email LIKE ? OR s.enrollment_no LIKE ?)"; $params = array_merge($params, ["%$search%", "%$search%", "%$search%", "%$search%"]); }
        if ($branch) { $where .= " AND s.branch = ?"; $params[] = $branch; }
        if ($status) { $where .= " AND u.status = ?"; $params[] = $status; }
        $params[] = $limit;
        $params[] = $offset;
        return $this->db->fetchAll("SELECT s.*, u.email, u.status as user_status FROM students s JOIN users u ON s.user_id = u.id WHERE $where ORDER BY s.created_at DESC LIMIT ? OFFSET ?", $params);
    }

    public function count(string $search = '', string $branch = '', string $status = ''): int {
        $params = [];
        $where = "1=1";
        if ($search) { $where .= " AND (s.first_name LIKE ? OR s.last_name LIKE ? OR u.email LIKE ?)"; $params = array_merge($params, ["%$search%", "%$search%", "%$search%"]); }
        if ($branch) { $where .= " AND s.branch = ?"; $params[] = $branch; }
        if ($status) { $where .= " AND u.status = ?"; $params[] = $status; }
        return (int)$this->db->fetchColumn("SELECT COUNT(*) FROM students s JOIN users u ON s.user_id = u.id WHERE $where", $params);
    }

    public function update(int $studentId, array $data): int {
        $fields = [];
        $values = [];
        foreach ($data as $key => $value) {
            $fields[] = "`{$key}` = ?";
            $values[] = $value;
        }
        $values[] = $studentId;
        return $this->db->update("UPDATE students SET " . implode(', ', $fields) . " WHERE id = ?", $values);
    }

    public function updateByUserId(int $userId, array $data): int {
        $fields = [];
        $values = [];
        foreach ($data as $key => $value) {
            $fields[] = "`{$key}` = ?";
            $values[] = $value;
        }
        $values[] = $userId;
        return $this->db->update("UPDATE students SET " . implode(', ', $fields) . " WHERE user_id = ?", $values);
    }

    public function updateProfileCompletion(int $studentId): void {
        $student = $this->findById($studentId);
        if ($student) {
            $completion = calculateProfileCompletion($student);
            $this->update($studentId, ['profile_completion' => $completion]);
        }
    }

    // Projects
    public function getProjects(int $studentId): array {
        return $this->db->fetchAll("SELECT * FROM student_projects WHERE student_id = ? ORDER BY created_at DESC", [$studentId]);
    }

    public function addProject(int $studentId, array $data): int {
        return $this->db->insert("INSERT INTO student_projects (student_id, title, description, technologies, project_url, github_url, start_date, end_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [$studentId, $data['title'], $data['description'] ?? null, $data['technologies'] ?? null, $data['project_url'] ?? null, $data['github_url'] ?? null, $data['start_date'] ?? null, $data['end_date'] ?? null]);
    }

    public function deleteProject(int $projectId, int $studentId): int {
        return $this->db->delete("DELETE FROM student_projects WHERE id = ? AND student_id = ?", [$projectId, $studentId]);
    }

    // Certifications
    public function getCertifications(int $studentId): array {
        return $this->db->fetchAll("SELECT * FROM student_certifications WHERE student_id = ? ORDER BY created_at DESC", [$studentId]);
    }

    public function addCertification(int $studentId, array $data): int {
        return $this->db->insert("INSERT INTO student_certifications (student_id, title, issuing_org, issue_date, expiry_date, credential_id, credential_url) VALUES (?, ?, ?, ?, ?, ?, ?)",
            [$studentId, $data['title'], $data['issuing_org'] ?? null, $data['issue_date'] ?? null, $data['expiry_date'] ?? null, $data['credential_id'] ?? null, $data['credential_url'] ?? null]);
    }

    public function deleteCertification(int $certId, int $studentId): int {
        return $this->db->delete("DELETE FROM student_certifications WHERE id = ? AND student_id = ?", [$certId, $studentId]);
    }

    // Languages
    public function getLanguages(int $studentId): array {
        return $this->db->fetchAll("SELECT * FROM student_languages WHERE student_id = ? ORDER BY created_at DESC", [$studentId]);
    }

    public function addLanguage(int $studentId, array $data): int {
        return $this->db->insert("INSERT INTO student_languages (student_id, language, proficiency) VALUES (?, ?, ?)",
            [$studentId, $data['language'], $data['proficiency'] ?? 'intermediate']);
    }

    public function deleteLanguage(int $langId, int $studentId): int {
        return $this->db->delete("DELETE FROM student_languages WHERE id = ? AND student_id = ?", [$langId, $studentId]);
    }

    // Achievements
    public function getAchievements(int $studentId): array {
        return $this->db->fetchAll("SELECT * FROM student_achievements WHERE student_id = ? ORDER BY created_at DESC", [$studentId]);
    }

    public function addAchievement(int $studentId, array $data): int {
        return $this->db->insert("INSERT INTO student_achievements (student_id, title, description, date) VALUES (?, ?, ?, ?)",
            [$studentId, $data['title'], $data['description'] ?? null, $data['date'] ?? null]);
    }

    public function deleteAchievement(int $achId, int $studentId): int {
        return $this->db->delete("DELETE FROM student_achievements WHERE id = ? AND student_id = ?", [$achId, $studentId]);
    }

    // Stats
    public function getPlacedCount(): int {
        return (int)$this->db->fetchColumn("SELECT COUNT(*) FROM students WHERE is_placed = 1");
    }

    public function getTotalCount(): int {
        return (int)$this->db->fetchColumn("SELECT COUNT(*) FROM students");
    }

    public function getBranchStats(): array {
        return $this->db->fetchAll("SELECT branch, COUNT(*) as total, SUM(is_placed) as placed FROM students GROUP BY branch ORDER BY total DESC");
    }

    public function getHighestPackage(): ?float {
        return $this->db->fetchColumn("SELECT MAX(placed_package) FROM students WHERE is_placed = 1");
    }

    public function getAveragePackage(): ?float {
        return $this->db->fetchColumn("SELECT AVG(placed_package) FROM students WHERE is_placed = 1 AND placed_package > 0");
    }
}
