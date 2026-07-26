# Real-Time Student ↔ Recruiter Messaging Module — Documentation

This document provides a comprehensive overview of the **Real-Time Messaging System** implemented in the Training & Placement Management System (TPMS).

---

## 1. System Overview & Authorization Rules

The messaging system facilitates direct communication strictly between **Students** and **Company Recruiters**.

### Access Control Rules:
1. **Student Authorization**: A student can initiate or participate in a chat with a company recruiter **only after submitting an application** to at least one job posted by that company.
2. **Company Recruiter Authorization**: A recruiter can chat **only with student applicants** who have applied to their job postings.
3. **Role Restriction**: Other roles (e.g. Admin) cannot participate in direct student-company chats.

---

## 2. Database Schema

The system uses two dedicated MySQL tables: `messages` and `user_presence`.

### A. `messages` Table
Stores chat messages, file attachment metadata, and delivery/read statuses.

```sql
CREATE TABLE IF NOT EXISTS `messages` (
    `id`            INT           PRIMARY KEY AUTO_INCREMENT,
    `sender_id`     INT           NOT NULL,
    `receiver_id`   INT           NOT NULL,
    `job_id`        INT           NULL,
    `message`       TEXT          NULL,
    `file_path`     VARCHAR(255)  NULL,
    `file_name`     VARCHAR(255)  NULL,
    `file_type`     VARCHAR(50)   NULL,
    `file_size`     INT           NULL,
    `is_read`       TINYINT(1)    NOT NULL DEFAULT 0,
    `read_at`       DATETIME      NULL,
    `created_at`    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`sender_id`)   REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`receiver_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`job_id`)      REFERENCES `jobs`(`id`)  ON DELETE SET NULL,
    INDEX `idx_sender_receiver` (`sender_id`, `receiver_id`),
    INDEX `idx_receiver_read`   (`receiver_id`, `is_read`),
    INDEX `idx_created_at`      (`created_at`)
);
```

### B. `user_presence` Table
Tracks real-time user online activity and typing indicator status.

```sql
CREATE TABLE IF NOT EXISTS `user_presence` (
    `user_id`           INT        PRIMARY KEY,
    `last_activity`     DATETIME   NOT NULL,
    `typing_target_id`  INT        NULL,
    `typing_updated_at` DATETIME   NULL,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
);
```

---

## 3. Architecture & File Structure

The messaging system is built using the project's MVC pattern:

- **Model**: [`models/Message.php`](file:///c:/xampp/htdocs/Internship%20Project/New-TNP-Project/models/Message.php)
  - Manages database queries, application relationship checks (`canUsersChat`), fetching conversation lists, retrieving history, sending messages/files, and presence management.
- **Controller**: [`controllers/MessageController.php`](file:///c:/xampp/htdocs/Internship%20Project/New-TNP-Project/controllers/MessageController.php)
  - Handles page rendering, AJAX requests for conversation loading, history polling, message sending, typing updates, unread badge counts, and secure file downloads.
- **View**: [`views/messages/chat.php`](file:///c:/xampp/htdocs/Internship%20Project/New-TNP-Project/views/messages/chat.php)
  - Responsive glassmorphic layout featuring the left conversation sidebar, active chat header, scrollable message stream, attachment preview bar, and message input footer.
- **Assets**:
  - [`assets/js/chat.js`](file:///c:/xampp/htdocs/Internship%20Project/New-TNP-Project/assets/js/chat.js): Client-side messaging engine managing AJAX polling (every 2.5s), file selection, active partner switching, DOM rendering, typing timeouts, and badge updates.
  - [`assets/css/chat.css`](file:///c:/xampp/htdocs/Internship%20Project/New-TNP-Project/assets/css/chat.css): CSS styling for chat bubbles, online indicators, file attachment pills, and responsive mobile drawers.

---

## 4. Key Features

### 1. Real-Time AJAX Polling (No Page Refresh)
- The client engine (`TPMS_Chat`) polls `/messages/poll` every 2.5 seconds.
- Fetches new incoming messages (`last_id` parameter) and appends them dynamically without reloading the page.

### 2. Online Presence & Status Tracking
- Whenever a user interacts with the chat system, their timestamp in `user_presence` is updated.
- Users active within the last 60 seconds display a green online dot indicator and "Online" status.

### 3. Real-Time Typing Indicators
- Triggered on input events in the chat box.
- Sends an AJAX request to `/messages/typing` setting `is_typing = true` for 5 seconds.
- Displays an animated `typing...` prompt in the chat header and conversation list preview.

### 4. Read Receipts & Unread Badges
- When a user opens a conversation or receives new messages while in an active chat, messages are marked as read (`is_read = 1`, `read_at = NOW()`).
- Unread notification badges (`.chat-unread-badge`) update in real time in both the chat sidebar and top navbar.

### 5. Secure File Sharing (Resume / Images / Documents)
- Supports file uploads up to 5MB (`.pdf`, `.doc`, `.docx`, `.jpg`, `.jpeg`, `.png`, `.gif`, `.webp`, `.txt`, `.zip`).
- Images display inline preview thumbnails; documents render clean attachment download pills.
- Files are downloaded via secure endpoint `/messages/download/{id}` enforcing strict authorization checks.

### 6. Conversation Search
- Search input in sidebar filters conversations in real time by contact name, email, or branch/company subtitle.

---

## 5. API Routes Reference

| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/student/messages` | Student main chat interface page |
| `GET` | `/company/messages` | Recruiter main chat interface page |
| `GET` | `/messages/conversations` | AJAX: Fetch list of eligible chat partners with unread counts |
| `GET` | `/messages/history?partner_id={id}` | AJAX: Fetch message history & partner presence |
| `POST` | `/messages/send` | AJAX: Send text message or file attachment |
| `GET` | `/messages/poll?partner_id={id}&last_id={id}` | AJAX: Real-time message & presence polling endpoint |
| `POST` | `/messages/typing` | AJAX: Update user typing indicator |
| `GET` | `/messages/unread-count` | AJAX: Global unread message count for navbar badges |
| `GET` | `/messages/download/{id}` | Download attached chat file securely |

---

## 6. How to Test the Messaging System

1. **Student Setup**: Log in as a student (e.g. `student@tpms.com` / `Student@123`) and submit an application to a job.
2. **Open Chat**: Click the **Messages & Chat** icon in the top navbar or sidebar (`/student/messages`).
3. **Select Company**: The company whose job you applied to will appear in your conversation list.
4. **Recruiter Chat**: Open a separate browser tab / incognito window, log in as company recruiter (`company@tpms.com` / `Company@123`), and open `/company/messages`.
5. **Send & Receive**: Send messages, share resume/PDF files, observe online status dots, typing indicators, read receipts, and unread badges in real time!
