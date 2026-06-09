<section class="guest-page">
    <nav class="guest-nav">
        <a class="guest-brand" href="<?= url('index.php') ?>">
            <span class="guest-brand-mark">K</span>
            <strong>Kost<span>Track</span></strong>
        </a>
        <div class="guest-nav-actions">
            <a class="btn ghost" href="#katalog-kamar">Katalog Kamar</a>
            <a class="btn primary" href="<?= url('index.php?route=auth/login') ?>">Login</a>
        </div>
    </nav>

    <header class="guest-hero">
        <div class="guest-hero-copy">
            <p class="eyebrow">Guest View</p>
            <h1>Katalog Kamar Kost</h1>
            <p>Lihat unit kamar yang tersedia, cek tipe, status, harga sewa, dan lanjutkan proses sewa dari kamar yang dipilih.</p>
        </div>
        <div class="guest-hero-panel" aria-label="Ringkasan fasilitas">
            <span>Fasilitas umum</span>
            <strong>WiFi, parkir, air bersih, keamanan 24 jam</strong>
        </div>
    </header>

    <section id="katalog-kamar" class="guest-section">
        <div class="guest-section-head">
            <div>
                <p class="eyebrow">Katalog Kamar</p>
                <h2>Daftar Unit Kamar</h2>
            </div>
            <span class="muted small"><?= count($rooms) ?> unit terdaftar</span>
        </div>

        <?php if (empty($rooms)): ?>
            <div class="empty-state">
                <strong>Belum ada kamar tersedia di katalog.</strong>
                <span>Silakan cek kembali setelah admin menambahkan data kamar.</span>
            </div>
        <?php else: ?>
            <div class="room-catalog">
                <?php foreach ($rooms as $room): ?>
                    <?php $imageUrl = $room['image'] ? url('uploads/rooms/' . $room['image']) : null; ?>
                    <article class="room-card">
                        <div class="room-card-media">
                            <?php if ($imageUrl): ?>
                                <img src="<?= e($imageUrl) ?>" alt="Foto kamar <?= e($room['room_number']) ?>">
                            <?php else: ?>
                                <div class="room-placeholder">
                                    <span>Kamar <?= e($room['room_number']) ?></span>
                                </div>
                            <?php endif; ?>
                            <span class="room-status <?= $room['status'] === 'kosong' ? 'available' : 'occupied' ?>">
                                <?= $room['status'] === 'kosong' ? 'Tersedia' : 'Terisi' ?>
                            </span>
                        </div>
                        <div class="room-card-body">
                            <div>
                                <h3>kamar <?= e($room['room_number']) ?></h3>
                                <p><?= e($room['type']) ?> · <?= rupiah($room['price']) ?>/bulan</p>
                            </div>
                            <dl class="room-summary">
                                <div><dt>Tipe</dt><dd><?= e($room['type']) ?></dd></div>
                                <div><dt>Status</dt><dd><?= e(ucfirst($room['status'])) ?></dd></div>
                                <div><dt>Harga</dt><dd><?= rupiah($room['price']) ?></dd></div>
                            </dl>
                            <a class="btn primary full-btn" href="<?= url('index.php?route=home/detail&id=' . (int) $room['id']) ?>">Lihat Kamar</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <section class="guest-section facility-section">
        <div class="guest-section-head">
            <div>
                <p class="eyebrow">Fasilitas Kost</p>
                <h2>Fasilitas Umum Penghuni</h2>
            </div>
        </div>
        <div class="facility-grid">
            <div><strong>Area Parkir Luas</strong><span>Ruang parkir tertata untuk kendaraan penghuni.</span></div>
            <div><strong>Akses Air Bersih</strong><span>Air bersih tersedia untuk kebutuhan harian.</span></div>
            <div><strong>Koneksi WiFi</strong><span>Akses internet untuk aktivitas belajar dan kerja.</span></div>
            <div><strong>Keamanan 24 Jam</strong><span>Lingkungan kost dipantau sepanjang hari.</span></div>
        </div>
    </section>
</section>
