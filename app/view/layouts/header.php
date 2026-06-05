<?php $user = Auth::user(); ?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? APP_NAME) ?> - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
</head>
<body>
<div class="app-shell">
    <?php if ($user): ?>
        <aside class="sidebar">
            <div class="brand">KOSTRACK</div>
            <div class="muted small">Halo, <?= e($user['full_name']) ?></div>
            <nav>
                <?php if ($user['role'] === 'owner'): ?>
                    <a href="<?= url('index.php?route=owner/dashboard') ?>">Dashboard</a>
                    <a href="<?= url('index.php?route=owner/rooms') ?>">Data Kamar</a>
                    <a href="<?= url('index.php?route=owner/tenants') ?>">Penghuni</a>
                    <a href="<?= url('index.php?route=owner/finance') ?>">Keuangan</a>
                    <a href="<?= url('index.php?route=owner/complaints') ?>">Keluhan</a>
                    <a href="<?= url('index.php?route=owner/profile') ?>">Profil Admin</a>
                <?php else: ?>
                    <a href="<?= url('index.php?route=penghuni/dashboard') ?>">Dashboard</a>
                    <a href="<?= url('index.php?route=penghuni/payment') ?>">Pembayaran</a>
                    <a href="<?= url('index.php?route=penghuni/history') ?>">Riwayat Pembayaran</a>
                    <a href="<?= url('index.php?route=penghuni/complaints') ?>">Keluhan</a>
                    <a href="<?= url('index.php?route=penghuni/profile') ?>">Profil</a>
                <?php endif; ?>
                <a class="danger-link" href="<?= url('index.php?route=auth/logout') ?>">Logout</a>
            </nav>
        </aside>
    <?php endif; ?>
    <main class="<?= $user ? 'content' : 'auth-content' ?>">
        <?php foreach (['success', 'error', 'warning'] as $type): ?>
            <?php if ($msg = flash($type)): ?>
                <div class="alert <?= $type ?>"><?= e($msg) ?></div>
            <?php endif; ?>
        <?php endforeach; ?>
