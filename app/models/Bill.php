<?php

class Bill extends Model
{
    public function currentForTenant(int $tenantId): ?array
    {
        $stmt = $this->db->prepare("SELECT b.*,
            COALESCE(SUM(p.amount), 0) AS paid_amount,
            GREATEST(b.amount - COALESCE(SUM(p.amount), 0), 0) AS remaining_amount
            FROM bills b
            LEFT JOIN payments p ON p.bill_id = b.id
            WHERE b.tenant_id = ?
              AND b.period_month = MONTH(CURDATE())
              AND b.period_year = YEAR(CURDATE())
            GROUP BY b.id
            LIMIT 1");
        $stmt->execute([$tenantId]);
        return $stmt->fetch() ?: null;
    }

    public function totalArrears(int $tenantId): float
    {
        $stmt = $this->db->prepare("SELECT COALESCE(SUM(GREATEST(b.amount - COALESCE(pay.paid_amount, 0), 0)), 0)
            FROM bills b
            LEFT JOIN (
                SELECT bill_id, SUM(amount) AS paid_amount
                FROM payments
                GROUP BY bill_id
            ) pay ON pay.bill_id = b.id
            WHERE b.tenant_id = ? AND b.status = 'belum_lunas'");
        $stmt->execute([$tenantId]);
        return (float) $stmt->fetchColumn();
    }

    public function totalArrearsAll(): float
    {
        $stmt = $this->db->query("SELECT COALESCE(SUM(GREATEST(b.amount - COALESCE(pay.paid_amount, 0), 0)), 0)
            FROM bills b
            JOIN tenants t ON t.id = b.tenant_id
            LEFT JOIN (
                SELECT bill_id, SUM(amount) AS paid_amount
                FROM payments
                GROUP BY bill_id
            ) pay ON pay.bill_id = b.id
            WHERE b.status = 'belum_lunas'
              AND t.status = 'aktif'");
        return (float) $stmt->fetchColumn();
    }

    public function arrearsReport(): array
    {
        $stmt = $this->db->query("SELECT
            t.id AS tenant_id,
            TRIM(CONCAT(COALESCE(u.nama_depan, ''), ' ', COALESCE(u.nama_belakang, ''))) AS full_name,
            r.room_number,
            COUNT(b.id) AS unpaid_bill_count,
            COALESCE(SUM(b.amount), 0) AS total_bill,
            COALESCE(SUM(COALESCE(pay.paid_amount, 0)), 0) AS paid_amount,
            COALESCE(SUM(GREATEST(b.amount - COALESCE(pay.paid_amount, 0), 0)), 0) AS remaining_amount
            FROM bills b
            JOIN tenants t ON t.id = b.tenant_id
            JOIN users u ON u.id = t.user_id
            JOIN rooms r ON r.id = t.room_id
            LEFT JOIN (
                SELECT bill_id, SUM(amount) AS paid_amount
                FROM payments
                GROUP BY bill_id
            ) pay ON pay.bill_id = b.id
            WHERE b.status = 'belum_lunas'
              AND t.status = 'aktif'
            GROUP BY t.id, full_name, r.room_number
            HAVING remaining_amount > 0
            ORDER BY remaining_amount DESC");
        return $stmt->fetchAll();
    }

    public function history(int $tenantId, ?int $month = null, ?int $year = null): array
    {
        $sql = "SELECT b.*, MAX(p.paid_at) AS paid_at, COALESCE(SUM(p.amount), 0) AS paid_amount
                FROM bills b
                LEFT JOIN payments p ON p.bill_id = b.id
                WHERE b.tenant_id = ?";
        $params = [$tenantId];
        if ($month) {
            $sql .= ' AND b.period_month = ?';
            $params[] = $month;
        }
        if ($year) {
            $sql .= ' AND b.period_year = ?';
            $params[] = $year;
        }
        $sql .= ' GROUP BY b.id ORDER BY b.period_year DESC, b.period_month DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function unpaidForTenant(int $tenantId): array
    {
        $stmt = $this->db->prepare("SELECT b.*,
            COALESCE(SUM(p.amount), 0) AS paid_amount,
            GREATEST(b.amount - COALESCE(SUM(p.amount), 0), 0) AS remaining_amount
            FROM bills b
            LEFT JOIN payments p ON p.bill_id = b.id
            WHERE b.tenant_id = ? AND b.status = 'belum_lunas'
            GROUP BY b.id
            HAVING remaining_amount > 0
            ORDER BY b.period_year ASC, b.period_month ASC");
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll();
    }

    public function payableBill(int $billId, int $tenantId): ?array
    {
        $stmt = $this->db->prepare("SELECT b.*,
            COALESCE(SUM(p.amount), 0) AS paid_amount,
            GREATEST(b.amount - COALESCE(SUM(p.amount), 0), 0) AS remaining_amount
            FROM bills b
            LEFT JOIN payments p ON p.bill_id = b.id
            WHERE b.id = ? AND b.tenant_id = ?
            GROUP BY b.id
            LIMIT 1");
        $stmt->execute([$billId, $tenantId]);
        return $stmt->fetch() ?: null;
    }

    public function payUnpaidForTenant(int $tenantId): int
    {
        $bills = $this->unpaidForTenant($tenantId);
        foreach ($bills as $bill) {
            $this->pay((int) $bill['id'], $tenantId, (float) $bill['remaining_amount']);
        }
        return count($bills);
    }

    public function pay(int $billId, int $tenantId, float $amount): bool
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare('SELECT amount FROM bills WHERE id = ? AND tenant_id = ? FOR UPDATE');
            $stmt->execute([$billId, $tenantId]);
            $bill = $stmt->fetch();
            if (!$bill) {
                throw new RuntimeException('Tagihan tidak ditemukan.');
            }

            $paidStmt = $this->db->prepare('SELECT COALESCE(SUM(amount), 0) FROM payments WHERE bill_id = ?');
            $paidStmt->execute([$billId]);
            $paid = (float) $paidStmt->fetchColumn();
            $remaining = max((float) $bill['amount'] - $paid, 0);
            if ($remaining <= 0) {
                throw new RuntimeException('Tagihan sudah lunas.');
            }
            if ($amount <= 0 || $amount > $remaining) {
                throw new RuntimeException('Nominal pembayaran tidak valid.');
            }

            $isFullPaid = ($paid + $amount) >= (float) $bill['amount'];
            $status = $isFullPaid ? 'lunas' : 'belum_lunas';
            $this->db->prepare('INSERT INTO payments (bill_id, tenant_id, amount, status) VALUES (?, ?, ?, ?)')->execute([$billId, $tenantId, $amount, $status]);
            if ($isFullPaid) {
                $this->db->prepare("UPDATE bills SET status = 'lunas' WHERE id = ? AND tenant_id = ?")->execute([$billId, $tenantId]);
            }
            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function overdue(): array
    {
        $stmt = $this->db->query("SELECT b.*, t.id AS tenant_id,
            TRIM(CONCAT(COALESCE(u.nama_depan, ''), ' ', COALESCE(u.nama_belakang, ''))) AS full_name,
            r.room_number,
            GREATEST(b.amount - COALESCE(pay.paid_amount, 0), 0) AS remaining_amount,
            DATEDIFF(CURDATE(), b.due_date) AS overdue_days
            FROM bills b
            JOIN tenants t ON t.id = b.tenant_id
            JOIN users u ON u.id = t.user_id
            JOIN rooms r ON r.id = t.room_id
            LEFT JOIN (
                SELECT bill_id, SUM(amount) AS paid_amount
                FROM payments
                GROUP BY bill_id
            ) pay ON pay.bill_id = b.id
            WHERE b.status = 'belum_lunas'
              AND b.due_date IS NOT NULL
              AND b.due_date < CURDATE()
              AND GREATEST(b.amount - COALESCE(pay.paid_amount, 0), 0) > 0
            ORDER BY b.due_date ASC");
        return $stmt->fetchAll();
    }
}
