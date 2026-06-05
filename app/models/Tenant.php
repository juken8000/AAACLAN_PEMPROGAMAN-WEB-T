<?php

class Tenant extends Model
{
    public function activeByUser(int $userId): ?array
    {
        $stmt = $this->db->prepare("SELECT t.*, r.room_number, r.type, r.price,
            TRIM(CONCAT(COALESCE(u.nama_depan, ''), ' ', COALESCE(u.nama_belakang, ''))) AS full_name,
            u.no_hp AS phone
            FROM tenants t
            JOIN rooms r ON r.id = t.room_id
            JOIN users u ON u.id = t.user_id
            WHERE t.user_id = ? AND t.status = 'aktif'
            LIMIT 1");
        $stmt->execute([$userId]);
        return $stmt->fetch() ?: null;
    }

    public function all(): array
    {
        $stmt = $this->db->query("SELECT t.*,
            TRIM(CONCAT(COALESCE(u.nama_depan, ''), ' ', COALESCE(u.nama_belakang, ''))) AS full_name,
            u.no_hp AS phone,
            r.room_number, r.type,
            COALESCE(ar.remaining_amount, 0) AS arrears,
            TIMESTAMPDIFF(MONTH, t.start_date, COALESCE(t.end_date, CURDATE())) AS months_rented
            FROM tenants t
            JOIN users u ON u.id = t.user_id
            JOIN rooms r ON r.id = t.room_id
            LEFT JOIN (
                SELECT b.tenant_id, SUM(GREATEST(b.amount - COALESCE(pay.paid_amount, 0), 0)) AS remaining_amount
                FROM bills b
                LEFT JOIN (
                    SELECT bill_id, SUM(amount) AS paid_amount
                    FROM payments
                    GROUP BY bill_id
                ) pay ON pay.bill_id = b.id
                WHERE b.status = 'belum_lunas'
                GROUP BY b.tenant_id
            ) ar ON ar.tenant_id = t.id
            ORDER BY t.status ASC, t.start_date DESC");
        return $stmt->fetchAll();
    }

    public function createActive(int $userId, int $roomId, int $leaseMonths = 1): int
    {
        $leaseMonths = max(1, min(60, $leaseMonths));
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("SELECT id, price, status FROM rooms WHERE id = ? FOR UPDATE");
            $stmt->execute([$roomId]);
            $room = $stmt->fetch();
            if (!$room) {
                throw new RuntimeException('Kamar tidak ditemukan.');
            }
            if ($room['status'] !== 'kosong') {
                throw new RuntimeException('Kamar yang dipilih sudah terisi.');
            }

            $this->db->prepare("INSERT INTO tenants (user_id, room_id, start_date, end_date, status)
                VALUES (?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL ? MONTH), 'aktif')")
                ->execute([$userId, $roomId, $leaseMonths]);
            $tenantId = (int) $this->db->lastInsertId();
            $amount = (float) $room['price'] * $leaseMonths;
            $description = $leaseMonths === 1
                ? 'Tagihan sewa bulan ini'
                : 'Tagihan sewa awal ' . $leaseMonths . ' bulan';

            $this->db->prepare("UPDATE rooms SET status = 'terisi' WHERE id = ?")->execute([$roomId]);
            $this->db->prepare("INSERT INTO bills (tenant_id, period_month, period_year, description, amount, status, due_date)
                VALUES (?, MONTH(CURDATE()), YEAR(CURDATE()), ?, ?, 'belum_lunas', DATE_ADD(CURDATE(), INTERVAL 7 DAY))")
                ->execute([$tenantId, $description, $amount]);

            $this->db->commit();
            return $tenantId;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function checkout(int $tenantId): bool
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare('SELECT room_id, user_id FROM tenants WHERE id = ? AND status = "aktif" FOR UPDATE');
            $stmt->execute([$tenantId]);
            $tenant = $stmt->fetch();
            if (!$tenant) {
                throw new RuntimeException('Penghuni aktif tidak ditemukan.');
            }

            $this->db->prepare("UPDATE tenants SET status = 'keluar', end_date = CURDATE() WHERE id = ?")->execute([$tenantId]);
            $this->db->prepare("UPDATE rooms SET status = 'kosong' WHERE id = ?")->execute([$tenant['room_id']]);
            $this->db->prepare('UPDATE users SET is_active = 0 WHERE id = ?')->execute([$tenant['user_id']]);
            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function extendLease(int $tenantId, int $months = 1): int
    {
        $months = max(1, min(12, $months));
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("SELECT t.*, r.price
                FROM tenants t
                JOIN rooms r ON r.id = t.room_id
                WHERE t.id = ? AND t.status = 'aktif'
                FOR UPDATE");
            $stmt->execute([$tenantId]);
            $tenant = $stmt->fetch();
            if (!$tenant) {
                throw new RuntimeException('Data sewa aktif tidak ditemukan.');
            }

            $baseDate = $tenant['end_date'] && strtotime($tenant['end_date']) > time()
                ? $tenant['end_date']
                : date('Y-m-d');
            $startPeriodDate = date('Y-m-d', strtotime($baseDate . ' +1 day'));
            $newEndDate = date('Y-m-d', strtotime($baseDate . ' +' . $months . ' month'));
            $periodMonth = (int) date('n', strtotime($startPeriodDate));
            $periodYear = (int) date('Y', strtotime($startPeriodDate));
            $endPeriodMonth = (int) date('n', strtotime($newEndDate));
            $endPeriodYear = (int) date('Y', strtotime($newEndDate));
            $description = $months === 1
                ? 'Perpanjangan sewa ' . month_name($periodMonth) . ' ' . $periodYear
                : 'Perpanjangan sewa ' . $months . ' bulan (' . month_name($periodMonth) . ' ' . $periodYear . ' - ' . month_name($endPeriodMonth) . ' ' . $endPeriodYear . ')';
            $amount = (float) $tenant['price'] * $months;

            $this->db->prepare('UPDATE tenants SET end_date = ? WHERE id = ?')->execute([$newEndDate, $tenantId]);
            $billStmt = $this->db->prepare("INSERT INTO bills (tenant_id, period_month, period_year, description, amount, status, due_date)
                VALUES (?, ?, ?, ?, ?, 'belum_lunas', DATE_ADD(CURDATE(), INTERVAL 7 DAY))
                ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id), description = VALUES(description), amount = amount + VALUES(amount), status = 'belum_lunas'");
            $billStmt->execute([$tenantId, $periodMonth, $periodYear, $description, $amount]);
            $billId = (int) $this->db->lastInsertId();

            $this->db->commit();
            return $billId;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function dueSoon(int $days = 30): array
    {
        $stmt = $this->db->prepare("SELECT t.*,
            TRIM(CONCAT(COALESCE(u.nama_depan, ''), ' ', COALESCE(u.nama_belakang, ''))) AS full_name,
            u.no_hp AS phone,
            r.room_number,
            DATEDIFF(t.end_date, CURDATE()) AS days_left
            FROM tenants t
            JOIN users u ON u.id = t.user_id
            JOIN rooms r ON r.id = t.room_id
            WHERE t.status = 'aktif'
              AND t.end_date IS NOT NULL
              AND t.end_date <= DATE_ADD(CURDATE(), INTERVAL ? DAY)
            ORDER BY t.end_date ASC");
        $stmt->execute([$days]);
        return $stmt->fetchAll();
    }
}
