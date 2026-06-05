<?php

function url(string $path = ''): string
{
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}

function redirect(string $route): void
{
    header('Location: ' . url('index.php?route=' . $route));
    exit;
}

function rupiah($number): string
{
    return 'Rp' . number_format((float) $number, 2, ',', '.');
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function month_name(int $month): string
{
    $names = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    return $names[$month] ?? '-';
}

function require_number(string $value, string $field = 'Nominal'): int
{
    if (!preg_match('/^[0-9]+$/', $value)) {
        throw new InvalidArgumentException($field . ' hanya boleh berisi angka.');
    }

    return (int) $value;
}

function flash(?string $key = null, ?string $message = null): ?string
{
    if ($key !== null && $message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }

    if ($key !== null && isset($_SESSION['flash'][$key])) {
        $value = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $value;
    }

    return null;
}
