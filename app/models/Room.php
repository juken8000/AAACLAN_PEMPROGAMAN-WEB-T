<?php

class Room extends Model
{
    public function all(string $search = ''): array
    {
        $sql = "SELECT r.*, TRIM(CONCAT(COALESCE(u.nama_depan, ''), ' ', COALESCE(u.nama_belakang, ''))) AS tenant_name
                FROM rooms r
                LEFT JOIN tenants t ON t.room_id = r.id AND t.status = 'aktif'
                LEFT JOIN users u ON u.id = t.user_id";
        $params = [];
        if ($search !== '') {
            $sql .= " WHERE r.room_number LIKE ? OR r.type LIKE ? OR TRIM(CONCAT(COALESCE(u.nama_depan, ''), ' ', COALESCE(u.nama_belakang, ''))) LIKE ?";
            $term = '%' . $search . '%';
            $params = [$term, $term, $term];
        }
        $sql .= ' ORDER BY r.room_number ASC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function available(): array
    {
        $stmt = $this->db->query("SELECT * FROM rooms WHERE status = 'kosong' ORDER BY room_number ASC");
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM rooms WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare('INSERT INTO rooms (room_number, type, price, image) VALUES (?, ?, ?, ?)');
        return $stmt->execute([$data['room_number'], $data['type'], $data['price'], $data['image']]);
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare('UPDATE rooms SET room_number = ?, type = ?, price = ?, image = COALESCE(?, image) WHERE id = ?');
        return $stmt->execute([$data['room_number'], $data['type'], $data['price'], $data['image'], $id]);
    }

    public function delete(int $id): bool
    {
        $room = $this->find($id);
        if ($room && $room['status'] === 'terisi') {
            throw new RuntimeException('Tidak bisa menghapus kamar yang sedang terisi!');
        }
        $stmt = $this->db->prepare('DELETE FROM rooms WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function stats(): array
    {
        $stmt = $this->db->query("SELECT
            SUM(status = 'terisi') AS terisi,
            SUM(status = 'kosong') AS kosong,
            COUNT(*) AS total
            FROM rooms");
        return $stmt->fetch() ?: ['terisi' => 0, 'kosong' => 0, 'total' => 0];
    }

    public function setStatus(int $roomId, string $status): bool
    {
        $stmt = $this->db->prepare('UPDATE rooms SET status = ? WHERE id = ?');
        return $stmt->execute([$status, $roomId]);
    }
}
