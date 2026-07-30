<?php
/**
 * TPMS - AI Recommendation Engine Model
 *
 * Computes multi-factor match scores between students and jobs.
 * Stores results in the existing `job_recommendations` table for fast retrieval.
 *
 * Scoring weights:
 *   Skills match         40%
 *   CGPA threshold       20%
 *   Branch eligibility   15%
 *   Certifications        8%
 *   Projects              7%
 *   Experience years      5%
 *   No active backlogs    3%
 *   Preferred location    2%
 */
class Recommendation {
    private Database $db;

    // Score weights (must sum to 100)
    private const W_SKILLS   = 40;
    private const W_CGPA     = 20;
    private const W_BRANCH   = 15;
    private const W_CERTS    =  8;
    private const W_PROJECTS =  7;
    private const W_EXP      =  5;
    private const W_BACKLOGS =  3;
    private const W_LOCATION =  2;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // =========================================================================
    // PUBLIC: compute & upsert all student scores for ONE job
    // Called when: a job is created/updated, or a manual refresh is triggered
    // =========================================================================
    public function recomputeForJob(int $jobId): int {
        $job = $this->db->fetchOne("SELECT * FROM jobs WHERE id = ?", [$jobId]);
        if (!$job) return 0;

        $students = $this->db->fetchAll(
            "SELECT s.*, u.email FROM students s
             JOIN users u ON s.user_id = u.id
             WHERE u.status = 'active' AND s.is_placed = 0"
        );

        $count = 0;
        foreach ($students as $student) {
            $result = $this->computeScore($student, $job);
            $this->upsert($student['id'], $jobId, $result);
            $count++;
        }
        return $count;
    }

    // =========================================================================
    // PUBLIC: compute & upsert all job scores for ONE student
    // Called when: student updates their profile
    // =========================================================================
    public function recomputeForStudent(int $studentId): int {
        $student = $this->db->fetchOne(
            "SELECT s.*, u.email FROM students s
             JOIN users u ON s.user_id = u.id WHERE s.id = ?",
            [$studentId]
        );
        if (!$student) return 0;

        $jobs = $this->db->fetchAll(
            "SELECT * FROM jobs WHERE status = 'active'
             AND (application_deadline IS NULL OR application_deadline >= CURDATE())"
        );

        $count = 0;
        foreach ($jobs as $job) {
            $result = $this->computeScore($student, $job);
            $this->upsert($studentId, $job['id'], $result);
            $count++;
        }
        return $count;
    }

    // =========================================================================
    // QUERY: get top recommended students for a company's jobs
    // Returns array grouped by job_id → top N students each
    // =========================================================================
    public function getTopStudentsForCompany(int $companyId, int $perJob = 5): array {
        // Get company's active jobs
        $jobs = $this->db->fetchAll(
            "SELECT id, title FROM jobs WHERE company_id = ? AND status = 'active'",
            [$companyId]
        );

        $result = [];
        foreach ($jobs as $job) {
            $students = $this->db->fetchAll(
                "SELECT jr.*, s.id as student_id, s.user_id, s.first_name, s.last_name, s.branch, s.cgpa,
                        s.profile_photo, s.skills, s.passing_year, s.enrollment_no,
                        s.is_placed, s.resume_path, u.email
                 FROM job_recommendations jr
                 JOIN students s ON jr.student_id = s.id
                 JOIN users u ON s.user_id = u.id
                 WHERE jr.job_id = ? AND u.status = 'active'
                 ORDER BY jr.recommendation_score DESC
                 LIMIT ?",
                [$job['id'], $perJob]
            );

            if (!empty($students)) {
                $result[] = [
                    'job'      => $job,
                    'students' => $students,
                ];
            }
        }
        return $result;
    }

    // =========================================================================
    // QUERY: get top recommended jobs for a student (for student dashboard)
    // =========================================================================
    public function getTopJobsForStudent(int $studentId, int $limit = 6): array {
        $recs = $this->db->fetchAll(
            "SELECT jr.*, j.title, j.location, j.job_type, j.work_mode,
                    j.salary_min, j.salary_max, j.application_deadline,
                    c.company_name, c.logo, c.id as company_id,
                    (SELECT COUNT(*) FROM applications a WHERE a.job_id = j.id AND a.student_id = ?) as has_applied,
                    (SELECT COUNT(*) FROM bookmarks b WHERE b.job_id = j.id AND b.student_id = ?) as is_bookmarked
             FROM job_recommendations jr
             JOIN jobs j ON jr.job_id = j.id
             JOIN companies c ON j.company_id = c.id
             WHERE jr.student_id = ?
               AND j.status = 'active'
               AND (j.application_deadline IS NULL OR j.application_deadline >= CURDATE())
             ORDER BY jr.recommendation_score DESC
             LIMIT ?",
            [$studentId, $studentId, $studentId, $limit]
        );

        if (empty($recs)) {
            $this->recomputeForStudent($studentId);
            $recs = $this->db->fetchAll(
                "SELECT jr.*, j.title, j.location, j.job_type, j.work_mode,
                        j.salary_min, j.salary_max, j.application_deadline,
                        c.company_name, c.logo, c.id as company_id,
                        (SELECT COUNT(*) FROM applications a WHERE a.job_id = j.id AND a.student_id = ?) as has_applied,
                        (SELECT COUNT(*) FROM bookmarks b WHERE b.job_id = j.id AND b.student_id = ?) as is_bookmarked
                 FROM job_recommendations jr
                 JOIN jobs j ON jr.job_id = j.id
                 JOIN companies c ON j.company_id = c.id
                 WHERE jr.student_id = ?
                   AND j.status = 'active'
                   AND (j.application_deadline IS NULL OR j.application_deadline >= CURDATE())
                 ORDER BY jr.recommendation_score DESC
                 LIMIT ?",
                [$studentId, $studentId, $studentId, $limit]
            );
        }

        return $recs;
    }

    // =========================================================================
    // QUERY: get all job recommendations for student with filters
    // =========================================================================
    public function getAllJobRecommendationsForStudent(int $studentId, array $filters = []): array {
        // First ensure student recommendations exist
        $count = (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM job_recommendations WHERE student_id = ?",
            [$studentId]
        );
        if ($count === 0) {
            $this->recomputeForStudent($studentId);
        }

        $where = "jr.student_id = ? AND j.status = 'active' AND (j.application_deadline IS NULL OR j.application_deadline >= CURDATE()) AND c.is_approved = 1";
        $params = [$studentId];

        if (!empty($filters['search'])) {
            $s = '%' . $filters['search'] . '%';
            $where .= " AND (j.title LIKE ? OR c.company_name LIKE ? OR j.skills_required LIKE ?)";
            $params[] = $s; $params[] = $s; $params[] = $s;
        }

        if (!empty($filters['job_type'])) {
            $where .= " AND j.job_type = ?";
            $params[] = $filters['job_type'];
        }

        if (!empty($filters['min_score'])) {
            $where .= " AND jr.recommendation_score >= ?";
            $params[] = (float)$filters['min_score'];
        }

        $results = $this->db->fetchAll(
            "SELECT jr.*, j.title, j.description, j.location, j.job_type, j.work_mode,
                    j.salary_min, j.salary_max, j.openings, j.skills_required,
                    j.eligibility_cgpa, j.eligibility_branches, j.eligibility_backlogs,
                    j.experience_required, j.application_deadline, j.created_at as job_created_at,
                    c.company_name, c.logo, c.id as company_id,
                    (SELECT COUNT(*) FROM applications a WHERE a.job_id = j.id AND a.student_id = ?) as has_applied,
                    (SELECT COUNT(*) FROM bookmarks b WHERE b.job_id = j.id AND b.student_id = ?) as is_bookmarked
             FROM job_recommendations jr
             JOIN jobs j ON jr.job_id = j.id
             JOIN companies c ON j.company_id = c.id
             WHERE $where
             ORDER BY jr.recommendation_score DESC, j.created_at DESC",
            array_merge([$studentId, $studentId], $params)
        );

        // Fallback: if no recommendations in DB yet for whatever reason, compute on the fly from active jobs
        if (empty($results) && empty($filters['search']) && empty($filters['job_type'])) {
            $student = $this->db->fetchOne("SELECT * FROM students WHERE id = ?", [$studentId]);
            if ($student) {
                $jobs = $this->db->fetchAll(
                    "SELECT j.*, c.company_name, c.logo, c.id as company_id,
                            (SELECT COUNT(*) FROM applications a WHERE a.job_id = j.id AND a.student_id = ?) as has_applied,
                            (SELECT COUNT(*) FROM bookmarks b WHERE b.job_id = j.id AND b.student_id = ?) as is_bookmarked
                     FROM jobs j JOIN companies c ON j.company_id = c.id
                     WHERE j.status = 'active' AND (j.application_deadline IS NULL OR j.application_deadline >= CURDATE()) AND c.is_approved = 1",
                    [$studentId, $studentId]
                );

                foreach ($jobs as $job) {
                    $res = $this->computeScore($student, $job);
                    $this->upsert($studentId, $job['id'], $res);
                    $job['recommendation_score'] = $res['score'];
                    $job['matched_skills']       = $res['matched_skills'];
                    $job['missing_skills']       = $res['missing_skills'];
                    $job['recommendation_level'] = $res['level'];
                    $job['reasons_json']         = json_encode($res['reasons'], JSON_UNESCAPED_UNICODE);
                    $results[] = $job;
                }

                // Sort by score DESC
                usort($results, fn($a, $b) => ($b['recommendation_score'] <=> $a['recommendation_score']));
            }
        }

        return $results;
    }


    // =========================================================================
    // QUERY: get scored applicants for a single job (for sorting by AI score)
    // =========================================================================
    public function getScoredApplicantsForJob(int $jobId): array {
        return $this->db->fetchAll(
            "SELECT a.*, s.first_name, s.last_name, s.branch, s.cgpa,
                    s.profile_photo, s.skills, s.user_id, u.email,
                    jr.recommendation_score, jr.matched_skills,
                    jr.missing_skills, jr.recommendation_level, jr.reasons_json
             FROM applications a
             JOIN students s ON a.student_id = s.id
             JOIN users u ON s.user_id = u.id
             LEFT JOIN job_recommendations jr ON jr.student_id = a.student_id AND jr.job_id = a.job_id
             WHERE a.job_id = ?
             ORDER BY jr.recommendation_score DESC, a.applied_at DESC",
            [$jobId]
        );
    }

    // =========================================================================
    // QUERY: quick summary stats for company dashboard widget
    // =========================================================================
    public function getCompanyRecommendationStats(int $companyId): array {
        $stats = $this->db->fetchOne(
            "SELECT
                COUNT(DISTINCT jr.student_id) as total_candidates,
                SUM(CASE WHEN jr.recommendation_score >= 75 THEN 1 ELSE 0 END) as excellent_matches,
                SUM(CASE WHEN jr.recommendation_score >= 55 AND jr.recommendation_score < 75 THEN 1 ELSE 0 END) as good_matches,
                AVG(jr.recommendation_score) as avg_score
             FROM job_recommendations jr
             JOIN jobs j ON jr.job_id = j.id
             WHERE j.company_id = ?",
            [$companyId]
        );
        return $stats ?: ['total_candidates' => 0, 'excellent_matches' => 0, 'good_matches' => 0, 'avg_score' => 0];
    }

    // =========================================================================
    // CORE: compute multi-factor score between one student and one job
    // Returns array: ['score', 'matched_skills', 'missing_skills', 'level', 'reasons']
    // =========================================================================
    public function computeScore(array $student, array $job): array {
        $score   = 0.0;
        $reasons = [];

        // ── 1. Skills match (40%) ─────────────────────────────────────────────
        $studentSkills = $this->parseSkills($student['skills'] ?? '');
        $jobSkills     = $this->parseSkills($job['skills_required'] ?? '');
        $matchedSkills = [];
        $missingSkills = [];

        if (!empty($jobSkills)) {
            foreach ($jobSkills as $js) {
                $found = false;
                foreach ($studentSkills as $ss) {
                    if ($this->skillsMatch($ss, $js)) { $found = true; break; }
                }
                if ($found) $matchedSkills[] = $js;
                else        $missingSkills[] = $js;
            }
            $skillPct = count($jobSkills) > 0 ? (count($matchedSkills) / count($jobSkills)) : 0;
            $skillScore = round($skillPct * self::W_SKILLS, 2);
            $score += $skillScore;
            $reasons[] = [
                'status' => $skillPct >= 0.6 ? 'success' : ($skillPct >= 0.3 ? 'warning' : 'danger'),
                'text'   => round($skillPct * 100) . '% skills matched (' . count($matchedSkills) . ' of ' . count($jobSkills) . ' required skills)',
            ];
        } else {
            // No required skills — give full skill score
            $score += self::W_SKILLS;
            $reasons[] = ['status' => 'info', 'text' => 'No specific skills required for this position'];
        }

        // Also consider student skills not in job — extra skills add small bonus
        if (!empty($studentSkills)) {
            $extraSkills = [];
            foreach ($studentSkills as $ss) {
                $inJob = false;
                foreach ($jobSkills as $js) { if ($this->skillsMatch($ss, $js)) { $inJob = true; break; } }
                if (!$inJob) $extraSkills[] = $ss;
            }
            if (count($extraSkills) >= 3) {
                $bonus = min(5, count($extraSkills) * 0.5);
                $score += $bonus;
                $reasons[] = ['status' => 'info', 'text' => count($extraSkills) . ' additional skills beyond requirements'];
            }
        }

        // ── 2. CGPA threshold (20%) ───────────────────────────────────────────
        $studentCgpa = (float)($student['cgpa'] ?? 0);
        $reqCgpa     = (float)($job['eligibility_cgpa'] ?? 0);
        if ($reqCgpa > 0) {
            if ($studentCgpa >= $reqCgpa) {
                // Bonus for exceeding CGPA
                $cgpaBonus = min(1.0, ($studentCgpa - $reqCgpa) / 2.0);
                $cgpaScore = self::W_CGPA * (0.8 + 0.2 * $cgpaBonus);
                $score += $cgpaScore;
                $reasons[] = ['status' => 'success', 'text' => "CGPA {$studentCgpa} meets requirement ({$reqCgpa}+)"];
            } else {
                // Partial credit if within 0.5 of threshold
                $diff = $reqCgpa - $studentCgpa;
                if ($diff <= 0.5) {
                    $score += self::W_CGPA * 0.5;
                    $reasons[] = ['status' => 'warning', 'text' => "CGPA {$studentCgpa} slightly below requirement ({$reqCgpa}+)"];
                } else {
                    $reasons[] = ['status' => 'danger', 'text' => "CGPA {$studentCgpa} does not meet requirement ({$reqCgpa}+)"];
                }
            }
        } else {
            $score += self::W_CGPA * 0.7; // neutral — no CGPA requirement
            $reasons[] = ['status' => 'info', 'text' => 'No minimum CGPA required'];
        }

        // ── 3. Branch eligibility (15%) ───────────────────────────────────────
        $eligibleBranches = $this->parseSkills($job['eligibility_branches'] ?? '');
        $studentBranch    = strtolower(trim($student['branch'] ?? ''));
        if (!empty($eligibleBranches)) {
            $branchMatched = false;
            foreach ($eligibleBranches as $b) {
                if ($this->branchMatch($studentBranch, strtolower($b))) {
                    $branchMatched = true;
                    break;
                }
            }
            if ($branchMatched) {
                $score += self::W_BRANCH;
                $reasons[] = ['status' => 'success', 'text' => 'Branch (' . ($student['branch'] ?? '') . ') is eligible'];
            } else {
                $reasons[] = ['status' => 'danger', 'text' => 'Branch (' . ($student['branch'] ?? '') . ') not in eligible branches'];
            }
        } else {
            $score += self::W_BRANCH; // open to all branches
            $reasons[] = ['status' => 'info', 'text' => 'Open to all branches'];
        }

        // ── 4. Certifications (8%) ────────────────────────────────────────────
        $certCount = (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM student_certifications WHERE student_id = ?",
            [$student['id']]
        );
        if ($certCount >= 3)       { $score += self::W_CERTS; $reasons[] = ['status' => 'success', 'text' => "{$certCount} certifications strengthen your candidacy"]; }
        elseif ($certCount >= 1)   { $score += self::W_CERTS * 0.5; $reasons[] = ['status' => 'warning', 'text' => "{$certCount} certification(s) — more certifications improve match score"]; }
        else                       { $reasons[] = ['status' => 'info', 'text' => 'Add certifications to improve your match score']; }

        // ── 5. Projects (7%) ──────────────────────────────────────────────────
        $projectCount = (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM student_projects WHERE student_id = ?",
            [$student['id']]
        );
        if ($projectCount >= 3)    { $score += self::W_PROJECTS; $reasons[] = ['status' => 'success', 'text' => "{$projectCount} projects demonstrate hands-on experience"]; }
        elseif ($projectCount >= 1){ $score += self::W_PROJECTS * 0.5; $reasons[] = ['status' => 'warning', 'text' => "{$projectCount} project(s) listed — more projects improve match"]; }

        // ── 6. Experience years (5%) ──────────────────────────────────────────
        $expYears  = (float)($student['experience_years'] ?? 0);
        $reqExpRaw = strtolower(trim($job['experience_required'] ?? ''));
        $reqExpYrs = (float)filter_var($reqExpRaw, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        if ($reqExpYrs > 0) {
            if ($expYears >= $reqExpYrs) {
                $score += self::W_EXP;
                $reasons[] = ['status' => 'success', 'text' => "{$expYears} yr(s) experience meets requirement ({$reqExpYrs}+ yrs)"];
            } else {
                $score += self::W_EXP * 0.3;
                $reasons[] = ['status' => 'warning', 'text' => "Experience ({$expYears} yr) below requirement ({$reqExpYrs} yrs)"];
            }
        } else {
            // Bonus for having experience even when not required
            if ($expYears > 0) { $score += self::W_EXP; $reasons[] = ['status' => 'success', 'text' => "{$expYears} yr(s) experience is a plus for this role"]; }
            else { $score += self::W_EXP * 0.6; } // fresher — neutral
        }

        // ── 7. Active backlogs (3%) ───────────────────────────────────────────
        $activeBacklogs = (int)($student['active_backlogs'] ?? 0);
        $maxBacklogs    = (int)($job['eligibility_backlogs'] ?? 0);
        if ($activeBacklogs === 0) {
            $score += self::W_BACKLOGS;
            $reasons[] = ['status' => 'success', 'text' => 'No active backlogs'];
        } elseif ($maxBacklogs > 0 && $activeBacklogs <= $maxBacklogs) {
            $score += self::W_BACKLOGS * 0.5;
            $reasons[] = ['status' => 'warning', 'text' => "{$activeBacklogs} active backlog(s) — within allowed limit"];
        } else {
            $reasons[] = ['status' => 'danger', 'text' => "{$activeBacklogs} active backlog(s) exceed job requirement"];
        }

        // ── 8. Location preference (2%) ───────────────────────────────────────
        $prefLoc  = strtolower(trim($student['preferred_location'] ?? ''));
        $jobLoc   = strtolower(trim($job['location'] ?? ''));
        $workMode = strtolower($job['work_mode'] ?? '');
        if ($workMode === 'remote') {
            $score += self::W_LOCATION;
            $reasons[] = ['status' => 'success', 'text' => 'Remote role — no relocation required'];
        } elseif ($prefLoc && $jobLoc && (str_contains($jobLoc, $prefLoc) || str_contains($prefLoc, $jobLoc))) {
            $score += self::W_LOCATION;
            $reasons[] = ['status' => 'success', 'text' => 'Preferred location matches job location'];
        }

        // ── Final clamping & level assignment ────────────────────────────────
        $finalScore = (float)min(100, max(0, round($score, 2)));
        $level = match(true) {
            $finalScore >= 75 => 'Excellent Match',
            $finalScore >= 55 => 'Good Match',
            $finalScore >= 35 => 'Fair Match',
            default           => 'Low Match',
        };

        return [
            'score'          => $finalScore,
            'matched_skills' => implode(',', $matchedSkills),
            'missing_skills' => implode(',', $missingSkills),
            'level'          => $level,
            'reasons'        => $reasons,
        ];
    }

    // =========================================================================
    // PRIVATE: upsert a recommendation row
    // =========================================================================
    private function upsert(int $studentId, int $jobId, array $result): void {
        $reasonsJson = json_encode($result['reasons'], JSON_UNESCAPED_UNICODE);
        $exists = $this->db->fetchColumn(
            "SELECT id FROM job_recommendations WHERE student_id = ? AND job_id = ?",
            [$studentId, $jobId]
        );
        if ($exists) {
            $this->db->update(
                "UPDATE job_recommendations
                 SET recommendation_score = ?, matched_skills = ?, missing_skills = ?,
                     recommendation_level = ?, reasons_json = ?, updated_at = NOW()
                 WHERE student_id = ? AND job_id = ?",
                [$result['score'], $result['matched_skills'], $result['missing_skills'],
                 $result['level'], $reasonsJson, $studentId, $jobId]
            );
        } else {
            $this->db->insert(
                "INSERT INTO job_recommendations
                 (student_id, job_id, recommendation_score, matched_skills, missing_skills,
                  recommendation_level, reasons_json)
                 VALUES (?, ?, ?, ?, ?, ?, ?)",
                [$studentId, $jobId, $result['score'], $result['matched_skills'],
                 $result['missing_skills'], $result['level'], $reasonsJson]
            );
        }
    }

    // =========================================================================
    // PRIVATE: helpers
    // =========================================================================
    private function parseSkills(string $raw): array {
        if (empty(trim($raw))) return [];
        $parts = preg_split('/[,;|\/]+/', $raw);
        return array_filter(array_map('trim', $parts));
    }

    private function skillsMatch(string $a, string $b): bool {
        $a = strtolower($a); $b = strtolower($b);
        return $a === $b || str_contains($a, $b) || str_contains($b, $a);
    }

    private function branchMatch(string $studentBranch, string $eligibleBranch): bool {
        // Abbreviation expansions
        $map = [
            'cse' => 'computer science', 'cs'   => 'computer science',
            'it'  => 'information technology', 'ece' => 'electronics',
            'eee' => 'electrical', 'mech' => 'mechanical', 'civil' => 'civil',
            'entc'=> 'electronics', 'extc' => 'electronics',
        ];
        $sb = $map[$studentBranch]   ?? $studentBranch;
        $eb = $map[$eligibleBranch]  ?? $eligibleBranch;
        return str_contains($sb, $eb) || str_contains($eb, $sb) ||
               str_contains($studentBranch, $eligibleBranch) || str_contains($eligibleBranch, $studentBranch);
    }
}
