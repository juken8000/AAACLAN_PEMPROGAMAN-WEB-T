<header class="page-header">
    <div>
        <p class="eyebrow">Manajemen dan Laporan Keuangan</p>
        <h1>Rekap Keuangan</h1>
    </div>
    <button class="btn primary" data-open-modal="expenseModal">Tambah Pengeluaran</button>
</header>

<section class="toolbar">
    <select id="financeMonth">
        <?php for ($m = 1; $m <= 12; $m++): ?>
            <option value="<?= $m ?>" <?= $m === $month ? 'selected' : '' ?>><?= month_name($m) ?></option>
        <?php endfor; ?>
    </select>
    <select id="financeYear">
        <?php for ($y = (int) date('Y') - 3; $y <= (int) date('Y') + 1; $y++): ?>
            <option value="<?= $y ?>" <?= $y === $year ? 'selected' : '' ?>><?= $y ?></option>
        <?php endfor; ?>
    </select>
    <input id="financeSearch" placeholder="Cari nama penghuni, kamar, atau tanggal...">
</section>

<section class="split">
    <article class="panel">
        <h2>Daftar Pemasukan</h2>
        <div class="table-wrap compact">
            <table>
                <thead><tr><th>Penghuni</th><th>Tanggal</th><th>Periode</th><th>Jumlah</th></tr></thead>
                <tbody id="incomeRows">
                <?php foreach ($income as $row): ?>
                    <tr><td><?= e($row['full_name']) ?> / <?= e($row['room_number']) ?></td><td><?= e($row['paid_at']) ?></td><td><?= month_name((int) $row['period_month']) ?> <?= e($row['period_year']) ?></td><td><?= rupiah($row['amount']) ?></td></tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot><tr><th colspan="3">Total Pemasukan</th><th id="incomeTotal"><?= rupiah($incomeTotal) ?></th></tr></tfoot>
            </table>
        </div>
    </article>
    <article class="panel">
        <h2>Pengeluaran Operasional</h2>
        <div class="table-wrap compact">
            <table>
                <thead><tr><th>Tanggal</th><th>Keterangan</th><th>Nominal</th><th>Aksi</th></tr></thead>
                <tbody id="expenseRows">
                <?php foreach ($expenses as $row): ?>
                    <tr>
                        <td><?= e($row['expense_date']) ?></td><td><?= e($row['description']) ?></td><td><?= rupiah($row['amount']) ?></td>
                        <td><form method="post" action="<?= url('index.php?route=owner/deleteExpense') ?>" data-confirm="Hapus pengeluaran ini?"><input type="hidden" name="id" value="<?= (int) $row['id'] ?>"><button class="btn small danger">Hapus</button></form></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot><tr><th colspan="2">Total Pengeluaran</th><th id="expenseTotal" colspan="2"><?= rupiah($expenseTotal) ?></th></tr></tfoot>
            </table>
        </div>
    </article>
</section>

<section class="profit-bar">
    <span>Keuntungan Bersih</span>
    <strong id="netProfit"><?= rupiah($incomeTotal - $expenseTotal) ?></strong>
</section>

<dialog id="expenseModal" class="modal">
    <form class="form" method="post" action="<?= url('index.php?route=owner/storeExpense') ?>">
        <h2>Tambah Pengeluaran</h2>
        <label>Keterangan<input name="description" required></label>
        <label>Tanggal<input type="date" name="expense_date" value="<?= date('Y-m-d') ?>" required></label>
        <label>Nominal<input name="amount" inputmode="numeric" data-numeric required></label>
        <div class="modal-actions"><button class="btn ghost" type="button" data-close-modal>Tutup</button><button class="btn primary" type="submit">Simpan</button></div>
    </form>
</dialog>
