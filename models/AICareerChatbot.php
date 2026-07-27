<?php
/**
 * TPMS - AI Career Assistant Chatbot Service
 * Intelligently answers placement, career, interview, and job recommendation queries
 * personalized for the logged-in student's profile.
 */

require_once ROOT_PATH . '/models/Student.php';
require_once ROOT_PATH . '/models/RecommendationEngine.php';

class AICareerChatbot {
    private Database $db;
    private Student $studentModel;
    private RecommendationEngine $recEngine;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->studentModel = new Student();
        $this->recEngine = new RecommendationEngine();
    }

    /**
     * Process incoming chat message from student
     */
    public function processMessage(int $userId, string $userMessage): array {
        $student = $this->studentModel->findByUserId($userId);
        if (!$student) {
            return [
                'response' => "Hello! I am your AI Career Assistant. Please complete your student profile so I can provide personalized job and placement guidance.",
                'type' => 'text'
            ];
        }

        // Fetch student certifications
        $certifications = $this->db->fetchAll("SELECT title FROM student_certifications WHERE student_id = ?", [$student['id']]);
        $certTitles = array_map(fn($c) => $c['title'], $certifications);
        $student['certifications_list'] = implode(', ', $certTitles);

        $cleanMsg = strtolower(trim($userMessage));
        $intent = $this->detectIntent($cleanMsg);

        $responsePayload = match($intent) {
            'recommend_jobs' => $this->handleJobRecommendations($student),
            'eligibility_check' => $this->handleEligibilityCheck($student),
            'skill_gap' => $this->handleSkillGapAnalysis($student),
            'interview_hr' => $this->handleHrInterviewTips($student),
            'interview_tech' => $this->handleTechnicalInterviewTips($student),
            'aptitude' => $this->handleAptitudeTips($student),
            'resume_review' => $this->handleResumeReview($student),
            'career_roadmap' => $this->handleCareerRoadmap($student),
            'companies_hiring' => $this->handleCompaniesHiring($student),
            'higher_studies' => $this->handleHigherStudiesGuidance($student),
            default => $this->handleGeneralPlacementFaq($cleanMsg, $student)
        };

        // Save conversation history
        try {
            $this->db->query(
                "INSERT INTO chat_history (user_id, message, response, metadata_json) VALUES (?, ?, ?, ?)",
                [$userId, $userMessage, $responsePayload['response'], json_encode($responsePayload)]
            );
        } catch (Exception $e) {
            // Log failure silently
        }

        return $responsePayload;
    }

    /**
     * Intent Classification Routine
     */
    private function detectIntent(string $msg): string {
        if (preg_match('/(recommend|job|match|find job|opportunity|placement option|suggest job)/i', $msg)) {
            return 'recommend_jobs';
        }
        if (preg_match('/(eligible|eligibility|cgpa cutoff|criteria|backlog)/i', $msg)) {
            return 'eligibility_check';
        }
        if (preg_match('/(skill|learn|missing skill|course|upskill)/i', $msg)) {
            return 'skill_gap';
        }
        if (preg_match('/(hr|behavioral|tell me about yourself|strength|weakness|hr question)/i', $msg)) {
            return 'interview_hr';
        }
        if (preg_match('/(technical|coding|data structure|sql|java|python|react|tech interview)/i', $msg)) {
            return 'interview_tech';
        }
        if (preg_match('/(aptitude|quant|reasoning|math|speed|test)/i', $msg)) {
            return 'aptitude';
        }
        if (preg_match('/(resume|cv|profile|bullet|review)/i', $msg)) {
            return 'resume_review';
        }
        if (preg_match('/(roadmap|salary|career path|growth|future)/i', $msg)) {
            return 'career_roadmap';
        }
        if (preg_match('/(company|hiring|recruiter|mnc|startup)/i', $msg)) {
            return 'companies_hiring';
        }
        if (preg_match('/(higher study|gate|gre|mba|m.tech)/i', $msg)) {
            return 'higher_studies';
        }
        return 'general_faq';
    }

    /**
     * Handle Job Recommendations Intent
     */
    private function handleJobRecommendations(array $student): array {
        $recs = $this->recEngine->getStudentRecommendations((int)$student['id']);
        $studentName = htmlspecialchars($student['first_name']);
        $cgpa = number_format((float)($student['cgpa'] ?? 0), 2);
        $skills = htmlspecialchars($student['skills'] ?: 'General');

        if (empty($recs)) {
            return [
                'response' => "Hi **{$studentName}**,\n\nBased on your profile:\n• **Branch:** {$student['branch']}\n• **CGPA:** {$cgpa}\n• **Skills:** {$skills}\n\nCurrently, there are no active jobs matching your criteria. I recommend updating your skills or checking back soon!",
                'type' => 'text'
            ];
        }

        $top = array_slice($recs, 0, 3);
        $reply = "Hi **{$studentName}**,\n\nBased on your profile:\n• **CGPA:** {$cgpa}\n• **Branch:** {$student['branch']}\n• **Skills:** {$skills}\n\nHere are your **Top Recommended Jobs**:\n\n";

        $cards = [];
        foreach ($top as $idx => $r) {
            $num = $idx + 1;
            $reply .= "{$num}. **{$r['title']}** at **{$r['company_name']}**\n";
            $reply .= "   - **Recommendation Score:** {$r['recommendation_score']}% ({$r['recommendation_level']})\n";
            $reply .= "   - **Matched Skills:** " . (implode(', ', $r['matched_skills_array']) ?: 'General') . "\n";
            if (!empty($r['missing_skills_array'])) {
                $reply .= "   - **Missing Skills to Learn:** " . implode(', ', $r['missing_skills_array']) . "\n";
            }
            $reply .= "   - **Salary Package:** " . formatSalaryRange($r['salary_min'], $r['salary_max']) . "\n\n";

            $cards[] = [
                'job_id' => $r['job_id'],
                'title' => $r['title'],
                'company' => $r['company_name'],
                'score' => $r['recommendation_score'],
                'level' => $r['recommendation_level'],
                'matched' => $r['matched_skills_array'],
                'missing' => $r['missing_skills_array']
            ];
        }

        $reply .= "💡 *Tip: Click on any job card below or visit the [Browse Jobs](/student/jobs) page to apply directly!*";

        return [
            'response' => $reply,
            'type' => 'recommendation_cards',
            'cards' => $cards
        ];
    }

    /**
     * Handle Eligibility Check Intent
     */
    private function handleEligibilityCheck(array $student): array {
        $cgpa = (float)($student['cgpa'] ?? 0);
        $branch = $student['branch'] ?? 'N/A';
        $backlogs = (int)($student['active_backlogs'] ?? 0);

        $activeJobs = $this->db->fetchAll("SELECT j.*, c.company_name FROM jobs j JOIN companies c ON j.company_id = c.id WHERE j.status = 'active'");
        
        $eligibleCount = 0;
        $ineligibleList = [];

        foreach ($activeJobs as $j) {
            $minCgpa = (float)$j['eligibility_cgpa'];
            $branches = !empty($j['eligibility_branches']) ? array_map('trim', explode(',', $j['eligibility_branches'])) : [];
            
            $cgpaOk = ($minCgpa <= 0 || $cgpa >= $minCgpa);
            $branchOk = (empty($branches) || in_array($branch, $branches));
            $backlogOk = ($j['eligibility_backlogs'] >= $backlogs);

            if ($cgpaOk && $branchOk && $backlogOk) {
                $eligibleCount++;
            } else {
                $reasons = [];
                if (!$cgpaOk) $reasons[] = "Requires CGPA {$minCgpa}+ (Your CGPA: {$cgpa})";
                if (!$branchOk) $reasons[] = "Branch not eligible";
                if (!$backlogOk) $reasons[] = "Max backlogs exceeded";
                $ineligibleList[] = "**{$j['title']}** ({$j['company_name']}): " . implode(', ', $reasons);
            }
        }

        $reply = "🎓 **Placement Eligibility Report for {$student['first_name']}**:\n\n";
        $reply .= "• **Your CGPA:** " . number_format($cgpa, 2) . "\n";
        $reply .= "• **Your Branch:** {$branch}\n";
        $reply .= "• **Active Backlogs:** {$backlogs}\n\n";
        $reply .= "✅ **You are eligible for {$eligibleCount} out of " . count($activeJobs) . " active placement drives!**\n\n";

        if (!empty($ineligibleList)) {
            $reply .= "⚠️ **Opportunities where eligibility is currently unmet:**\n";
            foreach (array_slice($ineligibleList, 0, 3) as $in) {
                $reply .= "• {$in}\n";
            }
        }

        return ['response' => $reply, 'type' => 'text'];
    }

    /**
     * Handle Skill Gap Analysis
     */
    private function handleSkillGapAnalysis(array $student): array {
        $studentSkills = array_map('strtolower', array_map('trim', explode(',', $student['skills'] ?? '')));
        
        $jobsSkills = $this->db->fetchAll("SELECT skills_required FROM jobs WHERE status = 'active' AND skills_required IS NOT NULL");
        $allDemand = [];
        foreach ($jobsSkills as $row) {
            foreach (explode(',', $row['skills_required']) as $s) {
                $sNorm = ucwords(strtolower(trim($s)));
                if (!empty($sNorm)) {
                    $allDemand[$sNorm] = ($allDemand[$sNorm] ?? 0) + 1;
                }
            }
        }
        arsort($allDemand);

        $missingHighDemand = [];
        foreach ($allDemand as $sk => $cnt) {
            if (!in_array(strtolower($sk), $studentSkills)) {
                $missingHighDemand[] = "**{$sk}** (Required in {$cnt} active jobs)";
            }
        }

        $reply = "💡 **Skill Gap & Upskilling Suggestions for {$student['first_name']}**:\n\n";
        $reply .= "Based on real-time placement market demand, learning the following skills will significantly increase your hiring probability:\n\n";

        foreach (array_slice($missingHighDemand, 0, 5) as $m) {
            $reply .= "• {$m}\n";
        }

        $reply .= "\n🚀 **Action Steps:**\n";
        $reply .= "1. Complete 1-2 practical projects using these skills.\n";
        $reply .= "2. Add verified certifications to your TPMS profile.\n";
        $reply .= "3. Update your skills in your [Edit Profile](/student/profile/edit) page to refresh your match scores!";

        return ['response' => $reply, 'type' => 'text'];
    }

    /**
     * Handle HR Interview Tips
     */
    private function handleHrInterviewTips(array $student): array {
        $reply = "👔 **Top HR Interview Preparation Guide**:\n\n";
        $reply .= "Here are key HR questions and winning answer frameworks for **{$student['first_name']}**:\n\n";
        $reply .= "1️⃣ **'Tell me about yourself'**\n";
        $reply .= "   - *Framework:* Present (Degree & CGPA {$student['cgpa']}) -> Past (Projects & Skills: {$student['skills']}) -> Future (Why this company excites you).\n\n";
        $reply .= "2️⃣ **'What is your biggest strength?'**\n";
        $reply .= "   - *Tip:* Highlight problem solving or adaptability with a 30-second STAR story from your academic projects.\n\n";
        $reply .= "3️⃣ **'Why do you want to join our company?'**\n";
        $reply .= "   - *Tip:* Mention specific products, tech stack, or company achievements rather than generic answers.\n\n";
        $reply .= "4️⃣ **'Where do you see yourself in 5 years?'**\n";
        $reply .= "   - *Tip:* Focus on technical mastery, leadership growth, and contributing to core team goals.";

        return ['response' => $reply, 'type' => 'text'];
    }

    /**
     * Handle Technical Interview Tips
     */
    private function handleTechnicalInterviewTips(array $student): array {
        $branch = $student['branch'] ?? 'Computer Science';
        $skills = $student['skills'] ?? 'Data Structures, SQL';

        $reply = "💻 **Technical Interview Preparation for {$branch}**:\n\n";
        $reply .= "Core focus areas based on your skills (**{$skills}**):\n\n";
        $reply .= "• **Data Structures & Algorithms:** Arrays, Linked Lists, Trees, Sorting, & Complexity Analysis.\n";
        $reply .= "• **Database & SQL:** Indexing, Joins, Normalization, ACID properties, Group By queries.\n";
        $reply .= "• **OOP Concepts:** Encapsulation, Inheritance, Polymorphism, Abstraction with code examples.\n";
        $reply .= "• **System Design / Projects:** Be ready to explain architecture, database schema, and challenges faced in your listed projects.\n\n";
        $reply .= "📌 *Pro Tip: Always think aloud while coding during live technical rounds!*";

        return ['response' => $reply, 'type' => 'text'];
    }

    /**
     * Handle Aptitude Preparation Tips
     */
    private function handleAptitudeTips(array $student): array {
        $reply = "🧩 **Aptitude & Reasoning Test Strategy**:\n\n";
        $reply .= "1. **Quantitative Aptitude:** Focus on Percentages, Profit & Loss, Time & Work, Speed & Distance, Averages.\n";
        $reply .= "2. **Logical Reasoning:** Practice Blood Relations, Coding-Decoding, Seating Arrangements, Syllogisms.\n";
        $reply .= "3. **Verbal Ability:** Reading Comprehension, Error Spotting, Synonyms/Antonyms.\n\n";
        $reply .= "⏱️ **Speed Hack:** Skip time-consuming questions initially and complete easy 30-second questions first!";

        return ['response' => $reply, 'type' => 'text'];
    }

    /**
     * Handle Resume Review
     */
    private function handleResumeReview(array $student): array {
        $hasResume = !empty($student['resume_path']);
        $completion = (int)($student['profile_completion'] ?? 0);

        $reply = "📄 **Resume Evaluation for {$student['first_name']}**:\n\n";
        $reply .= "• **Profile Completion:** {$completion}%\n";
        $reply .= "• **Uploaded Resume Document:** " . ($hasResume ? "✅ Uploaded" : "❌ Missing - Upload now!") . "\n\n";
        $reply .= "🚀 **3 Instant Resume Boosters:**\n";
        $reply .= "1. **Quantify Results:** Use numbers (e.g. 'Improved query performance by 40%').\n";
        $reply .= "2. **Action Verbs:** Start project bullets with Built, Developed, Designed, Automated.\n";
        $reply .= "3. **Skill Alignment:** Ensure skills listed in your profile match the job requirements.";

        return ['response' => $reply, 'type' => 'text'];
    }

    /**
     * Handle Career Roadmap
     */
    private function handleCareerRoadmap(array $student): array {
        $branch = $student['branch'] ?: 'Engineering';
        $reply = "🗺️ **Career Roadmap & Salary Expectations for {$branch}**:\n\n";
        $reply .= "• **Entry Level (0-2 Yrs):** Software Engineer / Associate Analyst\n  - Salary: ₹4.5 LPA - ₹12 LPA\n";
        $reply .= "• **Mid Level (2-5 Yrs):** Senior Software Engineer / Module Lead\n  - Salary: ₹12 LPA - ₹24 LPA\n";
        $reply .= "• **Senior Level (5+ Yrs):** Technical Architect / Engineering Manager\n  - Salary: ₹25 LPA+";

        return ['response' => $reply, 'type' => 'text'];
    }

    /**
     * Handle Companies Hiring
     */
    private function handleCompaniesHiring(array $student): array {
        $companies = $this->db->fetchAll("SELECT c.company_name, c.industry, COUNT(j.id) as active_jobs FROM companies c JOIN jobs j ON c.id = j.company_id WHERE j.status = 'active' GROUP BY c.id ORDER BY active_jobs DESC LIMIT 5");
        
        $reply = "🏢 **Top Active Hiring Companies in TPMS**:\n\n";
        foreach ($companies as $c) {
            $reply .= "• **{$c['company_name']}** ({$c['industry']}) - {$c['active_jobs']} active drive(s)\n";
        }
        $reply .= "\nVisit the [Browse Jobs](/student/jobs) page to view all open drives.";

        return ['response' => $reply, 'type' => 'text'];
    }

    /**
     * Handle Higher Studies Guidance
     */
    private function handleHigherStudiesGuidance(array $student): array {
        $reply = "🎓 **Higher Studies vs Job Placement Guidance**:\n\n";
        $reply .= "• **GATE (M.Tech / PSU):** Ideal for core research & PSU job entry. Start preparation 1 year in advance.\n";
        $reply .= "• **CAT / MBA:** Best for management, consulting, and product management roles.\n";
        $reply .= "• **GRE / MS Abroad:** Focus on SOP, LORs, and maintaining a high CGPA ({$student['cgpa']}).\n\n";
        $reply .= "Check the [Higher Studies](/student/higher-studies) tab on your dashboard for program listings!";

        return ['response' => $reply, 'type' => 'text'];
    }

    /**
     * General Placement FAQ Handler
     */
    private function handleGeneralPlacementFaq(string $msg, array $student): array {
        $reply = "Hello **{$student['first_name']}**! I am your AI Placement Assistant.\n\n";
        $reply .= "I can help you with:\n";
        $reply .= "• 🎯 **Personalized Job Recommendations**\n";
        $reply .= "• 🎓 **Eligibility & CGPA Checks**\n";
        $reply .= "• 💡 **Skill Gap & Learning Suggestions**\n";
        $reply .= "• 👔 **HR & Technical Interview Tips**\n";
        $reply .= "• 📄 **Resume Review & Feedback**\n\n";
        $reply .= "Click any quick suggestion below or ask me any question about placements!";

        return ['response' => $reply, 'type' => 'text'];
    }
}
