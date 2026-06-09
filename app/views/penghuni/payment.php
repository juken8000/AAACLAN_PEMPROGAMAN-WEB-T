<header class="page-header">
    <div>
        <p class="eyebrow">Pembayaran</p>
        <h1>Kelola Tagihan dan Pembayaran Kost</h1>
    </div>
</header>

<?php if (!$bill): ?>
    <section class="panel">
        <h2>Tidak Ada Tagihan Aktif</h2>
        <p class="muted">Semua tagihan Anda sudah lunas. Gunakan tombol Tambah Masa Sewa di dashboard jika ingin memperpanjang sewa untuk bulan berikutnya.</p>
        <a class="btn primary" href="<?= url('index.php?route=penghuni/dashboard') ?>">Kembali ke Dashboard</a>
    </section>
<?php else: ?>
    <section class="payment-shell">
        <form class="payment-card" method="post" action="<?= url('index.php?route=penghuni/processPayment') ?>">
            <input type="hidden" name="bill_id" value="<?= (int) $bill['id'] ?>">
            <input type="hidden" name="payment_channel" value="qris">

            <div class="payment-hero">
                <span>Tagihan <?= $bill['period_month'] == date('n') && $bill['period_year'] == date('Y') ? 'Bulan Ini' : 'Sewa' ?></span>
                <strong><?= rupiah($bill['remaining_amount']) ?></strong>
                <small>Periode: <?= month_name((int) $bill['period_month']) ?> <?= e($bill['period_year']) ?> - Kamar <?= e($tenant['room_number']) ?></small>
            </div>

            <dl class="payment-detail">
                <dt>Nomor Kamar</dt><dd><?= e($tenant['room_number']) ?> (<?= e($tenant['type']) ?>)</dd>
                <dt>Periode</dt><dd><?= month_name((int) $bill['period_month']) ?> <?= e($bill['period_year']) ?></dd>
                <dt>Jatuh Tempo</dt><dd class="text-red"><?= e($bill['due_date'] ?: '-') ?></dd>
                <dt>Status</dt><dd><span class="badge <?= $bill['status'] === 'lunas' ? 'green' : 'red' ?>"><?= strtoupper(str_replace('_', ' ', $bill['status'])) ?></span></dd>
                <dt>Jumlah Tagihan</dt><dd class="payment-amount"><?= rupiah($bill['amount']) ?></dd>
                <dt>Sudah Dibayar</dt><dd><?= rupiah($bill['paid_amount']) ?></dd>
                <dt>Sisa Bayar</dt><dd class="payment-amount"><?= rupiah($bill['remaining_amount']) ?></dd>
            </dl>

            <?php if (count($unpaidBills) > 1): ?>
                <label>Pilih tagihan lain
                    <select data-payment-jump>
                        <?php foreach ($unpaidBills as $row): ?>
                            <option value="<?= (int) $row['id'] ?>" <?= (int) $row['id'] === (int) $bill['id'] ? 'selected' : '' ?>>
                                <?= month_name((int) $row['period_month']) ?> <?= e($row['period_year']) ?> - <?= rupiah($row['remaining_amount']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
            <?php endif; ?>

            <div class="payment-methods">
                <h2>Metode Pembayaran</h2>
                <label class="payment-option">
                    <input type="radio" name="payment_method" value="full" checked>
                    <span>
                        <strong>Bayar Lunas</strong>
                        <small><?= rupiah($bill['remaining_amount']) ?></small>
                    </span>
                </label>
                <label class="payment-option">
                    <input type="radio" name="payment_method" value="dp">
                    <span>
                        <strong>Bayar Uang Muka (DP)</strong>
                        <small>Minimum Rp500.000</small>
                    </span>
                </label>
                <label class="dp-input" id="dpInputWrap">Nominal DP
                    <input name="dp_amount" inputmode="numeric" data-numeric placeholder="500000">
                </label>
            </div>

            <div class="qris-box">
                <h2><span class="qris-icon">▦</span> Scan QRIS untuk Bayar</h2>
                <p>Simulasi pembayaran digital</p>
                <div class="qris-code" aria-label="QRIS simulasi">
                    <?php for ($i = 1; $i <= 49; $i++): ?>
                        <span class="<?= in_array($i, [1,2,3,5,6,7,8,10,12,14,15,16,17,19,20,22,24,26,28,29,31,33,35,36,37,38,40,42,43,44,46,47,49], true) ? 'filled' : '' ?>"></span>
                    <?php endfor; ?>
                </div>
                <small>QRIS · Rantauln Kost</small>
            </div>

            <div class="payment-actions">
                <button class="btn primary" type="submit">Lakukan Pembayaran</button>
                <a class="btn ghost" href="<?= url('index.php?route=penghuni/dashboard') ?>">Batalkan</a>
            </div>
        </form>
    </section>
<?php endif; ?>
