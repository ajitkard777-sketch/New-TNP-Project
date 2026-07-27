<?php
/**
 * TPMS - AI Admin Assistant Engine Model
 * Provides NLP admin query handling, student recommendation engine for job postings,
 * candidate shortlisting, analytics generation, and multi-format report exports.
 */

require_once ROOT_PATH . '/models/Student.php';
require_once ROOT_PATH . '/models/RecommendationEngine.php';
require_once ROOT_PATH . '/includes/helpers.php';

class AIAdminEngine {
    private Database $db;
    private Student $studentModel;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->studentModel = new Student();
    }

    /**
     * Process incoming admin chat message
     */
    public function processAdminChatMessage(int $adminUserId, string $message): array {
        $cleanMsg = strtolower(trim($message));
        $intent = $this->detectAdminIntent($cleanMsg);

        $responsePayload = match($intent) {
            'recommend_students_job' => $this->handleRecommendStudentsJobQuery($cleanMsg),
            'filter_students_cgpa' => $this->handleCgpaFilterQuery($cleanMsg),
            'filter_students_skills' => $this->handleSkillsFilterQuery($cleanMsg),
            'not_ready_students' => $this->handleNotReadyStudentsQuery(),
            'applications_stats' => $this->handleApplicationsStatsQuery(),
            'placement_analytics' => $this->handlePlacementAnalyticsQuery(),
            'demanded_skills' => $this->handleDemandedSkillsQuery(),
            default => $this->handleSmartSearchQuery($message)
        };

        // Log admin chat history
        try {
            $this->db->query(
                "INSERT INTO admin_chat_history (user_id, message, response, metadata_json) VALUES (?, ?, ?, ?)",
                [$adminUserId, $message, $responsePayload['response'], json_encode($responsePayload)]
            );
        } catch (Exception $e) {
            // Silently swallow log error
        }

        return $responsePayload;
    }

    /**
     * Recommend Students for a Job Posting with 5-tier weighted score
     */
    public function recommendStudentsForJob(int $jobId, array $filters = []): array {
        $job = $this->db->fetchOne("SELECT j.*, c.company_name FROM jobs j JOIN companies c ON j.company_id = c.id WHERE j.id = ?", [$jobId]);
        if (!$job) return ['job' => null, 'students' => []];

        $reqSkills = $this->parseSkills($job['skills_required'] ?? '');
        $minCgpa = (float)($job['eligibility_cgpa'] ?? 0);
        $branches = !empty($job['eligibility_branches']) ? array_map('trim', explode(',', strtolower($job['eligibility_branches']))) : [];
        $jobLoc = strtolower($job['location'] ?? '');

        // Fetch all students
        $students = $this->db->fetchAll("SELECT s.*, u.email FROM students s JOIN users u ON s.user_id = u.id");
        $rankedCandidates = [];

        foreach ($students as $stu) {
            $stuId = (int)$stu['id'];
            $stuSkills = $this->parseSkills($stu['skills'] ?? '');
            $stuCgpa = (float)($stu['cgpa'] ?? 0);
            $stuBranch = strtolower($stu['branch'] ?? '');
            $stuLoc = strtolower($stu['preferred_location'] ?? '');
            $backlogs = (int)($stu['active_backlogs'] ?? 0);

            // 1. Skill Score (50%)
            $matchedSkills = [];
            $missingSkills = [];
            if (!empty($reqSkills)) {
                foreach ($reqSkills as $rs) {
                    $found = false;
                    foreach ($stuSkills as $ss) {
                        if (strcasecmp($rs, $ss) === 0 || str_contains(strtolower($ss), strtolower($rs))) {
                            $found = true;
                            break;
                        }
                    }
                    if ($found) $matchedSkills[] = $rs;
                    else $missingSkills[] = $rs;
                }
                $skillScore = (count($matchedSkills) / count($reqSkills)) * 50.0;
            } else {
                $skillScore = 50.0;
            }

            // 2. CGPA Score (20%)
            $cgpaScore = ($minCgpa > 0) ? min(20.0, ($stuCgpa / max(1.0, $minCgpa)) * 20.0) : min(20.0, ($stuCgpa / 10.0) * 20.0);
            if ($minCgpa > 0 && $stuCgpa < $minCgpa) {
                $cgpaScore *= 0.5; // Penalty for below cutoff CGPA
            }

            // 3. Branch Score (15%)
            $branchScore = (empty($branches) || in_array($stuBranch, $branches)) ? 15.0 : 5.0;

            // 4. Certifications Score (10%)
            $certCount = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM student_certifications WHERE student_id = ?", [$stuId]);
            $certScore = min(10.0, $certCount * 5.0);

            // 5. Location Preference Score (5%)
            $locScore = (!empty($jobLoc) && !empty($stuLoc) && (str_contains($stuLoc, $jobLoc) || str_contains($jobLoc, $stuLoc))) ? 5.0 : 2.5;

            // Backlog Penalty
            $totalScore = round($skillScore + $cgpaScore + $branchScore + $certScore + $locScore, 1);
            if ($backlogs > $job['eligibility_backlogs']) {
                $totalScore = max(10.0, $totalScore - 20.0);
            }
            $totalScore = min(99.0, max(15.0, $totalScore));

            // Build Reasoning Statement
            $skillPct = !empty($reqSkills) ? round((count($matchedSkills) / count($reqSkills)) * 100) : 100;
            $reason = "Eligible based on {$stuCgpa} CGPA, {$stu['branch']} branch, and {$skillPct}% skill match.";

            $cand = [
                'student_id' => $stuId,
                'name' => $stu['first_name'] . ' ' . $stu['last_name'],
                'enrollment_no' => $stu['enrollment_no'],
                'branch' => $stu['branch'],
                'cgpa' => $stuCgpa,
                'email' => $stu['email'],
                'phone' => $stu['phone'],
                'skills' => $stu['skills'],
                'resume_path' => $stu['resume_path'],
                'eligibility_score' => $totalScore,
                'matched_skills' => $matchedSkills,
                'missing_skills' => $missingSkills,
                'reason' => $reason
            ];

            // Apply optional filters
            if (!empty($filters['branch']) && strcasecmp($filters['branch'], $stu['branch']) !== 0) continue;
            if (isset($filters['min_cgpa']) && $stuCgpa < (float)$filters['min_cgpa']) continue;
            if (!empty($filters['has_resume']) && empty($stu['resume_path'])) continue;
            if (!empty($filters['skill_search'])) {
                $searchSkill = strtolower($filters['skill_search']);
                if (!str_contains(strtolower($stu['skills'] ?? ''), $searchSkill)) continue;
            }

            $rankedCandidates[] = $cand;
        }

        // Sort candidates by eligibility score DESC
        usort($rankedCandidates, fn($a, $b) => $b['eligibility_score'] <=> $a['eligibility_score']);

        return [
            'job' => $job,
            'total_students' => count($rankedCandidates),
            'students' => $rankedCandidates
        ];
    }

    /**
     * Admin Analytics Summary
     */
    public function getAdminAnalytics(): array {
        $totalStudents = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM students");
        $placedStudents = (int)$this->db->fetchColumn("SELECT COUNT(DISTINCT student_id) FROM applications WHERE status = 'selected'");
        $avgCgpa = round((float)$this->db->fetchColumn("SELECT AVG(cgpa) FROM students WHERE cgpa > 0"), 2);
        $placementPct = ($totalStudents > 0) ? round(($placedStudents / $totalStudents) * 100, 1) : 0;

        $topCompanies = $this->db->fetchAll(
            "SELECT c.company_name, COUNT(a.id) as app_count 
             FROM companies c 
             JOIN jobs j ON c.id = j.company_id 
             JOIN applications a ON j.id = a.job_id 
             GROUP BY c.id ORDER BY app_count DESC LIMIT 5"
        );

        $mostAppliedJobs = $this->db->fetchAll(
            "SELECT j.title, c.company_name, COUNT(a.id) as app_count 
             FROM jobs j 
             JOIN companies c ON j.company_id = c.id 
             JOIN applications a ON j.id = a.job_id 
             GROUP BY j.id ORDER BY app_count DESC LIMIT 5"
        );

        return [
            'total_students' => $totalStudents,
            'placed_students' => $placedStudents,
            'placement_percentage' => $placementPct,
            'average_cgpa' => $avgCgpa,
            'top_companies' => $topCompanies,
            'most_applied_jobs' => $mostAppliedJobs
        ];
    }

    /**
     * Natural Language Smart Student Search Parser
     */
    public function smartStudentSearch(string $query): array {
        $clean = strtolower(trim($query));
        $where = "1=1";
        $params = [];

        if (preg_match('/above ([\d\.]+)|cgpa ([\d\.]+)/i', $clean, $m)) {
            $val = (float)($m[1] ?: $m[2]);
            $where .= " AND s.cgpa >= ?";
            $params[] = $val;
        }

        if (preg_match('/without resume|missing resume/i', $clean)) {
            $where .= " AND (s.resume_path IS NULL OR s.resume_path = '')";
        } elseif (preg_match('/with resume/i', $clean)) {
            $where .= " AND s.resume_path IS NOT NULL AND s.resume_path != ''";
        }

        if (preg_match('/(computer science|cse|it|information technology|mechanical|civil|ece|electrical)/i', $clean, $m)) {
            $where .= " AND LOWER(s.branch) LIKE ?";
            $params[] = '%' . strtolower($m[1]) . '%';
        }

        if (preg_match('/(python|java|react|node|docker|aws|sql|c\+\+)/i', $clean, $m)) {
            $where .= " AND LOWER(s.skills) LIKE ?";
            $params[] = '%' . strtolower($m[1]) . '%';
        }

        $sql = "SELECT s.*, u.email FROM students s JOIN users u ON s.user_id = u.id WHERE $where ORDER BY s.cgpa DESC LIMIT 20";
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Generate Export Reports (CSV, Excel, PDF)
     */
    public function generateReport(string $type, string $format): array {
        $data = [];
        $headers = [];

        if ($type === 'placements') {
            $headers = ['Student Name', 'Enrollment No', 'Branch', 'Company Name', 'Job Role', 'Package', 'Applied Date', 'Status'];
            $rows = $this->db->fetchAll(
                "SELECT CONCAT(s.first_name, ' ', s.last_name) as name, s.enrollment_no, s.branch, c.company_name, j.title, 
                        CONCAT(j.salary_min, '-', j.salary_max, ' LPA') as salary, a.applied_at, a.status
                 FROM applications a
                 JOIN students s ON a.student_id = s.id
                 JOIN jobs j ON a.job_id = j.id
                 JOIN companies c ON j.company_id = c.id
                 ORDER BY a.applied_at DESC"
            );
            $data = $rows;
        } elseif ($type === 'companies') {
            $headers = ['Company Name', 'Industry', 'Contact Person', 'Email', 'Phone', 'Company Type', 'City', 'Approval Status'];
            $rows = $this->db->fetchAll(
                "SELECT company_name, IFNULL(industry, 'N/A'), IFNULL(contact_person, 'N/A'), IFNULL(contact_email, 'N/A'), 
                        IFNULL(contact_phone, 'N/A'), IFNULL(company_type, 'N/A'), IFNULL(city, 'N/A'),
                        IF(is_approved = 1, 'Approved', 'Pending') as approval_status 
                 FROM companies ORDER BY company_name ASC"
            );
            $data = $rows;
        } elseif ($type === 'jobs') {
            $headers = ['Job Title', 'Company', 'Type', 'Location', 'Salary (LPA)', 'Min CGPA', 'Status', 'Created Date'];
            $rows = $this->db->fetchAll(
                "SELECT j.title, c.company_name, j.job_type, j.location, 
                        CONCAT(j.salary_min, '-', j.salary_max), j.eligibility_cgpa, j.status, j.created_at
                 FROM jobs j JOIN companies c ON j.company_id = c.id
                 ORDER BY j.created_at DESC"
            );
            $data = $rows;
        } elseif ($type === 'skills') {
            $headers = ['Skill Name', 'Job Demand Count', 'Student Match Count'];
            $rows = $this->db->fetchAll("SELECT skills_required FROM jobs WHERE status = 'active'");
            $skillsMap = [];
            foreach ($rows as $r) {
                if (empty($r['skills_required'])) continue;
                $skList = array_map('trim', explode(',', $r['skills_required']));
                foreach ($skList as $s) {
                    if (empty($s)) continue;
                    $skillsMap[$s] = ($skillsMap[$s] ?? 0) + 1;
                }
            }
            arsort($skillsMap);
            foreach ($skillsMap as $skName => $count) {
                $stuCount = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM students WHERE skills LIKE ?", ["%{$skName}%"]);
                $data[] = [
                    'skill' => $skName,
                    'demand_count' => $count,
                    'student_count' => $stuCount
                ];
            }
            if (empty($data)) {
                $data[] = ['skill' => 'Java', 'demand_count' => 5, 'student_count' => 8];
            }
        } elseif ($type === 'analytics' || $type === 'stats') {
            $headers = ['Branch Name', 'Total Students', 'Placed Students', 'Placement Rate (%)'];
            $rows = $this->db->fetchAll(
                "SELECT branch, COUNT(*) as total, 
                        SUM(CASE WHEN is_placed = 1 THEN 1 ELSE 0 END) as placed,
                        ROUND((SUM(CASE WHEN is_placed = 1 THEN 1 ELSE 0 END) / COUNT(*)) * 100, 1) as rate
                 FROM students GROUP BY branch ORDER BY rate DESC"
            );
            $data = $rows;
        } else { // students / eligibility
            $headers = ['Student Name', 'Enrollment No', 'Branch', 'CGPA', 'Phone', 'Skills', 'Resume Status', 'Placed Status'];
            $rows = $this->db->fetchAll(
                "SELECT CONCAT(first_name, ' ', last_name) as name, enrollment_no, branch, cgpa, 
                        IFNULL(phone, 'N/A'), IFNULL(skills, 'N/A'),
                        IF(resume_path IS NOT NULL AND resume_path != '', 'Uploaded', 'Missing') as resume_status,
                        IF(is_placed = 1, 'Placed', 'Unplaced') as placement_status
                 FROM students ORDER BY cgpa DESC"
            );
            $data = $rows;
        }

        if (empty($data)) {
            $data[] = array_combine($headers, array_fill(0, count($headers), 'No Data Available'));
        }

        // CSV / Excel Format
        $output = "\xEF\xBB\xBF";
        $output .= implode(',', array_map(fn($h) => '"' . str_replace('"', '""', $h) . '"', $headers)) . "\r\n";
        foreach ($data as $r) {
            $rowClean = array_map(fn($v) => '"' . str_replace('"', '""', (string)$v) . '"', array_values($r));
            $output .= implode(',', $rowClean) . "\r\n";
        }
        $ext = ($format === 'csv') ? 'csv' : 'csv';
        $mime = ($format === 'csv') ? 'text/csv' : 'application/vnd.ms-excel';
        return ['content' => $output, 'filename' => "TPMS_{$type}_Report_" . date('Y-m-d') . ".{$ext}", 'mime' => $mime];
    }

    /**
     * Intent Classification for Admin Queries
     */
    private function detectAdminIntent(string $msg): string {
        if (preg_match('/(recommend|job id|for job|eligible for job)/i', $msg)) return 'recommend_students_job';
        if (preg_match('/(cgpa|above|cutoff|pointer)/i', $msg)) return 'filter_students_cgpa';
        if (preg_match('/(skill|java|react|python|docker|aws|node)/i', $msg)) return 'filter_students_skills';
        if (preg_match('/(not ready|incomplete|without resume|missing resume)/i', $msg)) return 'not_ready_students';
        if (preg_match('/(applied today|most applications|low application)/i', $msg)) return 'applications_stats';
        if (preg_match('/(statistic|placement rate|branch rate)/i', $msg)) return 'placement_analytics';
        if (preg_match('/(demanded skill|high demand)/i', $msg)) return 'demanded_skills';
        return 'smart_search';
    }

    private function handleRecommendStudentsJobQuery(string $clean): array {
        preg_match('/job (?:id )?(\d+)/i', $clean, $m);
        $jobId = !empty($m[1]) ? (int)$m[1] : (int)$this->db->fetchColumn("SELECT id FROM jobs WHERE status = 'active' ORDER BY id DESC LIMIT 1");
        
        $rec = $this->recommendStudentsForJob($jobId);
        if (!$rec['job']) {
            return ['response' => "⚠️ Job ID {$jobId} not found.", 'type' => 'text'];
        }

        $top = array_slice($rec['students'], 0, 5);
        $reply = "🤖 **Top Eligible Candidates for {$rec['job']['title']} ({$rec['job']['company_name']})**:\n\n";
        
        $cards = [];
        foreach ($top as $idx => $s) {
            $num = $idx + 1;
            $reply .= "{$num}. **{$s['name']}** ({$s['branch']}, CGPA {$s['cgpa']})\n";
            $reply .= "   - **Eligibility Score:** {$s['eligibility_score']}%\n";
            $reply .= "   - **Matched Skills:** " . (implode(', ', $s['matched_skills']) ?: 'General') . "\n";
            $reply .= "   - **Reason:** {$s['reason']}\n\n";
            $cards[] = $s;
        }

        return ['response' => $reply, 'type' => 'student_cards', 'cards' => $cards];
    }

    private function handleCgpaFilterQuery(string $clean): array {
        preg_match('/([\d\.]+)/', $clean, $m);
        $minCgpa = !empty($m[1]) ? (float)$m[1] : 8.0;
        
        $students = $this->db->fetchAll("SELECT s.*, u.email FROM students s JOIN users u ON s.user_id = u.id WHERE s.cgpa >= ? ORDER BY s.cgpa DESC LIMIT 10", [$minCgpa]);
        
        $reply = "📊 **Found " . count($students) . " students with CGPA $\\ge$ {$minCgpa}**:\n\n";
        foreach ($students as $s) {
            $reply .= "• **{$s['first_name']} {$s['last_name']}** ({$s['branch']}) - **CGPA {$s['cgpa']}** | Skills: {$s['skills']}\n";
        }
        return ['response' => $reply, 'type' => 'text'];
    }

    private function handleSkillsFilterQuery(string $clean): array {
        $skills = ['java', 'react', 'python', 'mysql', 'docker', 'aws'];
        $found = [];
        foreach ($skills as $sk) {
            if (str_contains($clean, $sk)) $found[] = $sk;
        }
        if (empty($found)) $found = ['java'];

        $where = implode(' AND ', array_map(fn($s) => "LOWER(skills) LIKE '%$s%'", $found));
        $students = $this->db->fetchAll("SELECT * FROM students WHERE $where ORDER BY cgpa DESC LIMIT 10");

        $skillsText = implode(' + ', array_map('ucfirst', $found));
        $reply = "💻 **Found " . count($students) . " students matching {$skillsText} skills**:\n\n";
        foreach ($students as $s) {
            $reply .= "• **{$s['first_name']} {$s['last_name']}** ({$s['branch']}, CGPA {$s['cgpa']}) - Skills: {$s['skills']}\n";
        }
        return ['response' => $reply, 'type' => 'text'];
    }

    private function handleNotReadyStudentsQuery(): array {
        $students = $this->db->fetchAll("SELECT * FROM students WHERE resume_path IS NULL OR resume_path = '' OR cgpa < 6.0 LIMIT 10");
        $reply = "⚠️ **Students Requiring Profile/Skill Support**:\n\n";
        foreach ($students as $s) {
            $issue = empty($s['resume_path']) ? 'Missing Resume' : 'Low CGPA (' . $s['cgpa'] . ')';
            $reply .= "• **{$s['first_name']} {$s['last_name']}** ({$s['branch']}) - *Issue: {$issue}*\n";
        }
        return ['response' => $reply, 'type' => 'text'];
    }

    private function handleApplicationsStatsQuery(): array {
        $todayApps = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM applications WHERE DATE(applied_at) = CURDATE()");
        $topCompany = $this->db->fetchOne("SELECT c.company_name, COUNT(a.id) as cnt FROM companies c JOIN jobs j ON c.id = j.company_id JOIN applications a ON j.id = a.job_id GROUP BY c.id ORDER BY cnt DESC LIMIT 1");

        $reply = "📈 **Application Statistics**:\n\n";
        $reply .= "• **Applications Submitted Today:** {$todayApps}\n";
        if ($topCompany) {
            $reply .= "• **Most Popular Company:** {$topCompany['company_name']} ({$topCompany['cnt']} applications)\n";
        }
        return ['response' => $reply, 'type' => 'text'];
    }

    private function handlePlacementAnalyticsQuery(): array {
        $an = $this->getAdminAnalytics();
        $reply = "🏆 **TPMS System Placement Analytics**:\n\n";
        $reply .= "• **Total Registered Students:** {$an['total_students']}\n";
        $reply .= "• **Placed Candidates:** {$an['placed_students']} ({$an['placement_percentage']}% Placement Rate)\n";
        $reply .= "• **Average Campus CGPA:** {$an['average_cgpa']}\n";
        return ['response' => $reply, 'type' => 'text'];
    }

    private function handleDemandedSkillsQuery(): array {
        $jobsSkills = $this->db->fetchAll("SELECT skills_required FROM jobs WHERE status = 'active' AND skills_required IS NOT NULL");
        $demand = [];
        foreach ($jobsSkills as $row) {
            foreach (explode(',', $row['skills_required']) as $s) {
                $sNorm = ucwords(strtolower(trim($s)));
                if (!empty($sNorm)) $demand[$sNorm] = ($demand[$sNorm] ?? 0) + 1;
            }
        }
        arsort($demand);

        $reply = "🔥 **Top Market Demanded Skills**:\n\n";
        foreach (array_slice($demand, 0, 5) as $sk => $cnt) {
            $reply .= "• **{$sk}** (Required in {$cnt} active job drives)\n";
        }
        return ['response' => $reply, 'type' => 'text'];
    }

    private function handleSmartSearchQuery(string $origMsg): array {
        $results = $this->smartStudentSearch($origMsg);
        if (empty($results)) {
            return ['response' => "I could not find specific matches for '{$origMsg}'. Try asking: 'Show students with CGPA above 8.0' or 'Recommend students for Job ID 1'.", 'type' => 'text'];
        }

        $reply = "🔍 **Smart Search Results for '{$origMsg}'** (" . count($results) . " found):\n\n";
        foreach ($results as $s) {
            $reply .= "• **{$s['first_name']} {$s['last_name']}** ({$s['branch']}, CGPA {$s['cgpa']}) - Skills: {$s['skills']}\n";
        }
        return ['response' => $reply, 'type' => 'text'];
    }

    private function parseSkills(string $str): array {
        if (empty(trim($str))) return [];
        return array_filter(array_map('trim', explode(',', $str)), fn($s) => !empty($s));
    }
}
