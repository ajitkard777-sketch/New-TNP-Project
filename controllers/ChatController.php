<?php
/**
 * TPMS - AI Career Chatbot Controller
 * Handles REST API endpoints for chatbot interaction
 */

require_once ROOT_PATH . '/models/AICareerChatbot.php';

class ChatController {
    private AICareerChatbot $bot;
    private Database $db;

    public function __construct() {
        $this->bot = new AICareerChatbot();
        $this->db = Database::getInstance();
    }

    /**
     * POST /api/chat
     */
    public function sendMessage(): void {
        AuthMiddleware::requireLogin();
        if ($_SESSION['user_role'] !== 'student') {
            jsonResponse(['error' => 'Chatbot is available for students'], 403);
            return;
        }

        $raw = file_get_contents('php://input');
        $json = json_decode($raw, true);
        $message = sanitize($_POST['message'] ?? $json['message'] ?? '');

        if (empty($message)) {
            jsonResponse(['error' => 'Message parameter is required'], 400);
            return;
        }

        $userId = (int)$_SESSION['user_id'];
        $result = $this->bot->processMessage($userId, $message);

        jsonResponse([
            'success' => true,
            'message' => $message,
            'response' => $result['response'],
            'type' => $result['type'] ?? 'text',
            'cards' => $result['cards'] ?? [],
            'timestamp' => date('h:i A')
        ]);
    }

    /**
     * POST /api/chat/recommend
     */
    public function getRecommendations(): void {
        AuthMiddleware::requireLogin();
        $userId = (int)$_SESSION['user_id'];
        $result = $this->bot->processMessage($userId, 'recommend jobs for me');

        jsonResponse([
            'success' => true,
            'response' => $result['response'],
            'type' => $result['type'],
            'cards' => $result['cards'] ?? []
        ]);
    }

    /**
     * GET /api/chat/history
     */
    public function getHistory(): void {
        AuthMiddleware::requireLogin();
        $userId = (int)$_SESSION['user_id'];

        $history = $this->db->fetchAll(
            "SELECT id, message, response, metadata_json, created_at FROM chat_history WHERE user_id = ? ORDER BY created_at ASC LIMIT 30",
            [$userId]
        );

        jsonResponse([
            'success' => true,
            'count' => count($history),
            'history' => $history
        ]);
    }

    /**
     * GET /api/chat/suggestions
     */
    public function getSuggestions(): void {
        AuthMiddleware::requireLogin();

        $suggestions = [
            ['icon' => '🎯', 'label' => 'Recommend Jobs', 'query' => 'Recommend jobs for me'],
            ['icon' => '💡', 'label' => 'Interview Tips', 'query' => 'Give me interview preparation tips'],
            ['icon' => '📄', 'label' => 'Resume Review', 'query' => 'Review my resume and suggest improvements'],
            ['icon' => '🎓', 'label' => 'Eligibility Check', 'query' => 'Which jobs am I eligible for?'],
            ['icon' => '🏢', 'label' => 'Companies Hiring', 'query' => 'Which companies are currently hiring?'],
            ['icon' => '🗺️', 'label' => 'Career Roadmap', 'query' => 'What is the career roadmap and salary outlook for my branch?'],
            ['icon' => '🧩', 'label' => 'Aptitude Questions', 'query' => 'How do I prepare for aptitude tests?'],
            ['icon' => '👔', 'label' => 'HR Questions', 'query' => 'Give me top HR interview questions and answers']
        ];

        jsonResponse([
            'success' => true,
            'suggestions' => $suggestions
        ]);
    }
}
