<header class="page-header">
    <div>
        <p class="eyebrow">Profil Penghuni</p>
        <h1>Informasi Pribadi & Detail Sewa</h1>
    </div>
</header>

<section class="split">
    <article class="panel">
        <h2>Data Sewa</h2>
        <dl class="detail-list">
            <dt>Nama</dt><dd><?= e($tenant['full_name']) ?></dd>
            <dt>No. Telepon</dt><dd><?= e($tenant['phone']) ?></dd>
            <dt>Kamar</dt><dd><?= e($tenant['room_number']) ?> - <?= e($tenant['type']) ?></dd>
            <dt>Harga Sewa</dt><dd><?= rupiah($tenant['price']) ?></dd>
            <dt>Tanggal Masuk</dt><dd><?= e($tenant['start_date']) ?></dd>
            <dt>Akhir Sewa</dt><dd><?= e($tenant['end_date'] ?? '-') ?></dd>
            <dt>Terakhir Bayar</dt><dd><?= e($lastPaid ?: '-') ?></dd>
        </dl>
    </article>
    <article class="panel">
        <h2>Edit Informasi</h2>
        <form class="form" method="post" action="<?= url('index.php?route=penghuni/updateProfile') ?>">
            <label>Nama lengkap<input name="full_name" value="<?= e($tenant['full_name']) ?>" required></label>
            <label>No. telepon<input name="phone" value="<?= e($tenant['phone']) ?>" required></label>
            <button class="btn primary" type="submit">Simpan Informasi</button>
        </form>
    </article>
    <article class="panel">
        <h2>Ubah Password</h2>
        <form class="form" method="post" action="<?= url('index.php?route=penghuni/updatePassword') ?>">
            <label>Password baru<input type="password" name="password" minlength="6" required></label>
            <label>Konfirmasi password<input type="password" name="confirm_password" minlength="6" required></label>
            <button class="btn" type="submit">Ubah Password</button>
        </form>
    </article>
    <article class="panel danger-zone">
        <h2>Hapus Akun</h2>
        <p class="muted">Gunakan jika Anda memutuskan berhenti menghuni. Kamar otomatis berubah menjadi kosong.</p>
        <form method="post" action="<?= url('index.php?route=penghuni/deactivate') ?>" data-confirm="Anda yakin ingin berhenti menghuni?">
            <button class="btn danger" type="submit">Hapus Akun</button>
        </form>
    </article>
</section>
