<?php

class Payment extends Model
{
    public function income(int $month, int $year, string $search = ''): array
    {
        $sql = "SELECT p.*, b.description, b.period_month, b.period_year,
                TRIM(CONCAT(COALESCE(u.nama_depan, ''), ' ', COALESCE(u.nama_belakang, ''))) AS full_name,
                r.room_number
                FROM payments p
                JOIN bills b ON b.id = p.bill_id
                JOIN tenants t ON t.id = p.tenant_id
                JOIN users u ON u.id = t.user_id
                JOIN rooms r ON r.id = t.room_id
                WHERE MONTH(p.paid_at) = ? AND YEAR(p.paid_at) = ?";
        $params = [$month, $year];
        if ($search !== '') {
            $sql .= " AND (TRIM(CONCAT(COALESCE(u.nama_depan, ''), ' ', COALESCE(u.nama_belakang, ''))) LIKE ? OR DATE(p.paid_at) LIKE ? OR r.room_number LIKE ?)";
            $term = '%' . $search . '%';
            array_push($params, $term, $term, $term);
        }
        $sql .= ' ORDER BY p.paid_at DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function monthlyChart(int $year): array
    {
        $stmt = $this->db->prepare("SELECT MONTH(paid_at) AS month, SUM(amount) AS total FROM payments WHERE YEAR(paid_at) = ? GROUP BY MONTH(paid_at)");
        $stmt->execute([$year]);
        return $stmt->fetchAll();
    }
}
