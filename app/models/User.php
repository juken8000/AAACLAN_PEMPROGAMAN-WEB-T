<?php

class User extends Model
{
    private string $selectFields = "id, nama_depan, nama_belakang, no_hp, email, password, role, is_active,
        TRIM(CONCAT(COALESCE(nama_depan, ''), ' ', COALESCE(nama_belakang, ''))) AS full_name,
        COALESCE(NULLIF(TRIM(no_hp), ''), '-') AS phone";

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT {$this->selectFields} FROM users WHERE email = ? AND is_active = 1 LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch() ?: null;
        return $user ? $this->normalizeUser($user) : null;
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT {$this->selectFields} FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $user = $stmt->fetch() ?: null;
        return $user ? $this->normalizeUser($user) : null;
    }

    public function create(array $data): int
    {
        if ($this->emailExists($data['email'])) {
            throw new RuntimeException('Email sudah terdaftar.');
        }

        $stmt = $this->db->prepare('INSERT INTO users (nama_depan, nama_belakang, no_hp, role, email, password, is_active) VALUES (?, ?, ?, ?, ?, ?, 1)');
        $stmt->execute([
            $data['nama_depan'],
            $data['nama_belakang'],
            $data['no_hp'],
            $this->normalizeRole($data['role']),
            $data['email'],
            password_hash($data['password'], PASSWORD_DEFAULT),
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function emailExists(string $email): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM users WHERE email = ?');
        $stmt->execute([$email]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function updateProfile(int $id, string $fullName, string $phone): bool
    {
        [$firstName, $lastName] = $this->splitName($fullName);
        $stmt = $this->db->prepare('UPDATE users SET nama_depan = ?, nama_belakang = ?, no_hp = ? WHERE id = ?');
        return $stmt->execute([$firstName, $lastName, $phone, $id]);
    }

    public function updatePassword(int $id, string $password): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET password = ? WHERE id = ?');
        return $stmt->execute([password_hash($password, PASSWORD_DEFAULT), $id]);
    }

    public function deactivate(int $id): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET is_active = 0 WHERE id = ?');
        return $stmt->execute([$id]);
    }

    private function splitName(string $fullName): array
    {
        $parts = preg_split('/\s+/', trim($fullName), 2);
        return [$parts[0] ?? '', $parts[1] ?? ''];
    }

    private function normalizeUser(array $user): array
    {
        $user['role'] = $this->normalizeRole($user['role'] ?? '');
        if (($user['full_name'] ?? '') === '') {
            $user['full_name'] = $user['email'] ?? 'Pengguna';
        }
        $user['phone'] = !empty($user['phone']) ? $user['phone'] : (!empty($user['no_hp']) ? $user['no_hp'] : '-');
        $user['username'] = $user['email'];
        return $user;
    }

    private function normalizeRole(string $role): string
    {
        return match (strtolower($role)) {
            'pemilik', 'admin', 'owner' => 'owner',
            default => 'penghuni',
        };
    }
}
