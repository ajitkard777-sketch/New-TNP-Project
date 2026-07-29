<?php
/**
 * TPMS - Chat Controller
 * Manages all Chat system requests and real-time polling AJAX endpoints.
 */

require_once ROOT_PATH . '/models/Chat.php';

class ChatController {

    private Chat $chatModel;
    private int  $userId;
    private string $role;

    public function __construct() {
        AuthMiddleware::requireLogin();
        $this->chatModel = new Chat();
        $this->userId    = (int)($_SESSION['user_id'] ?? 0);
        $this->role      = getCurrentUserRole();
    }

    /**
     * Main Chat Interface View (/chat)
     */
    public function index(): void {
        $pageTitle = 'Messages & Communication';
        $userId    = $this->userId;
        $role      = $this->role;

        // Touch user presence
        $this->chatModel->updatePresence($this->userId);

        require_once VIEWS_PATH . '/chat/index.php';
    }

    /**
     * GET /chat/contacts
     * Returns allowed contact list based on role permission rules.
     */
    public function getContacts(): void {
        $this->chatModel->updatePresence($this->userId);
        $contacts = $this->chatModel->getAllowedContacts($this->userId, $this->role);
        jsonResponse(['success' => true, 'contacts' => $contacts]);
    }

    /**
     * GET /chat/conversations
     * Returns active conversations for current user.
     */
    public function getConversations(): void {
        $this->chatModel->updatePresence($this->userId);
        $conversations = $this->chatModel->getUserConversations($this->userId);
        $totalUnread   = $this->chatModel->getTotalUnreadCount($this->userId);

        jsonResponse([
            'success'       => true,
            'conversations' => $conversations,
            'unread_total'  => $totalUnread,
        ]);
    }

    /**
     * GET /chat/messages/{id}
     * Returns message history for a conversation.
     */
    public function getMessages($conversationId): void {
        $conversationId = (int)$conversationId;
        $this->chatModel->updatePresence($this->userId);

        $db   = Database::getInstance();
        $conv = $db->fetchOne("SELECT * FROM chat_conversations WHERE id = ?", [$conversationId]);

        if (!$conv || ($conv['user_one_id'] != $this->userId && $conv['user_two_id'] != $this->userId)) {
            jsonResponse(['error' => 'Conversation not found or access denied'], 403);
        }

        $otherUserId = ((int)$conv['user_one_id'] === $this->userId) ? (int)$conv['user_two_id'] : (int)$conv['user_one_id'];
        $recipient   = $this->chatModel->getUserDisplayInfo($otherUserId);
        $messages    = $this->chatModel->getMessages($conversationId, $this->userId);

        // Check if other user is typing or online
        $presence = $db->fetchOne("SELECT last_active_at, typing_to_user_id, typing_updated_at FROM chat_presence WHERE user_id = ?", [$otherUserId]);
        $isOnline = false;
        $isTyping = false;
        if ($presence) {
            if ($presence['last_active_at'] && (time() - strtotime($presence['last_active_at'])) < 25) {
                $isOnline = true;
            }
            if ((int)($presence['typing_to_user_id'] ?? 0) === $this->userId && $presence['typing_updated_at'] && (time() - strtotime($presence['typing_updated_at'])) < 6) {
                $isTyping = true;
            }
        }

        jsonResponse([
            'success'        => true,
            'conversation'   => $conv,
            'other_user_id'  => $otherUserId,
            'recipient'      => $recipient,
            'is_online'      => $isOnline,
            'is_typing'      => $isTyping,
            'messages'       => $messages,
        ]);
    }

    /**
     * POST /chat/start
     * Start or open a conversation with a specific target user ID.
     */
    public function startConversation(): void {
        $raw   = file_get_contents('php://input');
        $body  = json_decode($raw, true) ?: [];
        $targetUserId = (int)($body['target_user_id'] ?? $_POST['target_user_id'] ?? $_GET['target_user_id'] ?? 0);

        if (!$targetUserId || $targetUserId === $this->userId) {
            jsonResponse(['error' => 'Invalid user selected'], 400);
        }

        // Verify role permission
        if (!$this->chatModel->isChatAllowed($this->userId, $this->role, $targetUserId)) {
            jsonResponse(['error' => 'You do not have permission to chat with this user'], 403);
        }

        $conv = $this->chatModel->getOrCreateConversation($this->userId, $targetUserId);
        jsonResponse([
            'success'         => true,
            'conversation_id' => (int)$conv['id'],
            'target_user_id'  => $targetUserId,
        ]);
    }

    /**
     * POST /chat/send
     * Send message & upload attachment.
     */
    public function sendMessage(): void {
        $raw   = file_get_contents('php://input');
        $json  = json_decode($raw, true) ?: [];

        $conversationId = (int)($_POST['conversation_id'] ?? $json['conversation_id'] ?? 0);
        $messageText    = trim($_POST['message'] ?? $json['message'] ?? '');


        if (!$conversationId) {
            jsonResponse(['error' => 'Conversation ID is required'], 400);
        }

        $db   = Database::getInstance();
        $conv = $db->fetchOne("SELECT * FROM chat_conversations WHERE id = ?", [$conversationId]);

        if (!$conv || ($conv['user_one_id'] != $this->userId && $conv['user_two_id'] != $this->userId)) {
            jsonResponse(['error' => 'Conversation not found or access denied'], 403);
        }

        $receiverId = ((int)$conv['user_one_id'] === $this->userId) ? (int)$conv['user_two_id'] : (int)$conv['user_one_id'];

        // Attachment handling
        $attachment = null;
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $file     = $_FILES['attachment'];
            $fileName = basename($file['name']);
            $fileSize = (int)$file['size'];
            $tmpPath  = $file['tmp_name'];

            if ($fileSize > 10 * 1024 * 1024) { // 10MB limit
                jsonResponse(['error' => 'File size exceeds 10MB limit'], 400);
            }

            $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowedExts = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'zip', 'txt'];
            if (!in_array($ext, $allowedExts)) {
                jsonResponse(['error' => 'File format not supported (.pdf, .doc, .docx, .jpg, .png, .zip, .txt allowed)'], 400);
            }

            $uploadDir = UPLOADS_PATH . '/chat';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileType = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']) ? 'image' : ($ext === 'pdf' ? 'pdf' : 'document');
            $newFileName = generateFileName($fileName, 'chat_' . $this->userId);
            $targetPath  = $uploadDir . '/' . $newFileName;

            if (move_uploaded_file($tmpPath, $targetPath)) {
                $attachment = [
                    'file_name' => $fileName,
                    'file_path' => $newFileName,
                    'file_type' => $fileType,
                    'file_size' => $fileSize,
                ];
            } else {
                jsonResponse(['error' => 'Failed to upload attachment'], 500);
            }
        }

        if (empty($messageText) && !$attachment) {
            jsonResponse(['error' => 'Cannot send an empty message'], 400);
        }

        $newMsg = $this->chatModel->sendMessage($conversationId, $this->userId, $receiverId, $messageText, $attachment);

        // Update presence & clear typing state
        $this->chatModel->updatePresence($this->userId, null);

        jsonResponse([
            'success' => true,
            'message' => $newMsg,
        ]);
    }

    /**
     * POST /chat/typing
     * Signal typing indicator state.
     */
    public function sendTyping(): void {
        $raw   = file_get_contents('php://input');
        $body  = json_decode($raw, true) ?: $_POST;
        $targetUserId = (int)($body['target_user_id'] ?? 0);
        $isTyping     = (bool)($body['is_typing'] ?? false);

        $this->chatModel->updatePresence($this->userId, $isTyping ? $targetUserId : null);
        jsonResponse(['success' => true]);
    }

    /**
     * POST /chat/delete
     * Soft delete conversation.
     */
    public function deleteConversation(): void {
        $raw  = file_get_contents('php://input');
        $body = json_decode($raw, true) ?: $_POST;
        $conversationId = (int)($body['conversation_id'] ?? 0);

        if (!$conversationId) {
            jsonResponse(['error' => 'Invalid conversation ID'], 400);
        }

        $this->chatModel->deleteConversation($conversationId, $this->userId);
        jsonResponse(['success' => true, 'message' => 'Conversation deleted']);
    }

    /**
     * POST /chat/archive
     * Archive or unarchive conversation.
     */
    public function archiveConversation(): void {
        $raw  = file_get_contents('php://input');
        $body = json_decode($raw, true) ?: $_POST;
        $conversationId = (int)($body['conversation_id'] ?? 0);

        if (!$conversationId) {
            jsonResponse(['error' => 'Invalid conversation ID'], 400);
        }

        $isArchived = $this->chatModel->toggleArchiveConversation($conversationId, $this->userId);
        jsonResponse([
            'success'     => true,
            'is_archived' => $isArchived,
            'message'     => $isArchived ? 'Conversation archived' : 'Conversation unarchived',
        ]);
    }

    /**
     * GET /chat/unread-count
     * Returns total unread count for navbar.
     */
    public function getUnreadCount(): void {
        $this->chatModel->updatePresence($this->userId);
        $count = $this->chatModel->getTotalUnreadCount($this->userId);
        jsonResponse(['success' => true, 'unread_count' => $count]);
    }

    /**
     * GET /chat/download/{id}
     * Securely download chat attachment file.
     */
    public function downloadAttachment($attachmentId): void {
        $attId = (int)$attachmentId;
        $db    = Database::getInstance();
        $att   = $db->fetchOne(
            "SELECT a.*, m.sender_id, m.receiver_id
             FROM chat_attachments a
             JOIN chat_messages m ON a.message_id = m.id
             WHERE a.id = ?",
            [$attId]
        );

        if (!$att || ($att['sender_id'] != $this->userId && $att['receiver_id'] != $this->userId)) {
            http_response_code(403);
            die('Access denied.');
        }

        $fullPath = UPLOADS_PATH . '/chat/' . $att['file_path'];
        if (!file_exists($fullPath)) {
            http_response_code(404);
            die('File not found.');
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($att['file_name']) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($fullPath));
        readfile($fullPath);
        exit;
    }
}
