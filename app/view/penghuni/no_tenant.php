<header class="page-header">
    <div>
        <p class="eyebrow">Panel Penghuni</p>
        <h1>Belum Ada Kamar Aktif</h1>
    </div>
</header>

<section class="panel">
    <h2>Akun Berhasil Masuk</h2>
    <p class="muted">
        Halo <?= e($user['full_name'] ?? 'Penghuni') ?>, akun Anda sudah aktif sebagai penghuni,
        tetapi belum terhubung dengan data kamar/sewa. Dashboard tagihan, riwayat pembayaran,
        dan keluhan baru bisa digunakan setelah owner memasangkan akun ini ke kamar.
    </p>
    <dl class="detail-list">
        <dt>Email</dt><dd><?= e($user['email'] ?? '-') ?></dd>
        <dt>No. Telepon</dt><dd><?= e($user['phone'] ?? '-') ?></dd>
        <dt>Status</dt><dd>Menunggu penempatan kamar</dd>
    </dl>
</section>
