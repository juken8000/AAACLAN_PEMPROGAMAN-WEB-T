<header class="page-header">
    <div>
        <p class="eyebrow">Profile Admin</p>
        <h1>Identitas Pengelola</h1>
    </div>
</header>

<section class="split">
    <article class="panel">
        <h2>Informasi Admin</h2>
        <?php $adminPhone = !empty($user['phone']) ? $user['phone'] : (!empty($user['no_hp']) ? $user['no_hp'] : '-'); ?>
        <dl class="detail-list">
            <dt>Email</dt><dd><?= e($user['email']) ?></dd>
            <dt>Nama Lengkap</dt><dd><?= e($user['full_name']) ?></dd>
            <dt>No. Telepon</dt><dd><?= e($adminPhone) ?></dd>
        </dl>
    </article>
    <article class="panel">
        <h2>Informasi Kost</h2>
        <dl class="detail-list">
            <dt>Nama Kost</dt><dd>KOSTRACK</dd>
            <dt>Alamat</dt><dd>Jl. Melati No. 10, Jakarta</dd>
            <dt>Deskripsi</dt><dd>Kost nyaman dengan sistem administrasi digital untuk pembayaran, kamar, dan layanan keluhan.</dd>
        </dl>
    </article>
    <article class="panel">
        <h2>Edit Informasi</h2>
        <form class="form" method="post" action="<?= url('index.php?route=owner/updateProfile') ?>">
            <label>Nama lengkap<input name="full_name" value="<?= e($user['full_name']) ?>" required></label>
            <label>No. telepon<input name="phone" value="<?= e($adminPhone) ?>" required></label>
            <button class="btn primary" type="submit">Simpan Informasi</button>
        </form>
    </article>
    <article class="panel">
        <h2>Ubah Password</h2>
        <form class="form" method="post" action="<?= url('index.php?route=owner/updatePassword') ?>">
            <label>Password baru<input type="password" name="password" minlength="6" required></label>
            <label>Konfirmasi password<input type="password" name="confirm_password" minlength="6" required></label>
            <button class="btn" type="submit">Ubah Password</button>
        </form>
    </article>
</section>
