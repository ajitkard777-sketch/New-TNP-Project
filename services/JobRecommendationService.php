<?php
/**
 * TPMS - AI-Powered Job Recommendation Service (Intelligent Rule-Based Engine)
 *
 * Algorithm Weights:
 *  - Skills Match   : 50%
 *  - Branch Match   : 20%
 *  - CGPA Match     : 20%
 *  - Location Match : 10%
 * Total = 100%
 */

class JobRecommendationService {
    private static ?JobRecommendationService $instance = null;
    private Database $db;

    private function __construct() {
        $this->db = Database::getInstance();
    }

    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Parse comma/space separated skill string into array of normalized tokens
     */
    private function parseSkills(?string $skillsStr): array {
        if (empty($skillsStr)) return [];
        $raw = preg_split('/[,;\/\n\r]+/', strtolower($skillsStr));
        $cleaned = [];
        foreach ($raw as $s) {
            $val = trim(preg_replace('/[^a-z0-9\+#\.]/', ' ', $s));
            if (!empty($val)) {
                $cleaned[] = $val;
            }
        }
        return array_unique($cleaned);
    }

    /**
     * Map branch strings to normalized branch category groups for flexible matching
     */
    public function normalizeBranchGroup(?string $branch): string {
        if (empty($branch)) return 'other';
        $b = strtolower(trim($branch));
        
        if (preg_match('/(cs|cse|comp|computer|computer science|computer engineering)/i', $b)) {
            return 'cs';
        }
        if (preg_match('/(it|information technology)/i', $b)) {
            return 'it';
        }
        if (preg_match('/(aids|ai & ds|ai\/ds|aiml|ai & ml|artificial intelligence|data science)/i', $b)) {
            return 'aids';
        }
        if (preg_match('/(entc|ece|electronics|telecom|extc)/i', $b)) {
            return 'entc';
        }
        if (preg_match('/(ee|electrical)/i', $b)) {
            return 'ee';
        }
        if (preg_match('/(mech|mechanical)/i', $b)) {
            return 'mech';
        }
        if (preg_match('/(civil)/i', $b)) {
            return 'civil';
        }
        return $b;
    }

    /**
     * Evaluate mandatory eligibility criteria independently from recommendation score.
     */
    public function checkEligibility(array $student, array $job): array {
        $reasons = [];

        // 1. Minimum CGPA Check
        $minCgpa = (float)($job['eligibility_cgpa'] ?? 0);
        $studentCgpa = isset($student['cgpa']) && $student['cgpa'] !== '' ? (float)$student['cgpa'] : null;

        if ($minCgpa > 0) {
            if ($studentCgpa === null || $studentCgpa <= 0) {
                $reasons[] = "CGPA required: " . number_format($minCgpa, 2) . " | Your CGPA: Not updated in profile";
            } elseif ($studentCgpa < $minCgpa) {
                $reasons[] = "CGPA required: " . number_format($minCgpa, 2) . " | Your CGPA: " . number_format($studentCgpa, 2);
            }
        }

        // 2. Branch Eligibility Check
        $studentBranch = trim($student['branch'] ?? '');
        $eligibleBranchesStr = trim($job['eligibility_branches'] ?? '');

        if (!empty($eligibleBranchesStr) && strtolower($eligibleBranchesStr) !== 'all') {
            $allowedBranches = array_map('trim', explode(',', $eligibleBranchesStr));
            $studentBranchGroup = $this->normalizeBranchGroup($studentBranch);
            $branchMatched = false;

            foreach ($allowedBranches as $ab) {
                $abGroup = $this->normalizeBranchGroup($ab);
                if (strtolower($studentBranch) === strtolower($ab) || ($studentBranchGroup !== 'other' && $studentBranchGroup === $abGroup)) {
                    $branchMatched = true;
                    break;
                }
            }

            if (!$branchMatched) {
                $reasons[] = "Your branch (" . ($studentBranch ?: 'Not specified') . ") is not eligible. Allowed: " . $eligibleBranchesStr;
            }
        }

        // 3. Passing Year Check (if specified on job)
        if (!empty($job['eligibility_passing_year'])) {
            $requiredYear = (int)$job['eligibility_passing_year'];
            $studentYear = (int)($student['passing_year'] ?? 0);
            if ($studentYear > 0 && $studentYear !== $requiredYear) {
                $reasons[] = "Passing year required: " . $requiredYear . " | Your passing year: " . $studentYear;
            }
        }

        // 4. Active Backlogs Check
        if (isset($job['eligibility_backlogs']) && $job['eligibility_backlogs'] !== null) {
            $maxBacklogs = (int)$job['eligibility_backlogs'];
            $studentBacklogs = (int)($student['active_backlogs'] ?? 0);
            if ($studentBacklogs > $maxBacklogs) {
                $reasons[] = "Max active backlogs allowed: " . $maxBacklogs . " | Your active backlogs: " . $studentBacklogs;
            }
        }

        // 5. Job Status & Application Deadline
        if (isset($job['status']) && $job['status'] !== 'active') {
            $reasons[] = "This job posting is currently closed or inactive";
        }

        if (!empty($job['application_deadline'])) {
            $deadlineTime = strtotime($job['application_deadline']);
            $todayTime = strtotime(date('Y-m-d'));
            if ($deadlineTime < $todayTime) {
                $reasons[] = "Application deadline expired on " . date('d M Y', $deadlineTime);
            }
        }

        // 6. Company Verification Status
        if (isset($job['is_approved']) && (int)$job['is_approved'] === 0) {
            $reasons[] = "Company profile is pending administrator verification";
        }

        $isEligible = empty($reasons);

        return [
            'is_eligible' => $isEligible,
            'reasons'     => $reasons,
            'reason_text' => $isEligible ? 'Eligible' : 'Not Eligible because: ' . implode('; ', $reasons)
        ];
    }

    /**
     * Calculate recommendation score and generate explanation for a student-job pair
     */
    public function calculateMatch(array $student, array $job): array {
        // ── 1. Skills Match (50% max) ──────────────────────────────────────
        $studentSkills = $this->parseSkills($student['skills'] ?? '');
        $jobSkills     = $this->parseSkills($job['skills_required'] ?? '');

        $skillsScore = 0.0;
        $matchedSkills = [];
        $missingSkills = [];

        if (empty($jobSkills)) {
            // If job specifies no specific required skills, default to full skills score
            $skillsScore = 50.0;
        } else {
            foreach ($jobSkills as $jSkill) {
                $found = false;
                foreach ($studentSkills as $sSkill) {
                    if (str_contains($sSkill, $jSkill) || str_contains($jSkill, $sSkill)) {
                        $found = true;
                        break;
                    }
                }
                if ($found) {
                    $matchedSkills[] = ucfirst($jSkill);
                } else {
                    $missingSkills[] = ucfirst($jSkill);
                }
            }

            if (count($jobSkills) > 0) {
                $matchRatio = count($matchedSkills) / count($jobSkills);
                $skillsScore = round($matchRatio * 50.0, 1);
            }
        }

        // ── 2. Branch Match (20% max) ──────────────────────────────────────
        $branchScore = 0.0;
        $branchMatched = false;
        $studentBranch = trim($student['branch'] ?? '');
        $eligibleBranchesStr = trim($job['eligibility_branches'] ?? '');

        if (empty($eligibleBranchesStr) || strtolower($eligibleBranchesStr) === 'all') {
            $branchScore = 20.0;
            $branchMatched = true;
        } else {
            $allowedBranches = array_map('trim', explode(',', $eligibleBranchesStr));
            $studentBranchGroup = $this->normalizeBranchGroup($studentBranch);

            foreach ($allowedBranches as $ab) {
                $abGroup = $this->normalizeBranchGroup($ab);
                if (strtolower($studentBranch) === strtolower($ab) || ($studentBranchGroup !== 'other' && $studentBranchGroup === $abGroup)) {
                    $branchScore = 20.0;
                    $branchMatched = true;
                    break;
                }
            }
        }

        // ── 3. CGPA Match (20% max) ─────────────────────────────────────────
        $cgpaScore = 0.0;
        $cgpaMatched = false;
        $studentCgpa = (float)($student['cgpa'] ?? 0);
        $minCgpa     = (float)($job['eligibility_cgpa'] ?? 0);

        if ($minCgpa <= 0) {
            $cgpaScore = 20.0;
            $cgpaMatched = true;
        } elseif ($studentCgpa >= $minCgpa) {
            $cgpaScore = 20.0;
            $cgpaMatched = true;
        } elseif ($studentCgpa >= ($minCgpa - 0.5)) {
            // Partial credit if close (within 0.5 CGPA gap)
            $cgpaScore = 10.0;
        }

        // ── 4. Location Match (10% max) ──────────────────────────────────────
        $locationScore = 0.0;
        $locationMatched = false;
        $jobLoc = strtolower(trim($job['location'] ?? ''));
        $prefLoc = strtolower(trim($student['preferred_location'] ?? ($student['city'] ?? '')));
        $studentCity = strtolower(trim($student['city'] ?? ''));
        $studentState = strtolower(trim($student['state'] ?? ''));

        if (empty($jobLoc) || str_contains($jobLoc, 'remote') || str_contains($jobLoc, 'any') || str_contains($jobLoc, 'pan india')) {
            $locationScore = 10.0;
            $locationMatched = true;
        } elseif (!empty($prefLoc) && (str_contains($jobLoc, $prefLoc) || str_contains($prefLoc, $jobLoc))) {
            $locationScore = 10.0;
            $locationMatched = true;
        } elseif (!empty($studentCity) && str_contains($jobLoc, $studentCity)) {
            $locationScore = 10.0;
            $locationMatched = true;
        } elseif (!empty($studentState) && str_contains($jobLoc, $studentState)) {
            $locationScore = 5.0;
        }

        // Total calculated score (0 - 100)
        $totalScore = round($skillsScore + $branchScore + $cgpaScore + $locationScore);
        if ($totalScore > 100) $totalScore = 100;

        // ── Match Badge & Classification ───────────────────────────────────
        $badgeClass = 'bg-secondary';
        $matchLabel = 'Standard Match';

        if ($totalScore >= 85) {
            $badgeClass = 'bg-success';
            $matchLabel = 'Recommended';
        } elseif ($totalScore >= 70) {
            $badgeClass = 'bg-primary';
            $matchLabel = 'Good Match';
        } elseif ($totalScore >= 50) {
            $badgeClass = 'bg-warning text-dark';
            $matchLabel = 'Average Match';
        }

        // ── Natural Language Explanation Generator ──────────────────────────
        $reasons = [];

        if (!empty($matchedSkills)) {
            $skillNames = implode(', ', array_slice($matchedSkills, 0, 3));
            if (count($matchedSkills) > 3) {
                $skillNames .= ' and ' . (count($matchedSkills) - 3) . ' more';
            }
            $reasons[] = "your {$skillNames} skills match this job";
        }

        if ($branchMatched && !empty($studentBranch)) {
            $reasons[] = "your {$studentBranch} branch is eligible";
        }

        if ($cgpaMatched && $studentCgpa > 0) {
            if ($minCgpa > 0) {
                $reasons[] = "your CGPA ({$studentCgpa}) satisfies company requirements (min {$minCgpa})";
            } else {
                $reasons[] = "your CGPA ({$studentCgpa}) satisfies criteria";
            }
        }

        if ($locationMatched && !empty($job['location'])) {
            $reasons[] = "the job location ({$job['location']}) aligns with your preferred work location";
        }

        $explanation = '';
        if (!empty($reasons)) {
            if (count($reasons) === 1) {
                $explanation = "Recommended because " . $reasons[0] . ".";
            } else {
                $lastReason = array_pop($reasons);
                $explanation = "Recommended because " . implode(', ', $reasons) . " and " . $lastReason . ".";
            }
        } else {
            $explanation = "Matches general job criteria for active placement drive.";
        }

        return [
            'score'           => (int)$totalScore,
            'match_label'     => $matchLabel,
            'badge_class'     => $badgeClass,
            'explanation'     => $explanation,
            'matched_skills'  => $matchedSkills,
            'missing_skills'  => $missingSkills,
            'skills_score'    => $skillsScore,
            'branch_matched'  => $branchMatched,
            'cgpa_matched'    => $cgpaMatched,
            'location_matched'=> $locationMatched,
        ];
    }

    /**
     * Get top recommended jobs for a student ordered by recommendation match score
     */
    public function getRecommendedJobs(array $student, int $limit = 10, int $minScore = 20): array {
        $db = Database::getInstance();
        $studentId = $student['id'] ?? 0;

        // Fetch active jobs (excluding already applied jobs)
        $jobs = $db->fetchAll(
            "SELECT j.*, c.company_name, c.logo,
                    (SELECT COUNT(*) FROM applications a WHERE a.job_id = j.id AND a.student_id = ?) as has_applied,
                    (SELECT COUNT(*) FROM bookmarks b WHERE b.job_id = j.id AND b.student_id = ?) as is_bookmarked
             FROM jobs j
             JOIN companies c ON j.company_id = c.id
             WHERE j.status = 'active'
               AND (j.application_deadline IS NULL OR j.application_deadline >= CURDATE())
             ORDER BY j.created_at DESC",
            [$studentId, $studentId]
        );

        $recommended = [];

        foreach ($jobs as $job) {
            $matchResult = $this->calculateMatch($student, $job);
            if ($matchResult['score'] >= $minScore) {
                $job['match_score']      = $matchResult['score'];
                $job['match_label']      = $matchResult['match_label'];
                $job['match_badge_class']= $matchResult['badge_class'];
                $job['match_explanation']= $matchResult['explanation'];
                $job['matched_skills']   = $matchResult['matched_skills'];
                $recommended[] = $job;
            }
        }

        // Sort by match score descending
        usort($recommended, function ($a, $b) {
            return $b['match_score'] <=> $a['match_score'];
        });

        if ($limit > 0) {
            $recommended = array_slice($recommended, 0, $limit);
        }

        return $recommended;
    }
}
