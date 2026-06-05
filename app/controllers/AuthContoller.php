<?php

class AuthController extends Controller
{
    public function login(): void
    {
        if (Auth::check()) {
            redirect(Auth::user()['role'] === 'owner' ? 'owner/dashboard' : 'penghuni/dashboard');
        }
        $this->view('auth/login', ['title' => 'Login']);
    }

    public function register(): void
    {
        if (Auth::check()) {
            redirect(Auth::user()['role'] === 'owner' ? 'owner/dashboard' : 'penghuni/dashboard');
        }
        $this->view('auth/register', [
            'title' => 'Daftar Akun',
            'availableRooms' => (new Room())->available(),
        ]);
    }

    public function authenticate(): void
    {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $user = (new User())->findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            flash('error', 'Email atau password salah.');
            redirect('auth/login');
        }

        Auth::login($user);
        redirect($user['role'] === 'owner' ? 'owner/dashboard' : 'penghuni/dashboard');
    }

    public function storeRegister(): void
    {
        $firstName = trim($_POST['nama_depan'] ?? '');
        $lastName = trim($_POST['nama_belakang'] ?? '');
        $phone = trim($_POST['no_hp'] ?? '');
        $role = trim($_POST['role'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $roomId = (int) ($_POST['room_id'] ?? 0);
        $leaseMonths = (int) ($_POST['lease_months'] ?? 1);
        $ownerCode = trim($_POST['owner_code'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if ($firstName === '' || $phone === '' || $role === '' || $email === '' || $password === '') {
            flash('error', 'Semua field wajib diisi.');
            redirect('auth/register');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Format email tidak valid.');
            redirect('auth/register');
        }
        if (!preg_match('/^[0-9]+$/', $phone)) {
            flash('error', 'Nomor handphone hanya boleh berisi angka.');
            redirect('auth/register');
        }
        if (strlen($password) < 8 || $password !== $confirm) {
            flash('error', 'Password minimal 8 karakter dan konfirmasi harus sama.');
            redirect('auth/register');
        }
        if ($role === 'owner' && !hash_equals(OWNER_REGISTER_CODE, $ownerCode)) {
            flash('error', 'Kode verifikasi pemilik kost salah. Pendaftaran pemilik ditolak.');
            redirect('auth/register');
        }
        if ($role === 'penghuni' && $roomId <= 0) {
            flash('error', 'Penghuni wajib memilih kamar kosong agar dashboard sewa bisa aktif.');
            redirect('auth/register');
        }

        try {
            $userId = (new User())->create([
                'nama_depan' => $firstName,
                'nama_belakang' => $lastName,
                'no_hp' => $phone,
                'role' => $role,
                'email' => $email,
                'password' => $password,
            ]);
            if ($role === 'penghuni') {
                $tenantId = (new Tenant())->createActive($userId, $roomId, $leaseMonths);
                $user = (new User())->find($userId);
                $bill = (new Bill())->currentForTenant($tenantId);
                Auth::login($user);
                flash('success', 'Akun berhasil dibuat. Silakan lanjutkan pembayaran sesuai masa sewa yang dipilih.');
                redirect('penghuni/payment&bill_id=' . (int) ($bill['id'] ?? 0));
            }
            flash('success', 'Akun berhasil dibuat. Silakan login.');
            redirect('auth/login');
        } catch (Throwable $e) {
            flash('error', $e->getMessage());
            redirect('auth/register');
        }
    }

    public function logout(): void
    {
        Auth::logout();
    }
}
