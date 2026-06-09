<header class="page-header">
    <div>
        <p class="eyebrow">Dashboard Owner</p>
        <h1>Ringkasan Operasional Bulan Ini</h1>
    </div>
</header>

<section class="stats-grid">
    <article class="stat-card"><span>Pemasukan</span><strong><?= rupiah($incomeTotal) ?></strong><small>Bulan berjalan</small></article>
    <article class="stat-card"><span>Pengeluaran</span><strong><?= rupiah($expenseTotal) ?></strong><small>Bulan berjalan</small></article>
    <article class="stat-card"><span>Kamar Terisi</span><strong><?= (int) $roomStats['terisi'] ?></strong><small>Dari <?= (int) $roomStats['total'] ?> kamar</small></article>
    <article class="stat-card"><span>Kamar Kosong</span><strong><?= (int) $roomStats['kosong'] ?></strong><small>Siap disewakan</small></article>
    <article class="stat-card alert-stat"><span>Keluhan Pending</span><strong><?= $pendingComplaints ?></strong><small>Belum ditanggapi</small></article>
    <article class="stat-card alert-stat"><span>Sewa Jatuh Tempo</span><strong><?= count($dueTenants) ?></strong><small>Dalam 30 hari / lewat</small></article>
    <article class="stat-card alert-stat"><span>Tagihan Terlambat</span><strong><?= count($overdueBills) ?></strong><small>Belum lunas lewat due date</small></article>
    <article class="stat-card alert-stat"><span>Total Tunggakan</span><strong><?= rupiah($arrearsTotal) ?></strong><small>Sisa tagihan setelah DP</small></article>
</section>

<section class="split dashboard-alerts">
    <article class="panel">
        <h2>Daftar Tunggakan Penghuni</h2>
        <div class="table-wrap compact">
            <table>
                <thead><tr><th>Penghuni</th><th>Kamar</th><th>Tagihan</th><th>Sudah Dibayar</th><th>Sisa Tunggakan</th></tr></thead>
                <tbody>
                <?php if (!$arrearsReport): ?>
                    <tr><td colspan="5">Tidak ada tunggakan aktif.</td></tr>
                <?php endif; ?>
                <?php foreach ($arrearsReport as $row): ?>
                    <tr>
                        <td><?= e($row['full_name']) ?></td>
                        <td><?= e($row['room_number']) ?></td>
                        <td><?= (int) $row['unpaid_bill_count'] ?> tagihan / <?= rupiah($row['total_bill']) ?></td>
                        <td><?= rupiah($row['paid_amount']) ?></td>
                        <td><span class="badge red"><?= rupiah($row['remaining_amount']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>

    <article class="panel">
        <h2>Masa Sewa Jatuh Tempo</h2>
        <div class="table-wrap compact">
            <table>
                <thead><tr><th>Penghuni</th><th>Kamar</th><th>Akhir Sewa</th><th>Status</th></tr></thead>
                <tbody>
                <?php if (!$dueTenants): ?>
                    <tr><td colspan="4">Belum ada masa sewa yang jatuh tempo dalam 30 hari.</td></tr>
                <?php endif; ?>
                <?php foreach ($dueTenants as $row): ?>
                    <tr>
                        <td><?= e($row['full_name']) ?></td>
                        <td><?= e($row['room_number']) ?></td>
                        <td><?= e($row['end_date']) ?></td>
                        <td>
                            <?php if ((int) $row['days_left'] < 0): ?>
                                <span class="badge red">LEWAT <?= abs((int) $row['days_left']) ?> HARI</span>
                            <?php else: ?>
                                <span class="badge neutral"><?= (int) $row['days_left'] ?> HARI LAGI</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>

    <article class="panel">
        <h2>Tagihan Lewat Jatuh Tempo</h2>
        <div class="table-wrap compact">
            <table>
                <thead><tr><th>Penghuni</th><th>Kamar</th><th>Due Date</th><th>Nominal</th></tr></thead>
                <tbody>
                <?php if (!$overdueBills): ?>
                    <tr><td colspan="4">Tidak ada tagihan terlambat.</td></tr>
                <?php endif; ?>
                <?php foreach ($overdueBills as $row): ?>
                    <tr>
                        <td><?= e($row['full_name']) ?></td>
                        <td><?= e($row['room_number']) ?></td>
                        <td><span class="badge red"><?= e($row['due_date']) ?> / <?= (int) $row['overdue_days'] ?> HARI</span></td>
                        <td><?= rupiah($row['remaining_amount']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>
</section>

<section class="panel">
    <h2>Grafik Keuangan <?= date('Y') ?></h2>
    <canvas id="financeChart" height="110" data-chart='<?= json_encode($chart) ?>'></canvas>
</section>
