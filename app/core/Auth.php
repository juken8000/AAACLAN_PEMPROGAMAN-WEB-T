<?php

class Auth
{
    public static function check(): bool
    {
        return isset($_SESSION['user']);
    }

    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function requireRole(string $role): void
    {
        self::guardTimeout();
        if (!self::check()) {
            redirect('auth/login');
        }
        if (($_SESSION['user']['role'] ?? '') !== $role) {
            redirect($_SESSION['user']['role'] === 'owner' ? 'owner/dashboard' : 'penghuni/dashboard');
        }
    }

    public static function guardTimeout(): void
    {
        if (!isset($_SESSION['user'])) {
            return;
        }

        $last = $_SESSION['last_activity'] ?? time();
        if (time() - $last > SESSION_TIMEOUT) {
            session_unset();
            session_destroy();
            session_start();
            flash('warning', 'Session berakhir karena tidak ada aktivitas lebih dari 30 menit. Silakan login kembali.');
            redirect('auth/login');
        }

        $_SESSION['last_activity'] = time();
    }

    public static function login(array $user): void
    {
        $_SESSION['user'] = [
            'id' => $user['id'],
            'username' => $user['email'],
            'email' => $user['email'],
            'role' => $user['role'],
            'full_name' => $user['full_name'],
        ];
        $_SESSION['last_activity'] = time();
    }

    public static function logout(): void
    {
        session_unset();
        session_destroy();
        session_start();
        flash('success', 'Anda berhasil logout.');
        redirect('auth/login');
    }
}
