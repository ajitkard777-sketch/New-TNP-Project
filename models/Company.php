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
        return $this->db->fetchOne("SELECT c.*, u.email, u.status as user_status FROM companies c JOIN users u ON c.user_id = u.id WHERE c.id = ?", [$id]);
    }

    public function findByUserId(int $userId): ?array {
        return $this->db->fetchOne("SELECT c.*, u.email, u.status as user_status FROM companies c JOIN users u ON c.user_id = u.id WHERE c.user_id = ?", [$userId]);
    }

    public function getAll(int $offset = 0, int $limit = RECORDS_PER_PAGE, string $search = '', string $status = ''): array {
        $params = []; $where = "1=1";
        if ($search) { $where .= " AND (c.company_name LIKE ? OR u.email LIKE ?)"; $params = array_merge($params, ["%$search%", "%$search%"]); }
        if ($status) { $where .= " AND c.is_approved = ?"; $params[] = $status === 'approved' ? 1 : 0; }
        $params[] = $limit; $params[] = $offset;
        return $this->db->fetchAll("SELECT c.*, u.email, u.status as user_status FROM companies c JOIN users u ON c.user_id = u.id WHERE $where ORDER BY c.created_at DESC LIMIT ? OFFSET ?", $params);
    }

    public function count(string $search = '', string $status = ''): int {
        $params = []; $where = "1=1";
        if ($search) { $where .= " AND (c.company_name LIKE ? OR u.email LIKE ?)"; $params = array_merge($params, ["%$search%", "%$search%"]); }
        if ($status) { $where .= " AND c.is_approved = ?"; $params[] = $status === 'approved' ? 1 : 0; }
        return (int)$this->db->fetchColumn("SELECT COUNT(*) FROM companies c JOIN users u ON c.user_id = u.id WHERE $where", $params);
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

    public function approve(int $companyId): void {
        $this->update($companyId, ['is_approved' => 1]);
    }

    public function reject(int $companyId): void {
        $this->update($companyId, ['is_approved' => 0]);
    }

    public function getApprovedCount(): int {
        return (int)$this->db->fetchColumn("SELECT COUNT(*) FROM companies WHERE is_approved = 1");
    }

    public function getTotalCount(): int {
        return (int)$this->db->fetchColumn("SELECT COUNT(*) FROM companies");
    }

    public function getPendingCount(): int {
        return (int)$this->db->fetchColumn("SELECT COUNT(*) FROM companies WHERE is_approved = 0");
    }
}
