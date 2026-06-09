<header class="page-header">
    <div>
        <p class="eyebrow">Keluhan Penghuni</p>
        <h1>Daftar Keluhan Anda</h1>
    </div>
    <button class="btn primary" data-open-modal="complaintModal">+ Ajukan Keluhan Baru</button>
</header>

<div class="table-wrap">
    <table>
        <thead>
        <tr>
            <th>Tanggal</th>
            <th>Keluhan</th>
            <th>Status</th>
            <th>Tanggapan Owner</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($complaints as $row): ?>
            <tr>
                <td><?= e($row['created_at']) ?></td>
                <td><?= e($row['message']) ?></td>
                <td><span class="badge <?= $row['status'] === 'selesai' ? 'green' : 'red' ?>"><?= strtoupper($row['status']) ?></span></td>
                <td><?= e($row['response'] ?: '-') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<dialog id="complaintModal" class="modal">
    <form method="post" action="<?= url('index.php?route=penghuni/storeComplaint') ?>">
        <h2>Ajukan Keluhan Baru</h2>
        <label>Deskripsi masalah
            <textarea name="message" rows="6" required></textarea>
        </label>
        <div class="modal-actions">
            <button class="btn ghost" type="button" data-close-modal>Tutup</button>
            <button class="btn primary" type="submit">Kirim</button>
        </div>
    </form>
</dialog>
