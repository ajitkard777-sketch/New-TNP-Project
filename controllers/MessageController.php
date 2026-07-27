<?php
/**
 * TPMS - Message Controller
 * Handles real-time chat sessions, AJAX polling, message sending, file sharing,
 * read receipts, and user presence.
 */

require_once ROOT_PATH . '/includes/helpers.php';
require_once ROOT_PATH . '/middleware/AuthMiddleware.php';
require_once ROOT_PATH . '/models/Message.php';
require_once ROOT_PATH . '/models/Student.php';
require_once ROOT_PATH . '/models/Company.php';

class MessageController {
    private Message $messageModel;
    private Database $db;

    public function __construct() {
        AuthMiddleware::requireLogin();
        $this->messageModel = new Message();
        $this->db = Database::getInstance();
    }

    /**
     * Display main chat view
     */
    public function index(): void {
        $role = getCurrentUserRole();
        if (!in_array($role, ['student', 'company'])) {
            setFlash('danger', 'Messaging is only available for Students and Company Recruiters.');
            redirect('/');
            return;
        }

        $userId = $_SESSION['user_id'];
        $this->messageModel->updatePresence($userId);

        $partnerId = isset($_GET['partner']) ? (int)$_GET['partner'] : 0;
        $pageTitle = 'Messages & Chat';

        require_once VIEWS_PATH . '/messages/chat.php';
    }

    /**
     * AJAX: Get conversation list
     */
    public function getConversations(): void {
        $userId = $_SESSION['user_id'];
        $role   = getCurrentUserRole();
        $search = sanitize($_GET['search'] ?? '');

        $this->messageModel->updatePresence($userId);
        $conversations = $this->messageModel->getConversations($userId, $role, $search);

        jsonResponse([
            'success' => true,
            'conversations' => $conversations,
            'total_unread' => $this->messageModel->getTotalUnreadCount($userId),
        ]);
    }

    /**
     * AJAX: Get chat history with partner
     */
    public function getHistory(): void {
        $userId    = $_SESSION['user_id'];
        $partnerId = (int)($_GET['partner_id'] ?? 0);

        if (!$partnerId) {
            jsonResponse(['success' => false, 'message' => 'Partner ID is required.'], 400);
            return;
        }

        if (!$this->messageModel->canUsersChat($userId, $partnerId)) {
            jsonResponse(['success' => false, 'message' => 'You are not authorized to message this contact.'], 403);
            return;
        }

        // Mark incoming unread messages as read
        $this->messageModel->markAsRead($userId, $partnerId);
        $this->messageModel->updatePresence($userId);

        $messages = $this->messageModel->getMessages($userId, $partnerId);
        $presence = $this->messageModel->getUserPresence($partnerId, $userId);

        // Get partner details
        $partnerUser = $this->db->fetchOne("SELECT id, role, email FROM users WHERE id = ?", [$partnerId]);
        $partnerName = '';
        $partnerAvatar = '';
        $partnerSubtitle = '';

        if ($partnerUser['role'] === 'company') {
            $comp = $this->db->fetchOne("SELECT company_name, logo FROM companies WHERE user_id = ?", [$partnerId]);
            $partnerName = $comp['company_name'] ?? 'Company';
            $partnerAvatar = $comp['logo'] ? uploadUrl('company/' . $comp['logo']) : asset('images/default-avatar.png');
            $partnerSubtitle = 'Company Recruiter';
        } else {
            $stu = $this->db->fetchOne("SELECT first_name, last_name, branch, profile_photo FROM students WHERE user_id = ?", [$partnerId]);
            $partnerName = ($stu['first_name'] ?? '') . ' ' . ($stu['last_name'] ?? '');
            $partnerAvatar = $stu['profile_photo'] ? uploadUrl('profile_photos/' . $stu['profile_photo']) : asset('images/default-avatar.png');
            $partnerSubtitle = $stu['branch'] ?? 'Student Applicant';
        }

        jsonResponse([
            'success' => true,
            'partner' => [
                'id'        => $partnerId,
                'name'      => $partnerName,
                'avatar'    => $partnerAvatar,
                'subtitle'  => $partnerSubtitle,
                'is_online' => $presence['is_online'],
                'last_seen' => $presence['last_seen'],
                'is_typing' => $presence['is_typing'],
            ],
            'messages' => $messages,
        ]);
    }

    /**
     * AJAX: Send a new message or file attachment
     */
    public function send(): void {
        CsrfMiddleware::requireValidToken();

        $senderId   = $_SESSION['user_id'];
        $receiverId = (int)($_POST['receiver_id'] ?? 0);
        $message    = sanitize($_POST['message'] ?? '');
        $jobId      = !empty($_POST['job_id']) ? (int)$_POST['job_id'] : null;
        $file       = $_FILES['attachment'] ?? null;

        if (!$receiverId) {
            jsonResponse(['success' => false, 'message' => 'Recipient ID is required.'], 400);
            return;
        }

        try {
            $msg = $this->messageModel->sendMessage($senderId, $receiverId, $message, $file, $jobId);
            $this->messageModel->updatePresence($senderId);

            jsonResponse([
                'success' => true,
                'message' => $msg,
            ]);
        } catch (Exception $e) {
            jsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * AJAX: Polling endpoint for real-time updates (called every 2-3s)
     */
    public function poll(): void {
        $userId    = $_SESSION['user_id'];
        $partnerId = (int)($_GET['partner_id'] ?? 0);
        $lastId    = (int)($_GET['last_id'] ?? 0);

        // Update presence for current user
        $this->messageModel->updatePresence($userId);

        $newMessages = [];
        $presence    = ['is_online' => false, 'last_seen' => '', 'is_typing' => false];

        if ($partnerId > 0 && $this->messageModel->canUsersChat($userId, $partnerId)) {
            // Mark new messages from partner as read
            $this->messageModel->markAsRead($userId, $partnerId);

            // Fetch new messages
            $newMessages = $this->messageModel->getMessages($userId, $partnerId, $lastId);
            $presence    = $this->messageModel->getUserPresence($partnerId, $userId);
        }

        jsonResponse([
            'success'      => true,
            'new_messages' => $newMessages,
            'presence'     => $presence,
            'total_unread' => $this->messageModel->getTotalUnreadCount($userId),
        ]);
    }

    /**
     * AJAX: Update typing indicator
     */
    public function typing(): void {
        $userId   = $_SESSION['user_id'];
        $targetId = (int)($_POST['target_id'] ?? 0);
        $isTyping = !empty($_POST['is_typing']);

        if ($targetId > 0) {
            $this->messageModel->updatePresence($userId, $targetId, $isTyping);
        }

        jsonResponse(['success' => true]);
    }

    /**
     * AJAX: Get total unread count for navbar badge
     */
    public function unreadCount(): void {
        $userId = $_SESSION['user_id'];
        $count  = $this->messageModel->getTotalUnreadCount($userId);
        jsonResponse(['success' => true, 'unread_count' => $count]);
    }

    /**
     * Download or view attached file securely
     */
    public function downloadFile(int $messageId): void {
        $userId = $_SESSION['user_id'];
        $msg    = $this->messageModel->getMessageById($messageId);

        if (!$msg || !$msg['file_path']) {
            setFlash('danger', 'Attachment not found.');
            redirect('/student/messages');
            return;
        }

        // Authorization check: User must be sender or receiver
        if ($msg['sender_id'] !== $userId && $msg['receiver_id'] !== $userId) {
            setFlash('danger', 'Unauthorized access to file attachment.');
            redirect('/');
            return;
        }

        $filePath = UPLOADS_PATH . '/chat_files/' . $msg['file_path'];
        if (!file_exists($filePath)) {
            setFlash('danger', 'Attachment file not found on server.');
            redirect('/student/messages');
            return;
        }

        if (ob_get_level()) { ob_end_clean(); }

        $filename = $msg['file_name'] ?: basename($msg['file_path']);
        $mime     = mime_content_type($filePath) ?: 'application/octet-stream';

        header('Content-Description: File Transfer');
        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }
}
