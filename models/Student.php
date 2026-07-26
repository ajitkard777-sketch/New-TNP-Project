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
        $startDate = !empty($data['start_date']) ? $data['start_date'] : null;
        $endDate = !empty($data['end_date']) ? $data['end_date'] : null;
        return $this->db->insert("INSERT INTO student_projects (student_id, title, description, technologies, project_url, github_url, start_date, end_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [$studentId, $data['title'], $data['description'] ?? null, $data['technologies'] ?? null, $data['project_url'] ?? null, $data['github_url'] ?? null, $startDate, $endDate]);
    }

    public function deleteProject(int $projectId, int $studentId): int {
        return $this->db->delete("DELETE FROM student_projects WHERE id = ? AND student_id = ?", [$projectId, $studentId]);
    }

    // Certifications
    public function getCertifications(int $studentId): array {
        return $this->db->fetchAll("SELECT * FROM student_certifications WHERE student_id = ? ORDER BY created_at DESC", [$studentId]);
    }

    public function addCertification(int $studentId, array $data): int {
        $issueDate = !empty($data['issue_date']) ? $data['issue_date'] : null;
        $expiryDate = !empty($data['expiry_date']) ? $data['expiry_date'] : null;
        return $this->db->insert("INSERT INTO student_certifications (student_id, title, issuing_org, issue_date, expiry_date, credential_id, credential_url) VALUES (?, ?, ?, ?, ?, ?, ?)",
            [$studentId, $data['title'], $data['issuing_org'] ?? null, $issueDate, $expiryDate, $data['credential_id'] ?? null, $data['credential_url'] ?? null]);
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
    public function getAchievements(int $studentId, string $search = '', string $category = ''): array {
        $params = [$studentId];
        $sql = "SELECT * FROM student_achievements WHERE student_id = ?";
        if ($search) {
            $sql .= " AND (title LIKE ? OR description LIKE ? OR organizer LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        if ($category) {
            $sql .= " AND category = ?";
            $params[] = $category;
        }
        $sql .= " ORDER BY COALESCE(achievement_date, created_at) DESC";
        return $this->db->fetchAll($sql, $params);
    }

    public function addAchievement(int $studentId, array $data): int {
        $rawDate = !empty($data['achievement_date']) ? $data['achievement_date'] : (!empty($data['date']) ? $data['date'] : null);
        return $this->db->insert(
            "INSERT INTO student_achievements (student_id, title, category, description, achievement_date, organizer, position_rank, certificate_file, achievement_image, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')",
            [
                $studentId,
                $data['title'],
                $data['category'] ?? 'Others',
                $data['description'] ?? null,
                $rawDate,
                $data['organizer'] ?? null,
                $data['position_rank'] ?? null,
                $data['certificate_file'] ?? null,
                $data['achievement_image'] ?? null
            ]
        );
    }

    public function updateAchievement(int $achId, int $studentId, array $data): int {
        $fields = [];
        $params = [];
        foreach ($data as $key => $val) {
            $fields[] = "`{$key}` = ?";
            $params[] = $val;
        }
        $params[] = $achId;
        $params[] = $studentId;
        return $this->db->update("UPDATE student_achievements SET " . implode(', ', $fields) . " WHERE id = ? AND student_id = ?", $params);
    }

    public function deleteAchievement(int $achId, int $studentId): int {
        $ach = $this->db->fetchOne("SELECT certificate_file, achievement_image FROM student_achievements WHERE id = ? AND student_id = ?", [$achId, $studentId]);
        if ($ach) {
            if (!empty($ach['certificate_file']) && file_exists(ROOT_PATH . '/uploads/achievements/' . $ach['certificate_file'])) {
                @unlink(ROOT_PATH . '/uploads/achievements/' . $ach['certificate_file']);
            }
            if (!empty($ach['achievement_image']) && file_exists(ROOT_PATH . '/uploads/achievements/' . $ach['achievement_image'])) {
                @unlink(ROOT_PATH . '/uploads/achievements/' . $ach['achievement_image']);
            }
        }
        return $this->db->delete("DELETE FROM student_achievements WHERE id = ? AND student_id = ?", [$achId, $studentId]);
    }

    // Certificates
    public function getCertificates(int $studentId, string $search = ''): array {
        $params = [$studentId];
        $sql = "SELECT * FROM student_certificates WHERE student_id = ?";
        if ($search) {
            $sql .= " AND (name LIKE ? OR issuing_organization LIKE ? OR skills_covered LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        $sql .= " ORDER BY created_at DESC";
        return $this->db->fetchAll($sql, $params);
    }

    public function getCertificateById(int $certId, int $studentId): ?array {
        return $this->db->fetchOne("SELECT * FROM student_certificates WHERE id = ? AND student_id = ?", [$certId, $studentId]);
    }

    public function addCertificate(int $studentId, array $data): int {
        return $this->db->insert(
            "INSERT INTO student_certificates (student_id, name, issuing_organization, issue_date, expiry_date, credential_id, credential_url, certificate_file, skills_covered, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')",
            [
                $studentId,
                $data['name'],
                $data['issuing_organization'] ?? null,
                !empty($data['issue_date']) ? $data['issue_date'] : null,
                !empty($data['expiry_date']) ? $data['expiry_date'] : null,
                $data['credential_id'] ?? null,
                $data['credential_url'] ?? null,
                $data['certificate_file'],
                $data['skills_covered'] ?? null
            ]
        );
    }

    public function deleteCertificate(int $certId, int $studentId): int {
        $cert = $this->getCertificateById($certId, $studentId);
        if ($cert && !empty($cert['certificate_file'])) {
            $filePath = ROOT_PATH . '/uploads/certificates/' . $cert['certificate_file'];
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        }
        return $this->db->delete("DELETE FROM student_certificates WHERE id = ? AND student_id = ?", [$certId, $studentId]);
    }

    // Placement Calendar Aggregated Events
    public function getPlacementCalendarEvents(string $startDate = '', string $endDate = '', ?int $studentId = null): array {
        $events = [];

        // 1. Placement Calendar Table Custom Events
        $sql1 = "SELECT e.*, c.company_name, c.logo as company_logo FROM placement_calendar_events e LEFT JOIN companies c ON e.company_id = c.id WHERE 1=1";
        $params1 = [];
        if ($startDate) { $sql1 .= " AND e.event_date >= ?"; $params1[] = $startDate; }
        if ($endDate) { $sql1 .= " AND e.event_date <= ?"; $params1[] = $endDate; }
        $customEvents = $this->db->fetchAll($sql1, $params1);

        foreach ($customEvents as $ce) {
            $colorMap = [
                'interview' => '#2563eb',
                'drive'     => '#d97706',
                'mock_test' => '#f97316',
                'workshop'  => '#059669',
                'deadline'  => '#7c3aed',
                'training'  => '#059669',
                'activity'  => '#6366f1',
                'other'     => '#64748b'
            ];
            $color = $ce['color'] ?: ($colorMap[$ce['event_type']] ?? '#2563eb');
            $events[] = [
                'id' => 'evt_' . $ce['id'],
                'db_id' => $ce['id'],
                'source' => 'custom',
                'event_type' => $ce['event_type'],
                'title' => $ce['title'],
                'description' => $ce['description'] ?? '',
                'date' => $ce['event_date'],
                'start_time' => $ce['start_time'] ? date('h:i A', strtotime($ce['start_time'])) : 'All Day',
                'end_time' => $ce['end_time'] ? date('h:i A', strtotime($ce['end_time'])) : '',
                'venue' => $ce['venue'] ?? 'Online / Campus',
                'organizer' => $ce['organizer'] ?? 'T&P Cell',
                'company_name' => $ce['company_name'] ?? '',
                'company_logo' => $ce['company_logo'] ?? '',
                'registration_link' => $ce['registration_link'] ?? '',
                'color' => $color
            ];
        }

        // 2. Scheduled Interviews
        $sql2 = "SELECT i.*, j.title as job_title, c.company_name, c.logo as company_logo FROM interviews i JOIN jobs j ON i.job_id = j.id JOIN companies c ON i.company_id = c.id WHERE i.status != 'cancelled'";
        $params2 = [];
        if ($studentId) { $sql2 .= " AND i.student_id = ?"; $params2[] = $studentId; }
        if ($startDate) { $sql2 .= " AND i.interview_date >= ?"; $params2[] = $startDate; }
        if ($endDate) { $sql2 .= " AND i.interview_date <= ?"; $params2[] = $endDate; }
        $interviews = $this->db->fetchAll($sql2, $params2);

        foreach ($interviews as $iv) {
            $venueOrLink = $iv['mode'] === 'online'
                ? (!empty($iv['meeting_link']) ? $iv['meeting_link'] : 'Online Meeting Link')
                : (!empty($iv['venue']) ? $iv['venue'] : 'Campus Venue');

            $events[] = [
                'id' => 'iv_' . $iv['id'],
                'db_id' => $iv['id'],
                'source' => 'interview',
                'event_type' => 'interview',
                'title' => 'Interview: ' . $iv['company_name'] . ' - ' . $iv['job_title'],
                'description' => 'Company: ' . $iv['company_name'] . ' | Role: ' . $iv['job_title'] . ' | Date: ' . formatDate($iv['interview_date']) . ' | Time: ' . date('h:i A', strtotime($iv['interview_time'])) . ' | Mode: ' . ucfirst($iv['mode']) . ' (' . ($iv['round'] ?? 'Round') . ') | Status: ' . ucfirst($iv['status']),
                'date' => $iv['interview_date'],
                'start_time' => date('h:i A', strtotime($iv['interview_time'])),
                'end_time' => '',
                'venue' => $venueOrLink,
                'organizer' => $iv['company_name'],
                'company_name' => $iv['company_name'],
                'company_logo' => $iv['company_logo'],
                'registration_link' => url('/student/interviews'),
                'color' => '#2563eb'
            ];
        }

        // 3. Job Deadlines (Placement Drives & Registration Deadlines)
        $sql3 = "SELECT j.*, c.company_name, c.logo as company_logo FROM jobs j JOIN companies c ON j.company_id = c.id WHERE j.status = 'active' AND j.application_deadline IS NOT NULL";
        $params3 = [];
        if ($startDate) { $sql3 .= " AND j.application_deadline >= ?"; $params3[] = $startDate; }
        if ($endDate) { $sql3 .= " AND j.application_deadline <= ?"; $params3[] = $endDate; }
        $jobs = $this->db->fetchAll($sql3, $params3);

        foreach ($jobs as $jb) {
            $events[] = [
                'id' => 'job_' . $jb['id'],
                'db_id' => $jb['id'],
                'source' => 'job_deadline',
                'event_type' => 'deadline',
                'title' => 'Deadline: ' . $jb['company_name'] . ' - ' . $jb['title'],
                'description' => 'Application deadline for ' . $jb['title'] . '. Openings: ' . $jb['openings'],
                'date' => $jb['application_deadline'],
                'start_time' => '11:59 PM',
                'end_time' => '',
                'venue' => 'Online Portal',
                'organizer' => $jb['company_name'],
                'company_name' => $jb['company_name'],
                'company_logo' => $jb['company_logo'],
                'registration_link' => url('/student/jobs'),
                'color' => '#7c3aed'
            ];

            // Drive event
            $events[] = [
                'id' => 'drive_' . $jb['id'],
                'db_id' => $jb['id'],
                'source' => 'drive',
                'event_type' => 'drive',
                'title' => 'Placement Drive: ' . $jb['company_name'],
                'description' => $jb['title'] . ' recruitment drive open for registration.',
                'date' => date('Y-m-d', strtotime($jb['created_at'])),
                'start_time' => '09:00 AM',
                'end_time' => '',
                'venue' => $jb['location'] ?: 'Campus',
                'organizer' => $jb['company_name'],
                'company_name' => $jb['company_name'],
                'company_logo' => $jb['company_logo'],
                'registration_link' => url('/student/jobs'),
                'color' => '#d97706'
            ];
        }

        // 4. Training Programs
        $sql4 = "SELECT * FROM trainings WHERE status != 'cancelled'";
        $params4 = [];
        if ($startDate) { $sql4 .= " AND start_date >= ?"; $params4[] = $startDate; }
        if ($endDate) { $sql4 .= " AND start_date <= ?"; $params4[] = $endDate; }
        $trainings = $this->db->fetchAll($sql4, $params4);

        foreach ($trainings as $tr) {
            $events[] = [
                'id' => 'tr_' . $tr['id'],
                'db_id' => $tr['id'],
                'source' => 'training',
                'event_type' => 'training',
                'title' => 'Training: ' . $tr['title'],
                'description' => ($tr['description'] ?? '') . ' (Trainer: ' . ($tr['trainer_name'] ?? 'T&P Expert') . ')',
                'date' => $tr['start_date'],
                'start_time' => $tr['start_time'] ? date('h:i A', strtotime($tr['start_time'])) : '09:00 AM',
                'end_time' => $tr['end_time'] ? date('h:i A', strtotime($tr['end_time'])) : '',
                'venue' => $tr['venue'] ?? 'Seminar Hall',
                'organizer' => $tr['trainer_name'] ?? 'T&P Training Cell',
                'company_name' => '',
                'company_logo' => '',
                'registration_link' => url('/student/trainings'),
                'color' => '#059669'
            ];
        }

        return $events;
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

