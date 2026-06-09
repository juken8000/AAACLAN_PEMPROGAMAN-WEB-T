<header class="page-header">
    <div>
        <p class="eyebrow">Keluhan Penghuni</p>
        <h1>Pusat Layanan Pelanggan</h1>
    </div>
    <a class="btn" href="<?= url('index.php?route=owner/complaints') ?>">Refresh</a>
</header>

<section class="toolbar">
    <input data-table-search="#complaintTable" placeholder="Cari nama penghuni, kamar, atau status...">
</section>

<div class="table-wrap">
    <table id="complaintTable">
        <thead><tr><th>Kamar</th><th>Penghuni</th><th>Keluhan</th><th>Status</th><th>Tanggal</th><th>Tanggapan</th><th>Aksi</th></tr></thead>
        <tbody>
        <?php foreach ($complaints as $row): ?>
            <tr>
                <td><?= e($row['room_number']) ?></td>
                <td><?= e($row['full_name']) ?></td>
                <td><?= e($row['message']) ?></td>
                <td><span class="badge <?= $row['status'] === 'selesai' ? 'green' : 'red' ?>"><?= strtoupper($row['status']) ?></span></td>
                <td><?= e($row['created_at']) ?></td>
                <td><?= e($row['response'] ?: '-') ?></td>
                <td class="actions">
                    <?php if ($row['status'] === 'pending'): ?>
                        <button class="btn small" data-complaint-id="<?= (int) $row['id'] ?>" data-complaint-text="<?= e($row['message']) ?>" data-open-modal="respondModal">Tanggapi</button>
                    <?php endif; ?>
                    <form method="post" action="<?= url('index.php?route=owner/deleteComplaint') ?>" data-confirm="Hapus keluhan ini?">
                        <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                        <button class="btn small danger">Hapus</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<dialog id="respondModal" class="modal">
    <form class="form" method="post" action="<?= url('index.php?route=owner/respondComplaint') ?>">
        <h2>Tanggapi Keluhan</h2>
        <input type="hidden" name="id" id="respondComplaintId">
        <p class="muted" id="respondComplaintText"></p>
        <label>Tanggapan owner<textarea name="response" rows="5" required></textarea></label>
        <div class="modal-actions"><button class="btn ghost" type="button" data-close-modal>Tutup</button><button class="btn primary" type="submit">OK</button></div>
    </form>
</dialog>
