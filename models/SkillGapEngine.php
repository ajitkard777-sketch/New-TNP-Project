<?php
/**
 * TPMS - AI Skill Gap Analysis & Course Recommendation Engine Model
 */

require_once ROOT_PATH . '/models/Student.php';
require_once ROOT_PATH . '/models/RecommendationEngine.php';

class SkillGapEngine {
    private Database $db;
    private Student $studentModel;
    private RecommendationEngine $recEngine;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->studentModel = new Student();
        $this->recEngine = new RecommendationEngine();
    }

    /**
     * Perform Skill Gap Analysis for Student against all active jobs
     */
    public function analyzeStudentSkillGap(int $studentId): array {
        $student = $this->studentModel->findById($studentId);
        if (!$student) {
            return ['matched' => [], 'missing' => [], 'average_match' => 0];
        }

        $studentSkills = $this->parseSkills($student['skills'] ?? '');

        // Fetch active jobs
        $jobs = $this->db->fetchAll("SELECT j.*, c.company_name FROM jobs j JOIN companies c ON j.company_id = c.id WHERE j.status = 'active'");
        if (empty($jobs)) {
            return ['matched' => $studentSkills, 'missing' => [], 'average_match' => 100];
        }

        $allMatchedMap = [];
        $allMissingMap = [];
        $totalMatchPercentages = 0;
        $jobCount = count($jobs);

        foreach ($jobs as $job) {
            $jobSkills = $this->parseSkills($job['skills_required'] ?? '');
            
            $matched = [];
            $missing = [];

            if (empty($jobSkills)) {
                $matchPct = 100.00;
            } else {
                foreach ($jobSkills as $jSkill) {
                    $hasSkill = false;
                    foreach ($studentSkills as $sSkill) {
                        if (strcasecmp($jSkill, $sSkill) === 0 || str_contains(strtolower($sSkill), strtolower($jSkill))) {
                            $hasSkill = true;
                            break;
                        }
                    }
                    if ($hasSkill) {
                        $matched[] = $jSkill;
                        $allMatchedMap[$jSkill] = ($allMatchedMap[$jSkill] ?? 0) + 1;
                    } else {
                        $missing[] = $jSkill;
                        $allMissingMap[$jSkill] = ($allMissingMap[$jSkill] ?? 0) + 1;
                    }
                }
                $matchPct = round((count($matched) / count($jobSkills)) * 100, 2);
            }

            $totalMatchPercentages += $matchPct;

            // UPSERT into skill_gap_analysis table
            $sql = "INSERT INTO skill_gap_analysis (student_id, job_id, matched_skills, missing_skills, skill_match_percentage, created_at)
                    VALUES (?, ?, ?, ?, ?, NOW())
                    ON DUPLICATE KEY UPDATE
                        matched_skills = VALUES(matched_skills),
                        missing_skills = VALUES(missing_skills),
                        skill_match_percentage = VALUES(skill_match_percentage),
                        created_at = NOW()";

            $this->db->query($sql, [
                $studentId,
                $job['id'],
                implode(',', $matched),
                implode(',', $missing),
                $matchPct
            ]);
        }

        arsort($allMissingMap);
        arsort($allMatchedMap);

        $avgMatch = round($totalMatchPercentages / max(1, $jobCount), 1);

        return [
            'matched_skills' => array_keys($allMatchedMap),
            'missing_skills' => $allMissingMap,
            'average_match' => $avgMatch,
            'analyzed_jobs_count' => $jobCount
        ];
    }

    /**
     * Generate AI Insights and Readiness Metrics for Student
     */
    public function getSkillInsights(int $studentId): array {
        $student = $this->studentModel->findById($studentId);
        $gapData = $this->analyzeStudentSkillGap($studentId);

        $studentCgpa = (float)($student['cgpa'] ?? 0);
        $avgSkillMatch = $gapData['average_match'];
        $profileCompletion = (int)($student['profile_completion'] ?? 50);

        // Overall Readiness Score (Weighted composite)
        $readinessScore = round(($avgSkillMatch * 0.60) + (min(10, $studentCgpa) * 2.5) + ($profileCompletion * 0.15), 1);
        $readinessScore = min(99.0, max(25.0, $readinessScore));

        // Missing Skill Metadata with Learning Time, Demand, and Difficulty
        $missingSkillsMeta = [];
        $topJobCount = max(1, $gapData['analyzed_jobs_count']);

        foreach ($gapData['missing_skills'] as $skillName => $freq) {
            $meta = $this->getSkillDetails($skillName, $freq, $topJobCount);
            $missingSkillsMeta[] = $meta;
        }

        // Generate AI Callout Statements
        $insights = [];

        $preferredRole = !empty($student['branch']) ? $student['branch'] . ' Specialist' : 'Software Engineer';
        $insights[] = [
            'icon' => 'fas fa-chart-line text-primary',
            'title' => 'Job Readiness Index',
            'text' => "You are **{$readinessScore}% ready** for active {$preferredRole} placement drives."
        ];

        if (!empty($missingSkillsMeta)) {
            $top1 = $missingSkillsMeta[0]['skill_name'];
            $top2 = isset($missingSkillsMeta[1]) ? $missingSkillsMeta[1]['skill_name'] : '';
            $boostPct = min(35, count($missingSkillsMeta) * 8);

            $skillsText = $top2 ? "**{$top1}** and **{$top2}**" : "**{$top1}**";
            $insights[] = [
                'icon' => 'fas fa-rocket text-success',
                'title' => 'Eligibility Boost',
                'text' => "Learning {$skillsText} will increase your job eligibility by approximately **{$boostPct}%**."
            ];

            $opportunityGain = min(15, round($missingSkillsMeta[0]['job_demand'] * 1.5));
            $insights[] = [
                'icon' => 'fas fa-briefcase text-warning',
                'title' => 'Opportunity Expansion',
                'text' => "Mastering **{$top1}** will make you eligible for **{$opportunityGain} additional** campus recruitment opportunities."
            ];
        } else {
            $insights[] = [
                'icon' => 'fas fa-trophy text-success',
                'title' => 'Complete Skill Alignment',
                'text' => "You possess all high-demand technical skills required for active recruitment drives!"
            ];
        }

        return [
            'readiness_score' => $readinessScore,
            'average_skill_match' => $avgSkillMatch,
            'matched_skills' => $gapData['matched_skills'],
            'missing_skills' => $missingSkillsMeta,
            'insights' => $insights
        ];
    }

    /**
     * Get Recommended Courses for missing skills or search filter
     */
    public function getRecommendedCourses(int $studentId, array $missingSkillNames = [], string $searchQuery = ''): array {
        $params = [];
        $where = "1=1";

        if (!empty($searchQuery)) {
            $where .= " AND (rc.skill_name LIKE ? OR rc.course_name LIKE ? OR rc.platform LIKE ? OR rc.description LIKE ?)";
            $params = array_merge($params, ["%$searchQuery%", "%$searchQuery%", "%$searchQuery%", "%$searchQuery%"]);
        } elseif (!empty($missingSkillNames)) {
            $placeholders = implode(',', array_fill(0, count($missingSkillNames), '?'));
            $where .= " AND LOWER(rc.skill_name) IN ($placeholders)";
            $params = array_map('strtolower', $missingSkillNames);
        }

        $sql = "SELECT rc.*,
                       slp.status as student_status,
                       slp.progress as student_progress,
                       slp.completed_at
                FROM recommended_courses rc
                LEFT JOIN student_learning_progress slp ON rc.id = slp.course_id AND slp.student_id = ?
                WHERE $where
                ORDER BY rc.rating DESC, rc.is_free DESC";

        array_unshift($params, $studentId);
        $courses = $this->db->fetchAll($sql, $params);

        foreach ($courses as &$c) {
            $c['is_enrolled'] = !empty($c['student_status']);
            $c['is_completed'] = ($c['student_status'] === 'completed');
            $c['student_progress'] = (int)($c['student_progress'] ?? 0);
        }

        return $courses;
    }

    /**
     * Generate Personalized Vertical Learning Roadmap
     */
    public function generateRoadmap(int $studentId): array {
        $student = $this->studentModel->findById($studentId);
        $gapData = $this->analyzeStudentSkillGap($studentId);

        $studentSkills = !empty($student['skills']) ? array_map('trim', explode(',', $student['skills'])) : ['General Problem Solving'];
        $missingSkills = array_keys($gapData['missing_skills']);

        $steps = [];

        // Step 1: Mastered Skills Foundation
        $steps[] = [
            'step_number' => 1,
            'title' => 'Mastered Foundation Skills',
            'category' => 'Foundation',
            'icon' => 'fas fa-check-circle text-success',
            'description' => 'Current skills on your profile: ' . implode(', ', array_slice($studentSkills, 0, 5)),
            'status' => 'completed',
            'progress' => 100
        ];

        // Step 2 & 3: High Priority Missing Skill Milestones
        $stepNum = 2;
        foreach (array_slice($missingSkills, 0, 3) as $mSkill) {
            $steps[] = [
                'step_number' => $stepNum,
                'title' => 'Learn ' . $mSkill,
                'category' => 'Core Upskilling',
                'icon' => 'fas fa-laptop-code text-primary',
                'description' => 'Complete recommended courses & build hands-on practice modules for ' . $mSkill,
                'status' => ($stepNum === 2 ? 'in_progress' : 'pending'),
                'progress' => ($stepNum === 2 ? 35 : 0)
            ];
            $stepNum++;
        }

        // Step 4: Practical Project Milestone
        $topSkill = !empty($missingSkills[0]) ? $missingSkills[0] : 'Full Stack';
        $steps[] = [
            'step_number' => $stepNum,
            'title' => 'Build ' . $topSkill . ' Portfolio Project',
            'category' => 'Hands-On Project',
            'icon' => 'fas fa-project-diagram text-warning',
            'description' => 'Build and publish a real-world web/API application on GitHub incorporating ' . $topSkill,
            'status' => 'pending',
            'progress' => 0
        ];
        $stepNum++;

        // Step 5: Aptitude & Interview Prep
        $steps[] = [
            'step_number' => $stepNum,
            'title' => 'Aptitude & Technical Interview Prep',
            'category' => 'Placement Readiness',
            'icon' => 'fas fa-user-tie text-info',
            'description' => 'Practice Quantitative Aptitude, Logical Reasoning, and Technical Mock Interviews',
            'status' => 'pending',
            'progress' => 0
        ];
        $stepNum++;

        // Step 6: Target Placement Role
        $targetRole = !empty($student['branch']) ? $student['branch'] . ' Engineer / Developer' : 'Target Campus Role';
        $steps[] = [
            'step_number' => $stepNum,
            'title' => 'Eligible for ' . $targetRole,
            'category' => 'Placement Goal',
            'icon' => 'fas fa-trophy text-success',
            'description' => 'Apply to 100% matching campus recruitment drives with high selection probability',
            'status' => 'pending',
            'progress' => 0
        ];

        return $steps;
    }

    /**
     * Update Student Course Progress (Enroll / Progress % / Mark Completed)
     */
    public function updateCourseProgress(int $studentId, int $courseId, string $status = 'enrolled', int $progress = 50): bool {
        $completedAt = ($status === 'completed') ? date('Y-m-d H:i:s') : null;
        if ($status === 'completed') $progress = 100;

        $sql = "INSERT INTO student_learning_progress (student_id, course_id, status, progress, completed_at, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE
                    status = VALUES(status),
                    progress = VALUES(progress),
                    completed_at = VALUES(completed_at)";

        $this->db->query($sql, [$studentId, $courseId, $status, $progress, $completedAt]);
        return true;
    }

    /**
     * Helper to build detailed metadata for missing skills
     */
    private function getSkillDetails(string $skill, int $jobDemand, int $totalJobs): array {
        $skillLower = strtolower($skill);

        $details = [
            'spring boot' => ['importance' => 'Used in Enterprise Java Backend Development & Microservices Architecture.', 'time' => '2-3 Weeks', 'difficulty' => 'Intermediate', 'demand' => 'High'],
            'docker' => ['importance' => 'Essential for DevOps, Containerization, and Microservices Cloud Deployment.', 'time' => '1-2 Weeks', 'difficulty' => 'Intermediate', 'demand' => 'High'],
            'git' => ['importance' => 'Universal Version Control System required by 100% of software companies.', 'time' => '3-5 Days', 'difficulty' => 'Beginner', 'demand' => 'High'],
            'aws' => ['importance' => 'Leading Cloud Infrastructure platform for hosting scalable web applications.', 'time' => '3-4 Weeks', 'difficulty' => 'Intermediate', 'demand' => 'High'],
            'react' => ['importance' => 'Top Frontend JavaScript framework for building modern interactive web UIs.', 'time' => '2-3 Weeks', 'difficulty' => 'Intermediate', 'demand' => 'High'],
            'node.js' => ['importance' => 'Asynchronous JavaScript Runtime for building high-speed RESTful Backend APIs.', 'time' => '2 Weeks', 'difficulty' => 'Intermediate', 'demand' => 'High'],
            'python' => ['importance' => 'High-level language widely used in AI, Machine Learning, Data Science, and Web Apps.', 'time' => '2 Weeks', 'difficulty' => 'Beginner', 'demand' => 'High'],
            'mysql' => ['importance' => 'Core Relational Database Management System for structured data storage.', 'time' => '1 Week', 'difficulty' => 'Beginner', 'demand' => 'High'],
            'kubernetes' => ['importance' => 'Container Orchestration platform for managing large-scale Docker deployments.', 'time' => '3 Weeks', 'difficulty' => 'Advanced', 'demand' => 'High']
        ];

        $def = $details[$skillLower] ?? [
            'importance' => 'High-demand industry technical skill required across modern recruitment drives.',
            'time' => '1-2 Weeks',
            'difficulty' => 'Intermediate',
            'demand' => 'High'
        ];

        return [
            'skill_name' => $skill,
            'importance' => $def['importance'],
            'learning_time' => $def['time'],
            'difficulty' => $def['difficulty'],
            'demand_level' => $def['demand'],
            'job_demand' => $jobDemand
        ];
    }

    private function parseSkills(string $skillsStr): array {
        if (empty(trim($skillsStr))) return [];
        $skills = array_map('trim', explode(',', $skillsStr));
        return array_filter($skills, fn($s) => !empty($s));
    }
}
