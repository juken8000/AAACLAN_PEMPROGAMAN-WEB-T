<section class="register-auth">
    <nav class="auth-nav">
        <a href="<?= url('index.php?route=auth/login') ?>" class="auth-nav-brand">
            <span class="auth-logo small-logo">🏠</span>
            <strong>Kost<span>Track</span></strong>
        </a>
        <a class="btn ghost" href="<?= url('index.php?route=auth/login') ?>">Sudah punya akun</a>
    </nav>

    <div class="register-card">
        <header class="register-head">
            <div class="step-badges">
                <span>Langkah 1</span>
                <span>Info Akun</span>
            </div>
            <h1>Buat Akun <em>KostTrack</em></h1>
            <p>Isi data di bawah ini untuk mulai menggunakan sistem.</p>
        </header>

        <form class="auth-form register-form" method="post" action="<?= url('index.php?route=auth/storeRegister') ?>">
            <p class="section-label">Informasi Pribadi</p>
            <div class="form-grid">
                <label>Nama Depan
                    <input name="nama_depan" placeholder="Budi" required>
                </label>
                <label>Nama Belakang
                    <input name="nama_belakang" placeholder="Santoso">
                </label>
                <label>No. Handphone
                    <input name="no_hp" placeholder="08xxxxxxxxxx" inputmode="numeric" data-numeric required>
                </label>
                <label>Role Pengguna
                    <select name="role" id="registerRole" required>
                        <option value="">-- Pilih Role --</option>
                        <option value="owner">Pemilik Kost</option>
                        <option value="penghuni">Penghuni Kost</option>
                    </select>
                </label>
                <label id="ownerCodeWrap">Kode Verifikasi Pemilik
                    <input name="owner_code" id="ownerCode" placeholder="Masukkan kode khusus owner">
                </label>
                <label id="roomSelectWrap">Pilih Kamar Kosong
                    <select name="room_id" id="registerRoom">
                        <option value="">-- Pilih Kamar --</option>
                        <?php foreach ($availableRooms as $room): ?>
                            <option value="<?= (int) $room['id'] ?>">
                                Kamar <?= e($room['room_number']) ?> - <?= e($room['type']) ?> - <?= rupiah($room['price']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label id="leaseMonthsWrap">Lama Sewa
                    <select name="lease_months" id="leaseMonths">
                        <option value="1">1 bulan</option>
                        <option value="3">3 bulan</option>
                        <option value="6">6 bulan</option>
                        <option value="12">12 bulan</option>
                    </select>
                </label>
            </div>

            <p class="section-label">Informasi Akun</p>
            <div class="form-grid">
                <label class="span-2">Alamat Email
                    <input type="email" name="email" placeholder="nama@email.com" autocomplete="email" required>
                </label>
                <label>Password
                    <input type="password" name="password" placeholder="Min. 8 karakter" minlength="8" data-password-strength required>
                    <span class="strength-wrap">
                        <span class="strength-bar"><span class="strength-fill" id="strengthFill"></span></span>
                        <span class="strength-label" id="strengthLabel">Masukkan password</span>
                    </span>
                </label>
                <label>Konfirmasi Password
                    <input type="password" name="confirm_password" placeholder="Ulangi password" minlength="8" required>
                </label>
            </div>

            <label class="terms-check">
                <input type="checkbox" required>
                <span>Saya menyetujui Syarat & Ketentuan serta Kebijakan Privasi KostTrack.</span>
            </label>

            <button type="submit" class="btn-register">Buat Akun Sekarang</button>
        </form>
    </div>
</section>
