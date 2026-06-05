<header class="page-header">
    <div>
        <p class="eyebrow">Dashboard Penghuni</p>
        <h1>Ringkasan Sewa Kamar <?= e($tenant['room_number']) ?></h1>
    </div>
</header>

<section class="stats-grid">
    <article class="stat-card">
        <span>Tagihan Bulan Ini</span>
        <strong><?= $currentBill ? rupiah($currentBill['amount']) : rupiah(0) ?></strong>
        <small><?= $currentBill ? month_name((int) $currentBill['period_month']) . ' ' . e($currentBill['period_year']) : 'Belum ada tagihan' ?></small>
    </article>
    <article class="stat-card">
        <span>Status Pembayaran</span>
        <strong class="<?= ($currentBill['status'] ?? 'lunas') === 'lunas' ? 'text-green' : 'text-red' ?>"><?= strtoupper(str_replace('_', ' ', $currentBill['status'] ?? 'lunas')) ?></strong>
        <small>Data ditarik dari tagihan aktif</small>
    </article>
    <article class="stat-card">
        <span>Keluhan Aktif</span>
        <strong><?= $activeComplaints ?></strong>
        <small>Keluhan berstatus pending</small>
    </article>
    <article class="stat-card">
        <span>Total Tunggakan</span>
        <strong><?= rupiah($arrears) ?></strong>
        <small>Akumulasi tagihan belum lunas</small>
    </article>
</section>

<section class="toolbar">
    <?php if ($arrears > 0): ?>
        <form method="post" action="<?= url('index.php?route=penghuni/payArrears') ?>">
            <button class="btn primary" type="submit">Bayar Tagihan</button>
        </form>
    <?php else: ?>
        <form method="post" action="<?= url('index.php?route=penghuni/payArrears') ?>">
            <button class="btn" type="submit">Bayar Tunggakan</button>
        </form>
    <?php endif; ?>
    <button class="btn primary" data-open-modal="extendLeaseModal">Tambah Masa Sewa</button>
    <button class="btn" data-open-modal="complaintModal">Ajukan Keluhan</button>
    <a class="btn" href="<?= url('index.php?route=penghuni/history') ?>">Lihat Riwayat</a>
</section>

<dialog id="extendLeaseModal" class="modal">
    <form method="post" action="<?= url('index.php?route=penghuni/extendLease') ?>">
        <h2>Tambah Masa Sewa</h2>
        <p class="muted">Perpanjangan akan membuat tagihan baru dan memperbarui tanggal akhir sewa.</p>
        <label>Lama perpanjangan
            <select name="months" required>
                <option value="1">1 bulan</option>
                <option value="3">3 bulan</option>
                <option value="6">6 bulan</option>
                <option value="12">12 bulan</option>
            </select>
        </label>
        <div class="modal-actions">
            <button class="btn ghost" type="button" data-close-modal>Tutup</button>
            <button class="btn primary" type="submit">Lanjut ke Pembayaran</button>
        </div>
    </form>
</dialog>

<dialog id="complaintModal" class="modal">
    <form method="post" action="<?= url('index.php?route=penghuni/storeComplaint') ?>">
        <h2>Ajukan Keluhan</h2>
        <label>Detail masalah
            <textarea name="message" rows="5" required></textarea>
        </label>
        <div class="modal-actions">
            <button class="btn ghost" type="button" data-close-modal>Tutup</button>
            <button class="btn primary" type="submit">Kirim</button>
        </div>
    </form>
</dialog>
