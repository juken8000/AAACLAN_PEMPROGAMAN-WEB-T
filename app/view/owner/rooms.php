<header class="page-header">
    <div>
        <p class="eyebrow">Manajemen Data Kamar</p>
        <h1>Daftar Kamar</h1>
    </div>
    <button class="btn primary" data-open-modal="roomCreateModal">Tambah Kamar</button>
</header>

<section class="toolbar">
    <input id="roomSearch" placeholder="Cari nomor kamar, tipe, atau penghuni...">
</section>

<div class="table-wrap">
    <table>
        <thead>
        <tr>
            <th>No. Kamar</th>
            <th>Tipe</th>
            <th>Harga</th>
            <th>Status</th>
            <th>Penghuni</th>
            <th>Gambar</th>
            <th>Aksi</th>
        </tr>
        </thead>
        <tbody id="roomRows">
        <?php foreach ($rooms as $room): ?>
            <tr>
                <td><?= e($room['room_number']) ?></td>
                <td><?= e($room['type']) ?></td>
                <td><?= rupiah($room['price']) ?></td>
                <td><span class="badge <?= $room['status'] === 'terisi' ? 'green' : 'neutral' ?>"><?= strtoupper($room['status']) ?></span></td>
                <td><?= e($room['tenant_name'] ?: '-') ?></td>
                <td><?= $room['image'] ? '<img class="thumb" src="' . e(url('uploads/rooms/' . $room['image'])) . '" alt="Kamar">' : '-' ?></td>
                <td class="actions">
                    <button class="btn small" data-fill-room='<?= e(json_encode($room)) ?>' data-open-modal="roomEditModal">Edit</button>
                    <form method="post" action="<?= url('index.php?route=owner/deleteRoom') ?>" data-confirm="Hapus kamar ini?">
                        <input type="hidden" name="id" value="<?= (int) $room['id'] ?>">
                        <button class="btn small danger" type="submit">Hapus</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<dialog id="roomCreateModal" class="modal">
    <form class="form" method="post" enctype="multipart/form-data" action="<?= url('index.php?route=owner/storeRoom') ?>">
        <h2>Tambah Kamar</h2>
        <label>Nomor kamar<input name="room_number" required></label>
        <label>Tipe<select name="type"><option>AC</option><option>NON-AC</option></select></label>
        <label>Harga sewa<input name="price" inputmode="numeric" data-numeric required></label>
        <label>Gambar kamar<input type="file" name="image" accept="image/jpeg,image/png,image/webp"></label>
        <div class="modal-actions"><button class="btn ghost" type="button" data-close-modal>Tutup</button><button class="btn primary" type="submit">Simpan</button></div>
    </form>
</dialog>

<dialog id="roomEditModal" class="modal">
    <form class="form" method="post" enctype="multipart/form-data" action="<?= url('index.php?route=owner/updateRoom') ?>">
        <h2>Edit Kamar</h2>
        <input type="hidden" name="id" id="editRoomId">
        <label>Nomor kamar<input name="room_number" id="editRoomNumber" required></label>
        <label>Tipe<select name="type" id="editRoomType"><option>AC</option><option>NON-AC</option></select></label>
        <label>Harga sewa<input name="price" id="editRoomPrice" inputmode="numeric" data-numeric required></label>
        <label>Gambar baru<input type="file" name="image" accept="image/jpeg,image/png,image/webp"></label>
        <div class="modal-actions"><button class="btn ghost" type="button" data-close-modal>Tutup</button><button class="btn primary" type="submit">Simpan Perubahan</button></div>
    </form>
</dialog>
