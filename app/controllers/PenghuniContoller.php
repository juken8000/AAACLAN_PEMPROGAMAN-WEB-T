<?php

class PenghuniController extends Controller
{
    private function tenant(): array
    {
        Auth::requireRole('penghuni');
        $tenantModel = new Tenant();
        $tenant = $tenantModel->activeByUser((int) Auth::user()['id']);
        if (!$tenant) {
            $rooms = (new Room())->available();
            if ($rooms) {
                $tenantModel->createActive((int) Auth::user()['id'], (int) $rooms[0]['id'], 1);
                $tenant = $tenantModel->activeByUser((int) Auth::user()['id']);
                flash('success', 'Akun penghuni berhasil dipasangkan otomatis ke kamar kosong.');
                return $tenant;
            }
            flash('warning', 'Akun Anda belum memiliki data sewa aktif dan saat ini tidak ada kamar kosong.');
            redirect('penghuni/noTenant');
        }
        return $tenant;
    }

    public function noTenant(): void
    {
        Auth::requireRole('penghuni');
        $this->view('penghuni/no_tenant', [
            'title' => 'Belum Ada Kamar Aktif',
            'user' => (new User())->find((int) Auth::user()['id']),
        ]);
    }

    public function dashboard(): void
    {
        $tenant = $this->tenant();
        $billModel = new Bill();
        $this->view('penghuni/dashboard', [
            'title' => 'Dashboard Penghuni',
            'tenant' => $tenant,
            'currentBill' => $billModel->currentForTenant((int) $tenant['id']),
            'arrears' => $billModel->totalArrears((int) $tenant['id']),
            'activeComplaints' => (new Complaint())->activeCount((int) $tenant['id']),
        ]);
    }

    public function payArrears(): void
    {
        $tenant = $this->tenant();
        $unpaid = (new Bill())->unpaidForTenant((int) $tenant['id']);
        if (!$unpaid) {
            flash('warning', 'Anda tidak memiliki tunggakan.');
            redirect('penghuni/dashboard');
        }
        redirect('penghuni/payment');
    }

    public function payment(): void
    {
        $tenant = $this->tenant();
        $billModel = new Bill();
        $billId = (int) ($_GET['bill_id'] ?? 0);
        $unpaidBills = $billModel->unpaidForTenant((int) $tenant['id']);
        $selectedBill = null;

        if ($billId > 0) {
            $selectedBill = $billModel->payableBill($billId, (int) $tenant['id']);
        }
        if (!$selectedBill && $unpaidBills) {
            $selectedBill = $unpaidBills[0];
        }

        $this->view('penghuni/payment', [
            'title' => 'Pembayaran',
            'tenant' => $tenant,
            'bill' => $selectedBill,
            'unpaidBills' => $unpaidBills,
        ]);
    }

    public function processPayment(): void
    {
        $tenant = $this->tenant();
        $billId = (int) ($_POST['bill_id'] ?? 0);
        $method = $_POST['payment_method'] ?? 'full';
        $bill = (new Bill())->payableBill($billId, (int) $tenant['id']);

        if (!$bill || (float) $bill['remaining_amount'] <= 0) {
            flash('error', 'Tagihan tidak ditemukan atau sudah lunas.');
            redirect('penghuni/payment');
        }

        try {
            $amount = (float) $bill['remaining_amount'];
            if ($method === 'dp') {
                $amount = require_number($_POST['dp_amount'] ?? '', 'Nominal DP');
                if ($amount < 500000) {
                    throw new RuntimeException('Minimum pembayaran DP adalah Rp500.000.');
                }
            }
            (new Bill())->pay($billId, (int) $tenant['id'], $amount);
            flash('success', $amount >= (float) $bill['remaining_amount'] ? 'Pembayaran lunas berhasil diproses.' : 'Pembayaran DP berhasil diproses.');
            redirect('penghuni/history');
        } catch (Throwable $e) {
            flash('error', $e->getMessage());
            redirect('penghuni/payment&bill_id=' . $billId);
        }
    }

    public function extendLease(): void
    {
        $tenant = $this->tenant();
        try {
            $months = (int) ($_POST['months'] ?? 1);
            $billId = (new Tenant())->extendLease((int) $tenant['id'], $months);
            flash('success', 'Masa sewa berhasil diperpanjang. Silakan lanjutkan pembayaran tagihan baru.');
            redirect('penghuni/payment&bill_id=' . $billId);
        } catch (Throwable $e) {
            flash('error', $e->getMessage());
            redirect('penghuni/dashboard');
        }
    }

    public function history(): void
    {
        $tenant = $this->tenant();
        $this->view('penghuni/history', [
            'title' => 'Riwayat Pembayaran',
            'tenant' => $tenant,
            'history' => (new Bill())->history((int) $tenant['id']),
        ]);
    }

    public function complaints(): void
    {
        $tenant = $this->tenant();
        $this->view('penghuni/complaints', [
            'title' => 'Keluhan Penghuni',
            'tenant' => $tenant,
            'complaints' => (new Complaint())->byTenant((int) $tenant['id']),
        ]);
    }

    public function storeComplaint(): void
    {
        $tenant = $this->tenant();
        $message = trim($_POST['message'] ?? '');
        if ($message === '') {
            flash('error', 'Detail keluhan wajib diisi.');
        } else {
            (new Complaint())->create((int) $tenant['id'], $message);
            flash('success', 'Keluhan berhasil dikirim.');
        }
        redirect('penghuni/complaints');
    }

    public function profile(): void
    {
        $tenant = $this->tenant();
        $history = (new Bill())->history((int) $tenant['id']);
        $lastPaid = null;
        foreach ($history as $row) {
            if ($row['paid_at']) {
                $lastPaid = $row['paid_at'];
                break;
            }
        }
        $this->view('penghuni/profile', [
            'title' => 'Profil Penghuni',
            'tenant' => $tenant,
            'lastPaid' => $lastPaid,
        ]);
    }

    public function updateProfile(): void
    {
        Auth::requireRole('penghuni');
        (new User())->updateProfile((int) Auth::user()['id'], trim($_POST['full_name'] ?? ''), trim($_POST['phone'] ?? ''));
        $_SESSION['user']['full_name'] = trim($_POST['full_name'] ?? '');
        flash('success', 'Informasi profil berhasil diperbarui.');
        redirect('penghuni/profile');
    }

    public function updatePassword(): void
    {
        Auth::requireRole('penghuni');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        if (strlen($password) < 6 || $password !== $confirm) {
            flash('error', 'Password minimal 6 karakter dan konfirmasi harus sama.');
            redirect('penghuni/profile');
        }
        (new User())->updatePassword((int) Auth::user()['id'], $password);
        flash('success', 'Password berhasil diperbarui.');
        redirect('penghuni/profile');
    }

    public function deactivate(): void
    {
        $tenant = $this->tenant();
        (new Tenant())->checkout((int) $tenant['id']);
        session_unset();
        session_destroy();
        session_start();
        flash('success', 'Proses keluar berhasil. Akun Anda dinonaktifkan.');
        redirect('auth/login');
    }
}
