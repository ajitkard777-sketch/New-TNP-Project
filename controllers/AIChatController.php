<?php
/**
 * TPMS - AI Chat Controller
 * Handles all AI assistant AJAX requests for Student, Company, and Admin roles.
 * Supports Groq AI (Llama 3.3 70B) & Google Gemini REST API.
 */

class AIChatController {

    private Database $db;
    private string   $userId;
    private string   $role;
    private string   $sessionId;

    public function __construct() {
        $this->db        = Database::getInstance();
        $this->userId    = (string) ($_SESSION['user_id'] ?? 0);
        $this->role      = $_SESSION['user_role'] ?? 'student';
        $this->sessionId = session_id();
    }

    // ─────────────────────────────────────────────────────────────
    // PUBLIC ENDPOINTS
    // ─────────────────────────────────────────────────────────────

    /**
     * POST /ai-chat/chat
     * Body: { message: string }
     */
    public function chat(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['error' => 'Method not allowed'], 405);
        }

        $rawInput = file_get_contents('php://input');
        $body     = json_decode($rawInput, true);
        $message  = trim($body['message'] ?? ($_POST['message'] ?? ''));

        if (empty($message)) {
            jsonResponse(['error' => 'Message cannot be empty'], 400);
        }

        if (strlen($message) > 2000) {
            jsonResponse(['error' => 'Message too long (max 2000 characters)'], 400);
        }

        $hasGroq   = defined('GROQ_API_KEY') && !empty(GROQ_API_KEY);
        $hasGemini = defined('GEMINI_API_KEY') && !empty(GEMINI_API_KEY);

        if (!$hasGroq && !$hasGemini) {
            jsonResponse([
                'reply'   => "⚠️ **AI assistant is not configured yet.**\n\nThe administrator needs to set the API key in the `.env` file.",
                'success' => false,
                'error'   => 'API key not configured'
            ]);
        }

        // Build context from DB
        $context = $this->buildContext();

        // Load recent history for conversation continuity (last 10 exchanges)
        $history = $this->getRecentHistory(10);

        // Dispatch call depending on provider availability
        $provider = defined('AI_PROVIDER') ? AI_PROVIDER : 'groq';
        if ($hasGroq && ($provider === 'groq' || !$hasGemini)) {
            $reply = $this->callGroq($message, $context, $history);
        } else {
            $reply = $this->callGemini($message, $context, $history);
        }

        // Save user message
        $this->saveMessage('user', $message);

        // Save AI reply
        $this->saveMessage('ai', $reply);

        jsonResponse(['reply' => $reply, 'success' => true]);
    }

    /**
     * GET /ai-chat/history
     * Returns last 40 messages for the current session.
     */
    public function getHistory(): void {
        $rows = $this->db->fetchAll(
            "SELECT sender, message, created_at FROM ai_chat_history
             WHERE user_id = ? AND session_id = ?
             ORDER BY created_at ASC
             LIMIT 40",
            [$this->userId, $this->sessionId]
        );
        jsonResponse(['history' => $rows, 'success' => true]);
    }

    /**
     * POST /ai-chat/clear
     * Clears current session chat history.
     */
    public function clearHistory(): void {
        $this->db->delete(
            "DELETE FROM ai_chat_history WHERE user_id = ? AND session_id = ?",
            [$this->userId, $this->sessionId]
        );
        jsonResponse(['success' => true, 'message' => 'Chat history cleared']);
    }

    // ─────────────────────────────────────────────────────────────
    // PRIVATE HELPERS — CONTEXT BUILDERS
    // ─────────────────────────────────────────────────────────────

    private function buildContext(): string {
        switch ($this->role) {
            case 'student':
                return $this->buildStudentContext();
            case 'company':
                return $this->buildCompanyContext();
            case 'admin':
                return $this->buildAdminContext();
            default:
                return '';
        }
    }

    private function buildStudentContext(): string {
        $student = $this->db->fetchOne(
            "SELECT s.*, u.email FROM students s JOIN users u ON s.user_id = u.id WHERE s.user_id = ?",
            [$this->userId]
        );
        if (!$student) return "Student profile not found.";

        $applications = $this->db->fetchAll(
            "SELECT j.title, c.company_name, a.status, a.applied_at
             FROM applications a
             JOIN jobs j ON a.job_id = j.id
             JOIN companies c ON j.company_id = c.id
             WHERE a.student_id = ?
             ORDER BY a.applied_at DESC LIMIT 10",
            [$student['id']]
        );

        $certifications = $this->db->fetchAll(
            "SELECT title, issuing_org FROM student_certifications WHERE student_id = ? LIMIT 10",
            [$student['id']]
        );

        $projects = $this->db->fetchAll(
            "SELECT title, technologies FROM student_projects WHERE student_id = ? LIMIT 5",
            [$student['id']]
        );

        $upcomingInterviews = $this->db->fetchAll(
            "SELECT i.interview_date, j.title as job_title, c.company_name
             FROM interviews i
             JOIN jobs j ON i.job_id = j.id
             JOIN companies c ON i.company_id = c.id
             WHERE i.student_id = ? AND i.status = 'scheduled'
             ORDER BY i.interview_date ASC LIMIT 5",
            [$student['id']]
        );

        $availableJobs = $this->db->fetchAll(
            "SELECT j.title, j.job_type, j.skills_required, j.eligibility_cgpa, j.salary_min, j.salary_max, c.company_name
             FROM jobs j JOIN companies c ON j.company_id = c.id
             WHERE j.status = 'active' AND (j.application_deadline IS NULL OR j.application_deadline >= CURDATE())
             ORDER BY j.created_at DESC LIMIT 10"
        );

        $ctx  = "=== STUDENT PROFILE ===\n";
        $ctx .= "Name: {$student['first_name']} {$student['last_name']}\n";
        $ctx .= "Email: {$student['email']}\n";
        $ctx .= "Branch: " . ($student['branch'] ?? 'N/A') . "\n";
        $ctx .= "Degree: " . ($student['degree'] ?? 'B.Tech') . "\n";
        $ctx .= "CGPA: " . ($student['cgpa'] ?? 'N/A') . "\n";
        $ctx .= "10th %: " . ($student['tenth_percentage'] ?? 'N/A') . "\n";
        $ctx .= "12th %: " . ($student['twelfth_percentage'] ?? 'N/A') . "\n";
        $ctx .= "Backlogs: " . ($student['backlogs'] ?? 0) . " (Active: " . ($student['active_backlogs'] ?? 0) . ")\n";
        $ctx .= "Skills: " . ($student['skills'] ?? 'Not specified') . "\n";
        $ctx .= "Profile completion: " . ($student['profile_completion'] ?? 0) . "%\n";
        $ctx .= "Is placed: " . ($student['is_placed'] ? 'Yes — at ' . $student['placed_company'] : 'No') . "\n";
        $ctx .= "Bio: " . ($student['bio'] ?? 'N/A') . "\n";

        if ($certifications) {
            $ctx .= "\n=== CERTIFICATIONS ===\n";
            foreach ($certifications as $c) {
                $ctx .= "- {$c['title']} by {$c['issuing_org']}\n";
            }
        }

        if ($projects) {
            $ctx .= "\n=== PROJECTS ===\n";
            foreach ($projects as $p) {
                $ctx .= "- {$p['title']} (Tech: {$p['technologies']})\n";
            }
        }

        if ($applications) {
            $ctx .= "\n=== RECENT JOB APPLICATIONS ===\n";
            foreach ($applications as $a) {
                $ctx .= "- {$a['title']} at {$a['company_name']}: Status = {$a['status']}\n";
            }
        }

        if ($upcomingInterviews) {
            $ctx .= "\n=== UPCOMING INTERVIEWS ===\n";
            foreach ($upcomingInterviews as $i) {
                $ctx .= "- {$i['job_title']} at {$i['company_name']} on {$i['interview_date']}\n";
            }
        }

        if ($availableJobs) {
            $ctx .= "\n=== CURRENTLY ACTIVE JOBS (Top 10) ===\n";
            foreach ($availableJobs as $j) {
                $ctx .= "- {$j['title']} at {$j['company_name']} ({$j['job_type']}), Min CGPA: {$j['eligibility_cgpa']}, Skills: {$j['skills_required']}\n";
            }
        }

        return $ctx;
    }

    private function buildCompanyContext(): string {
        $company = $this->db->fetchOne(
            "SELECT c.*, u.email FROM companies c JOIN users u ON c.user_id = u.id WHERE c.user_id = ?",
            [$this->userId]
        );
        if (!$company) return "Company profile not found.";

        $jobs = $this->db->fetchAll(
            "SELECT j.title, j.job_type, j.skills_required, j.status, j.openings, j.application_deadline,
                    (SELECT COUNT(*) FROM applications a WHERE a.job_id = j.id) as applicant_count
             FROM jobs j WHERE j.company_id = ?
             ORDER BY j.created_at DESC LIMIT 10",
            [$company['id']]
        );

        $recentApplicants = $this->db->fetchAll(
            "SELECT s.first_name, s.last_name, s.branch, s.cgpa, s.skills, a.status, j.title as job_title
             FROM applications a
             JOIN students s ON a.student_id = s.id
             JOIN jobs j ON a.job_id = j.id
             WHERE j.company_id = ?
             ORDER BY a.applied_at DESC LIMIT 15",
            [$company['id']]
        );

        $ctx  = "=== COMPANY PROFILE ===\n";
        $ctx .= "Company: {$company['company_name']}\n";
        $ctx .= "Industry: " . ($company['industry'] ?? 'N/A') . "\n";
        $ctx .= "Type: " . ($company['company_type'] ?? 'N/A') . "\n";
        $ctx .= "Size: " . ($company['employee_count'] ?? 'N/A') . " employees\n";
        $ctx .= "City: " . ($company['city'] ?? 'N/A') . ", " . ($company['state'] ?? '') . "\n";
        $ctx .= "Approved: " . ($company['is_approved'] ? 'Yes' : 'Pending') . "\n";

        if ($jobs) {
            $ctx .= "\n=== POSTED JOBS ===\n";
            foreach ($jobs as $j) {
                $ctx .= "- {$j['title']} ({$j['job_type']}), Status: {$j['status']}, Openings: {$j['openings']}, Applicants: {$j['applicant_count']}, Skills: {$j['skills_required']}\n";
            }
        }

        if ($recentApplicants) {
            $ctx .= "\n=== RECENT APPLICANTS ===\n";
            foreach ($recentApplicants as $a) {
                $ctx .= "- {$a['first_name']} {$a['last_name']} | Branch: {$a['branch']} | CGPA: {$a['cgpa']} | Skills: {$a['skills']} | Applied for: {$a['job_title']} | Status: {$a['status']}\n";
            }
        }

        return $ctx;
    }

    private function buildAdminContext(): string {
        $totalStudents  = (int) $this->db->fetchColumn("SELECT COUNT(*) FROM students");
        $placedStudents = (int) $this->db->fetchColumn("SELECT COUNT(*) FROM students WHERE is_placed = 1");
        $totalCompanies = (int) $this->db->fetchColumn("SELECT COUNT(*) FROM companies WHERE is_approved = 1");
        $activeJobs     = (int) $this->db->fetchColumn("SELECT COUNT(*) FROM jobs WHERE status = 'active'");
        $totalApps      = (int) $this->db->fetchColumn("SELECT COUNT(*) FROM applications");
        $totalTrainings = (int) $this->db->fetchColumn("SELECT COUNT(*) FROM trainings");
        $totalPlacements= (int) $this->db->fetchColumn("SELECT COUNT(*) FROM placements WHERE status = 'confirmed'");
        $avgCgpa        = $this->db->fetchColumn("SELECT ROUND(AVG(cgpa),2) FROM students WHERE cgpa IS NOT NULL");

        $branchStats = $this->db->fetchAll(
            "SELECT branch, COUNT(*) as count, SUM(is_placed) as placed FROM students WHERE branch IS NOT NULL GROUP BY branch ORDER BY count DESC LIMIT 10"
        );

        $recentPlacements = $this->db->fetchAll(
            "SELECT s.first_name, s.last_name, s.branch, p.package, c.company_name, p.placement_date
             FROM placements p
             JOIN students s ON p.student_id = s.id
             LEFT JOIN companies c ON p.company_id = c.id
             WHERE p.status = 'confirmed'
             ORDER BY p.placement_date DESC LIMIT 10"
        );

        $ctx  = "=== ADMIN DASHBOARD — PLATFORM STATISTICS ===\n";
        $ctx .= "Total Students: $totalStudents\n";
        $ctx .= "Placed Students: $placedStudents (" . ($totalStudents > 0 ? round($placedStudents/$totalStudents*100,1) : 0) . "%)\n";
        $ctx .= "Total Approved Companies: $totalCompanies\n";
        $ctx .= "Active Jobs: $activeJobs\n";
        $ctx .= "Total Applications: $totalApps\n";
        $ctx .= "Total Confirmed Placements: $totalPlacements\n";
        $ctx .= "Total Trainings: $totalTrainings\n";
        $ctx .= "Average CGPA: $avgCgpa\n";
        $ctx .= "Today's Date: " . date('d M Y') . "\n";

        if ($branchStats) {
            $ctx .= "\n=== BRANCH-WISE STATS ===\n";
            foreach ($branchStats as $b) {
                $ctx .= "- {$b['branch']}: {$b['count']} students, {$b['placed']} placed\n";
            }
        }

        if ($recentPlacements) {
            $ctx .= "\n=== RECENT PLACEMENTS ===\n";
            foreach ($recentPlacements as $p) {
                $ctx .= "- {$p['first_name']} {$p['last_name']} ({$p['branch']}) → {$p['company_name']} | Package: {$p['package']} LPA | Date: {$p['placement_date']}\n";
            }
        }

        return $ctx;
    }

    private function getRecentHistory(int $limit = 10): array {
        return $this->db->fetchAll(
            "SELECT sender, message FROM ai_chat_history
             WHERE user_id = ? AND session_id = ?
             ORDER BY created_at DESC
             LIMIT ?",
            [$this->userId, $this->sessionId, $limit]
        );
    }

    private function saveMessage(string $sender, string $message): void {
        try {
            $this->db->insert(
                "INSERT INTO ai_chat_history (user_id, role, session_id, sender, message) VALUES (?, ?, ?, ?, ?)",
                [$this->userId, $this->role, $this->sessionId, $sender, $message]
            );
        } catch (Exception $e) {
            error_log("AI Chat save error: " . $e->getMessage());
        }
    }

    private function buildSystemPrompt(string $context): string {
        $baseInstructions = "You are a helpful, concise, and professional AI assistant embedded in TPMS (Training & Placement Management System), an academic college placement portal in India.\n\n"
            . "FORMATTING RULES:\n"
            . "- Use markdown formatting (bold, bullets, numbered lists) for clarity.\n"
            . "- Keep responses focused and actionable. Avoid fluff.\n"
            . "- If recommending jobs or candidates, explain WHY (skill match, CGPA, etc.).\n"
            . "- Use Indian context (LPA for salary, Indian universities, Indian job market).\n"
            . "- If asked something unrelated to placements/education/career, politely redirect.\n\n";

        switch ($this->role) {
            case 'student':
                return $baseInstructions
                    . "You are a STUDENT CAREER ASSISTANT. Help students with:\n"
                    . "- Job recommendations based on their profile\n"
                    . "- Skill gap analysis for specific jobs\n"
                    . "- Resume improvement tips\n"
                    . "- Interview preparation (questions, tips, mock answers)\n"
                    . "- Career roadmap and certification suggestions\n"
                    . "- Higher studies guidance (GATE, GRE, CAT, MBA etc.)\n"
                    . "- Application status tracking advice\n"
                    . "- Aptitude and placement exam tips\n\n"
                    . "STUDENT DATA:\n{$context}";

            case 'company':
                return $baseInstructions
                    . "You are an HR RECRUITMENT ASSISTANT. Help company HR with:\n"
                    . "- Writing better, attractive job descriptions\n"
                    . "- Recommending and ranking suitable student candidates from the applicant pool\n"
                    . "- Generating role-specific interview questions\n"
                    . "- Summarizing candidate profiles (strengths/weaknesses)\n"
                    . "- Salary range benchmarking for Indian IT market\n"
                    . "- Hiring strategy and sourcing tips\n\n"
                    . "COMPANY DATA:\n{$context}";

            case 'admin':
                return $baseInstructions
                    . "You are an ADMIN ANALYTICS ASSISTANT. Help the placement coordinator with:\n"
                    . "- Analyzing placement trends and statistics\n"
                    . "- Identifying at-risk or unplaced students\n"
                    . "- Generating insights from placement data\n"
                    . "- Predicting placement rates based on current data\n"
                    . "- Suggesting improvements to the placement process\n"
                    . "- Summarizing reports for management\n\n"
                    . "PLATFORM DATA:\n{$context}";

            default:
                return $baseInstructions . "CONTEXT:\n{$context}";
        }
    }

    // ─────────────────────────────────────────────────────────────
    // API INTEGRATION — GROQ (OpenAI Compatible)
    // ─────────────────────────────────────────────────────────────

    private function callGroq(string $userMessage, string $context, array $history): string {
        $systemPrompt = $this->buildSystemPrompt($context);

        $messages = [];
        $messages[] = ['role' => 'system', 'content' => $systemPrompt];

        // Add history in chronological order
        $historyReversed = array_reverse($history);
        foreach ($historyReversed as $msg) {
            $messages[] = [
                'role'    => ($msg['sender'] === 'user') ? 'user' : 'assistant',
                'content' => $msg['message']
            ];
        }

        // Add user message
        $messages[] = ['role' => 'user', 'content' => $userMessage];

        $payload = json_encode([
            'model'       => GROQ_MODEL,
            'messages'    => $messages,
            'temperature' => 0.7,
            'max_tokens'  => 1024
        ]);

        $ch = curl_init(GROQ_API_URL);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . GROQ_API_KEY
            ],
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response  = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            error_log("Groq cURL error: $curlError");
            return "⚠️ Connection error. Please try again in a moment.";
        }

        $data = json_decode($response, true);

        if ($httpCode === 200 && isset($data['choices'][0]['message']['content'])) {
            return $data['choices'][0]['message']['content'];
        }

        $errMsg = $data['error']['message'] ?? "HTTP $httpCode";
        error_log("Groq API error ($httpCode): $errMsg");

        return "⚠️ AI service error: $errMsg";
    }

    // ─────────────────────────────────────────────────────────────
    // API INTEGRATION — GEMINI
    // ─────────────────────────────────────────────────────────────

    private function callGemini(string $userMessage, string $context, array $history): string {
        $systemPrompt = $this->buildSystemPrompt($context);

        $contents = [];
        $contents[] = [
            'role'  => 'user',
            'parts' => [['text' => $systemPrompt . "\n\n---\nNow respond as this assistant. The user will ask questions."]]
        ];
        $contents[] = [
            'role'  => 'model',
            'parts' => [['text' => "Understood! I'm ready to assist. How can I help you today?"]]
        ];

        $historyReversed = array_reverse($history);
        foreach ($historyReversed as $msg) {
            $contents[] = [
                'role'  => ($msg['sender'] === 'user') ? 'user' : 'model',
                'parts' => [['text' => $msg['message']]]
            ];
        }

        $contents[] = [
            'role'  => 'user',
            'parts' => [['text' => $userMessage]]
        ];

        $payload = json_encode([
            'contents'         => $contents,
            'generationConfig' => [
                'temperature'     => 0.75,
                'topK'            => 40,
                'topP'            => 0.95,
                'maxOutputTokens' => 1024,
            ]
        ]);

        $apiUrl = GEMINI_API_URL . '?key=' . GEMINI_API_KEY;

        $ch = curl_init($apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response  = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            error_log("Gemini cURL error: $curlError");
            return "⚠️ Connection error. Please try again in a moment.";
        }

        $data = json_decode($response, true);

        if ($httpCode === 200 && isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            return $data['candidates'][0]['content']['parts'][0]['text'];
        }

        $errMsg = $data['error']['message'] ?? "HTTP $httpCode";
        error_log("Gemini API error ($httpCode): $errMsg");

        return "⚠️ AI service error: $errMsg";
    }
}
