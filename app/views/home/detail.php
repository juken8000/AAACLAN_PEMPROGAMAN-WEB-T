<section class="guest-page">
    <nav class="guest-nav">
        <a class="guest-brand" href="<?= url('index.php') ?>">
            <span class="guest-brand-mark">K</span>
            <strong>Kost<span>Track</span></strong>
        </a>
        <div class="guest-nav-actions">
            <a class="btn ghost" href="<?= url('index.php') ?>">Kembali</a>
            <a class="btn primary" href="<?= url('index.php?route=auth/login') ?>">Login</a>
        </div>
    </nav>

    <article class="room-detail">
        <div class="room-detail-media">
            <?php if ($room['image']): ?>
                <img src="<?= e(url('uploads/rooms/' . $room['image'])) ?>" alt="Foto interior kamar <?= e($room['room_number']) ?>">
            <?php else: ?>
                <div class="room-detail-placeholder">
                    <span>Interior Kamar <?= e($room['room_number']) ?></span>
                </div>
            <?php endif; ?>
        </div>

        <div class="room-detail-content">
            <p class="eyebrow">Detail Kamar</p>
            <h1>Kamar <?= e($room['room_number']) ?></h1>
            <dl class="detail-list room-detail-list">
                <dt>Tipe Kamar</dt>
                <dd><?= e($room['type']) ?></dd>
                <dt>Harga Sewa</dt>
                <dd><?= rupiah($room['price']) ?> / bulan</dd>
                <dt>Status</dt>
                <dd>
                    <span class="badge <?= $room['status'] === 'kosong' ? 'green' : 'neutral' ?>">
                        <?= $room['status'] === 'kosong' ? 'TERSEDIA' : 'TERISI' ?>
                    </span>
                </dd>
            </dl>

            <section class="room-facilities">
                <h2>Fasilitas Interior</h2>
                <ul>
                    <?php foreach ($facilities as $facility): ?>
                        <li><?= e($facility) ?></li>
                    <?php endforeach; ?>
                </ul>
            </section>

            <div class="detail-actions">
                <?php if ($room['status'] === 'kosong'): ?>
                    <a class="btn primary" href="<?= url('index.php?route=auth/register&room_id=' . (int) $room['id']) ?>">Sewa Kamar</a>
                <?php else: ?>
                    <button class="btn primary" type="button" disabled>Kamar Terisi</button>
                <?php endif; ?>
                <a class="btn ghost" href="<?= url('index.php') ?>">Lihat Kamar Lain</a>
            </div>
        </div>
    </article>
</section>
