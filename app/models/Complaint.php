<?php

class Complaint extends Model
{
    public function byTenant(int $tenantId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM complaints WHERE tenant_id = ? ORDER BY created_at DESC');
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll();
    }

    public function activeCount(int $tenantId): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM complaints WHERE tenant_id = ? AND status = 'pending'");
        $stmt->execute([$tenantId]);
        return (int) $stmt->fetchColumn();
    }

    public function create(int $tenantId, string $message): bool
    {
        $stmt = $this->db->prepare("INSERT INTO complaints (tenant_id, message, status) VALUES (?, ?, 'pending')");
        return $stmt->execute([$tenantId, $message]);
    }

    public function all(): array
    {
        $stmt = $this->db->query("SELECT c.*,
            TRIM(CONCAT(COALESCE(u.nama_depan, ''), ' ', COALESCE(u.nama_belakang, ''))) AS full_name,
            r.room_number
            FROM complaints c
            JOIN tenants t ON t.id = c.tenant_id
            JOIN users u ON u.id = t.user_id
            JOIN rooms r ON r.id = t.room_id
            ORDER BY c.status ASC, c.created_at DESC");
        return $stmt->fetchAll();
    }

    public function pendingCount(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM complaints WHERE status = 'pending'")->fetchColumn();
    }

    public function respond(int $id, string $response): bool
    {
        $stmt = $this->db->prepare("UPDATE complaints SET status = 'selesai', response = ?, responded_at = NOW() WHERE id = ?");
        return $stmt->execute([$response, $id]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM complaints WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
