<?php
/**
 * TPMS - AI Job Recommendation Engine Model & Service
 * Generates automated job recommendations for students based on 5 weighted criteria:
 * - Skill Match (50%)
 * - CGPA Match (20%)
 * - Branch Match (15%)
 * - Location Preference (10%)
 * - Certification & Experience (5%)
 */

class RecommendationEngine {
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Calculate Recommendation Score & Details for a given Student and Job
     */
    public function calculateRecommendation(array $student, array $job): array {
        // 1. Skill Matching
        $studentSkills = $this->parseSkills($student['skills'] ?? '');
        $jobSkills = $this->parseSkills($job['skills_required'] ?? '');

        $matchedSkills = [];
        $missingSkills = [];

        if (empty($jobSkills)) {
            $skillScore = 100;
        } else {
            foreach ($jobSkills as $jSkill) {
                $matched = false;
                foreach ($studentSkills as $sSkill) {
                    if (strcasecmp($jSkill, $sSkill) === 0 || str_contains(strtolower($sSkill), strtolower($jSkill))) {
                        $matchedSkills[] = $jSkill;
                        $matched = true;
                        break;
                    }
                }
                if (!$matched) {
                    $missingSkills[] = $jSkill;
                }
            }
            $skillScore = (count($matchedSkills) / count($jobSkills)) * 100;
        }

        // 2. CGPA Eligibility & Score
        $minCgpa = (float)($job['eligibility_cgpa'] ?? 0);
        $studentCgpa = (float)($student['cgpa'] ?? 0);
        $cgpaEligible = true;
        
        if ($minCgpa > 0) {
            if ($studentCgpa >= $minCgpa) {
                $cgpaScore = 100;
            } else {
                $cgpaScore = 0;
                $cgpaEligible = false;
            }
        } else {
            $cgpaScore = 100;
        }

        // 3. Branch Match Score & Eligibility
        $eligibleBranches = $this->parseBranches($job['eligibility_branches'] ?? '');
        $studentBranch = trim($student['branch'] ?? '');
        $branchEligible = true;

        if (empty($eligibleBranches) || in_array('all', array_map('strtolower', $eligibleBranches)) || in_array('any', array_map('strtolower', $eligibleBranches))) {
            $branchScore = 100;
        } else {
            $matchedBranch = false;
            foreach ($eligibleBranches as $eb) {
                if (strcasecmp($eb, $studentBranch) === 0 || str_contains(strtolower($studentBranch), strtolower($eb)) || str_contains(strtolower($eb), strtolower($studentBranch))) {
                    $matchedBranch = true;
                    break;
                }
            }
            if ($matchedBranch) {
                $branchScore = 100;
            } else {
                $branchScore = 0;
                $branchEligible = false;
            }
        }

        // 4. Location Score
        $workMode = strtolower($job['work_mode'] ?? 'onsite');
        $jobLocation = strtolower(trim($job['location'] ?? ''));
        $prefLocation = strtolower(trim($student['preferred_location'] ?? ''));
        $studentCity = strtolower(trim($student['city'] ?? ''));

        if ($workMode === 'remote') {
            $locationScore = 100;
        } elseif (empty($prefLocation)) {
            $locationScore = 80; // default acceptable score
        } elseif (!empty($jobLocation) && (str_contains($jobLocation, $prefLocation) || str_contains($prefLocation, $jobLocation))) {
            $locationScore = 100;
        } elseif (!empty($studentCity) && !empty($jobLocation) && str_contains($jobLocation, $studentCity)) {
            $locationScore = 85;
        } else {
            $locationScore = 30;
        }

        // 5. Certification & Experience Score
        $certificationsCount = isset($student['certifications_count']) ? (int)$student['certifications_count'] : 0;
        $experienceYears = (float)($student['experience_years'] ?? 0);
        $reqExperience = trim($job['experience_required'] ?? '');

        $certScore = 50; // base score
        if ($certificationsCount > 0) {
            $certScore += 30;
        }
        if ($experienceYears > 0 || !empty($reqExperience)) {
            $certScore += 20;
        }
        $certScore = min(100, $certScore);

        // Calculate weighted score
        // Recommendation Score = (SkillScore * 0.50) + (CGPA Score * 0.20) + (Branch Score * 0.15) + (Location Score * 0.10) + (Certification Score * 0.05)
        $rawScore = ($skillScore * 0.50) + ($cgpaScore * 0.20) + ($branchScore * 0.15) + ($locationScore * 0.10) + ($certScore * 0.05);
        $recommendationScore = round($rawScore, 2);

        // Recommendation Level
        if ($recommendationScore >= 90) {
            $level = "90-100";
            $levelLabel = "Excellent Match";
            $stars = 5;
        } elseif ($recommendationScore >= 75) {
            $level = "75-89";
            $levelLabel = "Very Good Match";
            $stars = 4;
        } elseif ($recommendationScore >= 60) {
            $level = "60-74";
            $levelLabel = "Good Match";
            $stars = 3;
        } elseif ($recommendationScore >= 40) {
            $level = "40-59";
            $levelLabel = "Fair Match";
            $stars = 2;
        } else {
            $level = "Below 40";
            $levelLabel = "Not Recommended";
            $stars = 1;
        }

        // Build Explanations List
        $reasons = [];
        if ($cgpaEligible) {
            $reasons[] = [
                'status' => 'success',
                'text' => 'Your CGPA (' . number_format($studentCgpa, 2) . ') meets the job requirement' . ($minCgpa > 0 ? ' (' . number_format($minCgpa, 2) . '+)' : '')
            ];
        } else {
            $reasons[] = [
                'status' => 'danger',
                'text' => 'Minimum CGPA required is ' . number_format($minCgpa, 2) . ' (Your CGPA: ' . number_format($studentCgpa, 2) . ')'
            ];
        }

        if (!empty($jobSkills)) {
            $skillPercentage = round($skillScore);
            $reasons[] = [
                'status' => $skillPercentage >= 50 ? 'success' : 'warning',
                'text' => $skillPercentage . '% skills matched (' . count($matchedSkills) . ' of ' . count($jobSkills) . ' skills)'
            ];
        } else {
            $reasons[] = [
                'status' => 'success',
                'text' => 'No special technical skills required for this job'
            ];
        }

        if ($branchEligible) {
            $reasons[] = [
                'status' => 'success',
                'text' => 'Eligible branch (' . ($studentBranch ?: 'General') . ')'
            ];
        } else {
            $reasons[] = [
                'status' => 'danger',
                'text' => 'Branch mismatch (' . $studentBranch . ' vs ' . implode(', ', $eligibleBranches) . ')'
            ];
        }

        if ($workMode === 'remote') {
            $reasons[] = [
                'status' => 'success',
                'text' => 'Remote work opportunity matches location preference'
            ];
        } elseif ($locationScore >= 80) {
            $reasons[] = [
                'status' => 'success',
                'text' => 'Preferred location matches (' . ($student['preferred_location'] ?: $student['city'] ?: 'Flexible') . ')'
            ];
        } else {
            $reasons[] = [
                'status' => 'warning',
                'text' => 'Job location differs from preferred location'
            ];
        }

        if ($certificationsCount > 0) {
            $reasons[] = [
                'status' => 'success',
                'text' => $certificationsCount . ' verified certification(s) on profile'
            ];
        }

        return [
            'recommendation_score' => $recommendationScore,
            'recommendation_level' => $levelLabel,
            'level_range' => $level,
            'stars' => $stars,
            'matched_skills' => $matchedSkills,
            'missing_skills' => $missingSkills,
            'cgpa_eligible' => $cgpaEligible,
            'branch_eligible' => $branchEligible,
            'reasons' => $reasons,
            'breakdown' => [
                'skill' => round($skillScore, 1),
                'cgpa' => round($cgpaScore, 1),
                'branch' => round($branchScore, 1),
                'location' => round($locationScore, 1),
                'certification' => round($certScore, 1)
            ]
        ];
    }

    /**
     * Generate & Save Recommendations for a Student into Database
     */
    public function generateForStudent(int $studentId): int {
        // Fetch full student profile
        $student = $this->db->fetchOne("SELECT s.*, (SELECT COUNT(*) FROM student_certifications sc WHERE sc.student_id = s.id) as certifications_count FROM students s WHERE s.id = ?", [$studentId]);
        if (!$student) {
            return 0;
        }

        // Fetch active jobs
        $jobs = $this->db->fetchAll("SELECT j.*, c.company_name, c.logo FROM jobs j JOIN companies c ON j.company_id = c.id WHERE j.status = 'active'");
        if (empty($jobs)) {
            return 0;
        }

        $generatedCount = 0;

        foreach ($jobs as $job) {
            $res = $this->calculateRecommendation($student, $job);

            $matchedSkillsStr = implode(',', $res['matched_skills']);
            $missingSkillsStr = implode(',', $res['missing_skills']);
            $reasonsJson = json_encode($res['reasons']);

            // Insert or Update Recommendation record
            $sql = "INSERT INTO job_recommendations (student_id, job_id, recommendation_score, matched_skills, missing_skills, recommendation_level, reasons_json, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW()) 
                    ON DUPLICATE KEY UPDATE 
                        recommendation_score = VALUES(recommendation_score),
                        matched_skills = VALUES(matched_skills),
                        missing_skills = VALUES(missing_skills),
                        recommendation_level = VALUES(recommendation_level),
                        reasons_json = VALUES(reasons_json),
                        updated_at = NOW()";

            $this->db->query($sql, [
                $studentId,
                $job['id'],
                $res['recommendation_score'],
                $matchedSkillsStr,
                $missingSkillsStr,
                $res['recommendation_level'],
                $reasonsJson
            ]);

            $generatedCount++;
        }

        return $generatedCount;
    }

    /**
     * Generate recommendations for all active students (or bulk update)
     */
    public function generateForAllStudents(): int {
        $students = $this->db->fetchAll("SELECT id FROM students");
        $count = 0;
        foreach ($students as $st) {
            $count += $this->generateForStudent((int)$st['id']);
        }
        return $count;
    }

    /**
     * Get Student Recommendations with Filtering and Pagination
     */
    public function getStudentRecommendations(int $studentId, array $filters = []): array {
        // Automatically ensure recommendations exist or are updated
        $countExisting = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM job_recommendations WHERE student_id = ?", [$studentId]);
        if ($countExisting === 0) {
            $this->generateForStudent($studentId);
        }

        $params = [$studentId];
        $where = "r.student_id = ? AND j.status = 'active'";

        if (!empty($filters['min_score'])) {
            $where .= " AND r.recommendation_score >= ?";
            $params[] = (float)$filters['min_score'];
        }

        if (!empty($filters['location'])) {
            $where .= " AND (j.location LIKE ? OR j.work_mode LIKE ?)";
            $params[] = '%' . $filters['location'] . '%';
            $params[] = '%' . $filters['location'] . '%';
        }

        if (!empty($filters['package'])) {
            $where .= " AND (j.salary_max >= ? OR j.salary_min >= ?)";
            $params[] = (float)$filters['package'];
            $params[] = (float)$filters['package'];
        }

        if (!empty($filters['company'])) {
            $where .= " AND c.company_name LIKE ?";
            $params[] = '%' . $filters['company'] . '%';
        }

        if (!empty($filters['job_type'])) {
            $where .= " AND j.job_type = ?";
            $params[] = $filters['job_type'];
        }

        if (!empty($filters['work_mode'])) {
            $where .= " AND j.work_mode = ?";
            $params[] = $filters['work_mode'];
        }

        if (!empty($filters['search'])) {
            $where .= " AND (j.title LIKE ? OR c.company_name LIKE ? OR j.skills_required LIKE ?)";
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }

        $orderBy = "r.recommendation_score DESC, j.created_at DESC";
        if (isset($filters['sort_by'])) {
            if ($filters['sort_by'] === 'salary') {
                $orderBy = "j.salary_max DESC, r.recommendation_score DESC";
            } elseif ($filters['sort_by'] === 'recent') {
                $orderBy = "j.created_at DESC, r.recommendation_score DESC";
            }
        }

        $sql = "SELECT r.*, 
                       j.title, j.description, j.job_type, j.work_mode, j.location, j.salary_min, j.salary_max, j.openings, j.skills_required, j.eligibility_cgpa, j.eligibility_branches, j.application_deadline,
                       c.company_name, c.logo,
                       (SELECT COUNT(*) FROM applications a WHERE a.job_id = j.id AND a.student_id = r.student_id) as has_applied,
                       (SELECT COUNT(*) FROM bookmarks b WHERE b.job_id = j.id AND b.student_id = r.student_id) as is_bookmarked
                FROM job_recommendations r
                JOIN jobs j ON r.job_id = j.id
                JOIN companies c ON j.company_id = c.id
                WHERE $where
                ORDER BY $orderBy";

        $recommendations = $this->db->fetchAll($sql, $params);

        // Format output
        foreach ($recommendations as &$rec) {
            $rec['reasons'] = !empty($rec['reasons_json']) ? json_decode($rec['reasons_json'], true) : [];
            $rec['matched_skills_array'] = !empty($rec['matched_skills']) ? array_map('trim', explode(',', $rec['matched_skills'])) : [];
            $rec['missing_skills_array'] = !empty($rec['missing_skills']) ? array_map('trim', explode(',', $rec['missing_skills'])) : [];
        }

        return $recommendations;
    }

    /**
     * Get Top Recommended Jobs for Student Dashboard Widget
     */
    public function getTopRecommendations(int $studentId, int $limit = 5): array {
        return $this->getStudentRecommendations($studentId, ['min_score' => 40, 'sort_by' => 'score']);
    }

    /**
     * Admin Analytics Dashboard Metrics
     */
    public function getAdminAnalytics(): array {
        // 1. Most Recommended Jobs
        $topJobs = $this->db->fetchAll("
            SELECT j.id, j.title, c.company_name, AVG(r.recommendation_score) as avg_score, COUNT(r.id) as recommendation_count
            FROM job_recommendations r
            JOIN jobs j ON r.job_id = j.id
            JOIN companies c ON j.company_id = c.id
            GROUP BY j.id
            ORDER BY avg_score DESC
            LIMIT 5
        ");

        // 2. Average Recommendation Score overall
        $avgScore = (float)$this->db->fetchColumn("SELECT AVG(recommendation_score) FROM job_recommendations");

        // 3. Demanded Skills Analysis
        $jobsSkills = $this->db->fetchAll("SELECT skills_required FROM jobs WHERE status = 'active' AND skills_required IS NOT NULL AND skills_required != ''");
        $skillFreq = [];
        foreach ($jobsSkills as $row) {
            $skills = array_map('trim', explode(',', $row['skills_required']));
            foreach ($skills as $s) {
                if (empty($s)) continue;
                $sNormalized = ucwords(strtolower($s));
                $skillFreq[$sNormalized] = ($skillFreq[$sNormalized] ?? 0) + 1;
            }
        }
        arsort($skillFreq);
        $topSkills = array_slice($skillFreq, 0, 10, true);

        // 4. Students without high matching jobs (Score < 50 for all jobs)
        $unmatchedStudents = $this->db->fetchAll("
            SELECT s.id, s.first_name, s.last_name, s.branch, s.cgpa, s.skills, MAX(r.recommendation_score) as top_score
            FROM students s
            LEFT JOIN job_recommendations r ON s.id = r.student_id
            GROUP BY s.id
            HAVING top_score IS NULL OR top_score < 50
            ORDER BY top_score ASC
            LIMIT 10
        ");

        // 5. Top Hiring Companies by Average Recommendation Score
        $topCompanies = $this->db->fetchAll("
            SELECT c.id, c.company_name, c.logo, COUNT(j.id) as active_jobs, AVG(r.recommendation_score) as avg_recommendation
            FROM companies c
            JOIN jobs j ON c.id = j.company_id
            LEFT JOIN job_recommendations r ON j.id = r.job_id
            WHERE j.status = 'active'
            GROUP BY c.id
            ORDER BY avg_recommendation DESC
            LIMIT 5
        ");

        return [
            'average_score' => round($avgScore, 1),
            'top_jobs' => $topJobs,
            'top_skills' => $topSkills,
            'unmatched_students' => $unmatchedStudents,
            'top_companies' => $topCompanies,
            'total_recommendations' => (int)$this->db->fetchColumn("SELECT COUNT(*) FROM job_recommendations")
        ];
    }

    /**
     * Future-Ready AI LLM Advice Integration Placeholder Method
     * Connects with external AI API (OpenAI, Gemini, Ollama) for enriched career suggestions
     */
    public function generateAIEvaluations(array $student, array $job): array {
        // Standard rule-based fallback evaluation
        $rec = $this->calculateRecommendation($student, $job);
        
        $insights = [
            'career_advice' => "Based on your skill profile matching {$rec['recommendation_score']}%, focusing on missing skills (" . implode(', ', $rec['missing_skills']) . ") will significantly increase your hiring probability.",
            'readiness_index' => $rec['recommendation_score'],
            'suggested_certifications' => array_map(fn($s) => "Certified " . $s . " Developer", $rec['missing_skills'])
        ];

        return $insights;
    }

    // Helper functions
    private function parseSkills(string $skillsStr): array {
        if (empty(trim($skillsStr))) return [];
        $skills = array_map('trim', explode(',', $skillsStr));
        return array_filter($skills, fn($s) => !empty($s));
    }

    private function parseBranches(string $branchStr): array {
        if (empty(trim($branchStr))) return [];
        $branches = array_map('trim', explode(',', $branchStr));
        return array_filter($branches, fn($b) => !empty($b));
    }
}
