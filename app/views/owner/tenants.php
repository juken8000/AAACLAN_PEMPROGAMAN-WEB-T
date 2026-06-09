<header class="page-header">
    <div>
        <p class="eyebrow">Manajemen Penghuni</p>
        <h1>Daftar Riwayat Penghuni</h1>
    </div>
</header>

<section class="toolbar">
    <input data-table-search="#tenantTable" placeholder="Cari nama, nomor kamar, atau status...">
</section>

<div class="table-wrap">
    <table id="tenantTable">
        <thead>
        <tr>
            <th>Nama</th>
            <th>No. Telepon</th>
            <th>Kamar</th>
            <th>Tanggal Masuk</th>
            <th>Lama Sewa</th>
            <th>Tunggakan</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($tenants as $row): ?>
            <tr>
                <td><?= e($row['full_name']) ?></td>
                <td><?= e($row['phone']) ?></td>
                <td><?= e($row['room_number']) ?> (<?= e($row['type']) ?>)</td>
                <td><?= e($row['start_date']) ?></td>
                <td><?= (int) $row['months_rented'] ?> bulan</td>
                <td><span class="badge <?= (float) $row['arrears'] > 0 ? 'red' : 'green' ?>"><?= rupiah($row['arrears']) ?></span></td>
                <td><span class="badge <?= $row['status'] === 'aktif' ? 'green' : 'neutral' ?>"><?= strtoupper($row['status']) ?></span></td>
                <td>
                    <?php if ($row['status'] === 'aktif'): ?>
                        <form method="post" action="<?= url('index.php?route=owner/checkoutTenant') ?>" data-confirm="Keluarkan penghuni ini?">
                            <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                            <button class="btn small danger" type="submit">Keluarkan</button>
                        </form>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
