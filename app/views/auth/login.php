<section class="auth-page login-auth">
    <aside class="auth-side">
        <div class="auth-brand">
            <div class="auth-logo">🏠</div>
            <div>
                <div class="auth-brand-name">Kost<span>Track</span></div>
                <p>Sistem Pengelolaan Kost Modern</p>
            </div>
        </div>
        <div class="auth-features">
            <div><strong>Data Penghuni</strong><span>Kelola penyewa, kamar, dan status sewa.</span></div>
            <div><strong>Keuangan Real-Time</strong><span>Pantau pemasukan, pengeluaran, dan laba bersih.</span></div>
            <div><strong>Layanan Keluhan</strong><span>Respons laporan penghuni dengan rapi.</span></div>
        </div>
    </aside>

    <main class="auth-card">
        <div class="auth-card-head">
            <h1>Selamat <span>Datang</span></h1>
            <p>Masuk ke akun KostTrack Anda</p>
        </div>

        <form class="auth-form" method="post" action="<?= url('index.php?route=auth/authenticate') ?>">
            <label>Email
                <input type="email" name="email" placeholder="nama@email.com" autocomplete="email" required>
            </label>
            <label>Password
                <input type="password" name="password" placeholder="Masukkan password" autocomplete="current-password" required>
            </label>
            <div class="form-options">
                <label class="inline-check"><input type="checkbox"> Ingat saya</label>
                <span class="muted small">Session aktif 30 menit</span>
            </div>
            <button type="submit" class="btn-login">Masuk ke Dashboard</button>
        </form>

        <div class="divider"><span></span><p>Belum punya akun?</p><span></span></div>
        <p class="register-prompt">Daftarkan diri Anda <a href="<?= url('index.php?route=auth/register') ?>">Buat Akun Baru</a></p>
    </main>
</section>
