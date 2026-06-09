<header class="page-header">
    <div>
        <p class="eyebrow">Riwayat Pembayaran</p>
        <h1>Rekam Jejak Transaksi</h1>
    </div>
</header>

<section class="toolbar">
    <select id="historyMonth">
        <option value="">Semua Bulan</option>
        <?php for ($m = 1; $m <= 12; $m++): ?>
            <option value="<?= $m ?>"><?= month_name($m) ?></option>
        <?php endfor; ?>
    </select>
    <select id="historyYear">
        <option value="">Semua Tahun</option>
        <?php for ($y = (int) date('Y') - 3; $y <= (int) date('Y') + 1; $y++): ?>
            <option value="<?= $y ?>"><?= $y ?></option>
        <?php endfor; ?>
    </select>
</section>

<div class="table-wrap">
    <table>
        <thead>
        <tr>
            <th>Keterangan</th>
            <th>Tanggal Pembayaran</th>
            <th>Periode</th>
            <th>Jumlah</th>
            <th>Status</th>
        </tr>
        </thead>
        <tbody id="historyRows">
        <?php foreach ($history as $row): ?>
            <tr>
                <td><?= e($row['description']) ?></td>
                <td><?= e($row['paid_at'] ?: '-') ?></td>
                <td><?= month_name((int) $row['period_month']) ?> <?= e($row['period_year']) ?></td>
                <td><?= rupiah($row['amount']) ?></td>
                <td><span class="badge <?= $row['status'] === 'lunas' ? 'green' : 'red' ?>"><?= strtoupper(str_replace('_', ' ', $row['status'])) ?></span></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
