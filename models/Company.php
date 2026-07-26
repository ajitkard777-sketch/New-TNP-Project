<?php
/**
 * TPMS - Company Model
 */
class Company {
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findById(int $id): ?array {
        return $this->db->fetchOne(
            "SELECT c.*, u.email, u.status as user_status
             FROM companies c JOIN users u ON c.user_id = u.id WHERE c.id = ?",
            [$id]
        );
    }

    public function findByUserId(int $userId): ?array {
        return $this->db->fetchOne(
            "SELECT c.*, u.email, u.status as user_status
             FROM companies c JOIN users u ON c.user_id = u.id WHERE c.user_id = ?",
            [$userId]
        );
    }

    /**
     * Get all companies with optional search + approval-status filter.
     * $status: 'approved' | 'pending' | 'rejected' | 'suspended' | '' (all)
     */
    public function getAll(int $offset = 0, int $limit = RECORDS_PER_PAGE, string $search = '', string $status = ''): array {
        [$where, $params] = $this->buildWhere($search, $status);
        $params[] = $limit;
        $params[] = $offset;
        return $this->db->fetchAll(
            "SELECT c.*, u.email, u.status as user_status
             FROM companies c JOIN users u ON c.user_id = u.id
             WHERE $where ORDER BY c.created_at DESC LIMIT ? OFFSET ?",
            $params
        );
    }

    public function count(string $search = '', string $status = ''): int {
        [$where, $params] = $this->buildWhere($search, $status);
        return (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM companies c JOIN users u ON c.user_id = u.id WHERE $where",
            $params
        );
    }

    /** Get companies by approval status — for admin tabbed view */
    public function getByApprovalStatus(string $status, string $search = ''): array {
        [$where, $params] = $this->buildWhere($search, $status);
        return $this->db->fetchAll(
            "SELECT c.*, u.email, u.status as user_status
             FROM companies c JOIN users u ON c.user_id = u.id
             WHERE $where ORDER BY c.created_at DESC",
            $params
        );
    }

    public function update(int $companyId, array $data): int {
        $fields = []; $values = [];
        foreach ($data as $k => $v) { $fields[] = "`{$k}` = ?"; $values[] = $v; }
        $values[] = $companyId;
        return $this->db->update("UPDATE companies SET " . implode(', ', $fields) . " WHERE id = ?", $values);
    }

    public function updateByUserId(int $userId, array $data): int {
        $fields = []; $values = [];
        foreach ($data as $k => $v) { $fields[] = "`{$k}` = ?"; $values[] = $v; }
        $values[] = $userId;
        return $this->db->update("UPDATE companies SET " . implode(', ', $fields) . " WHERE user_id = ?", $values);
    }

    /** Approve: set is_approved=1, clear rejected state, re-activate account */
    public function approve(int $companyId): void {
        $this->update($companyId, [
            'is_approved'      => 1,
            'is_rejected'      => 0,
            'rejection_reason' => null,
        ]);
        $company = $this->findById($companyId);
        if ($company) {
            $this->db->update("UPDATE users SET status = 'active' WHERE id = ?", [$company['user_id']]);
        }
    }

    /**
     * Reject: set is_approved=0, is_rejected=1, store reason, block account.
     */
    public function reject(int $companyId, string $reason = ''): void {
        $this->update($companyId, [
            'is_approved'      => 0,
            'is_rejected'      => 1,
            'rejection_reason' => $reason ?: null,
        ]);
        $company = $this->findById($companyId);
        if ($company) {
            $this->db->update("UPDATE users SET status = 'blocked' WHERE id = ?", [$company['user_id']]);
        }
    }

    /** Suspend: block user account (keep is_approved=1 so it shows in Verified tab) */
    public function suspend(int $companyId): void {
        $company = $this->findById($companyId);
        if ($company) {
            $this->db->update("UPDATE users SET status = 'blocked' WHERE id = ?", [$company['user_id']]);
        }
    }

    /** Unsuspend / re-activate blocked verified company */
    public function unsuspend(int $companyId): void {
        $company = $this->findById($companyId);
        if ($company) {
            $this->db->update("UPDATE users SET status = 'active' WHERE id = ?", [$company['user_id']]);
        }
    }

    /** Check if a mobile number is already used by another company */
    public function phoneExists(string $phone, int $excludeUserId = 0): bool {
        $sql    = "SELECT COUNT(*) FROM companies WHERE contact_phone = ?";
        $params = [$phone];
        if ($excludeUserId) { $sql .= " AND user_id != ?"; $params[] = $excludeUserId; }
        return (bool)$this->db->fetchColumn($sql, $params);
    }

    public function getApprovedCount(): int {
        return (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM companies c JOIN users u ON c.user_id = u.id WHERE c.is_approved = 1 AND COALESCE(c.is_rejected,0)=0 AND u.status='active'"
        );
    }

    public function getTotalCount(): int {
        return (int)$this->db->fetchColumn("SELECT COUNT(*) FROM companies");
    }

    public function getPendingCount(): int {
        return (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM companies WHERE is_approved = 0 AND COALESCE(is_rejected,0) = 0"
        );
    }

    public function getRejectedCount(): int {
        return (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM companies WHERE COALESCE(is_rejected,0) = 1"
        );
    }

    public function getSuspendedCount(): int {
        return (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM companies c JOIN users u ON c.user_id = u.id
             WHERE c.is_approved = 1 AND u.status = 'blocked'"
        );
    }

    /** Build WHERE clause + params for status + search filters */
    private function buildWhere(string $search, string $status): array {
        $params = [];
        $where  = '1=1';
        if ($search) {
            $where  .= " AND (c.company_name LIKE ? OR u.email LIKE ? OR c.contact_person LIKE ?)";
            $params  = ["%$search%", "%$search%", "%$search%"];
        }
        switch ($status) {
            case 'approved':
                $where .= " AND c.is_approved = 1 AND COALESCE(c.is_rejected,0) = 0 AND u.status = 'active'";
                break;
            case 'pending':
                $where .= " AND c.is_approved = 0 AND COALESCE(c.is_rejected,0) = 0";
                break;
            case 'rejected':
                $where .= " AND COALESCE(c.is_rejected,0) = 1";
                break;
            case 'suspended':
                $where .= " AND c.is_approved = 1 AND u.status = 'blocked'";
                break;
        }
        return [$where, $params];
    }
}
