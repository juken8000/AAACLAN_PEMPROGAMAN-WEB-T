<?php

class OwnerController extends Controller
{
    private function guard(): void
    {
        Auth::requireRole('owner');
    }

    public function dashboard(): void
    {
        $this->guard();
        $month = (int) date('n');
        $year = (int) date('Y');
        $payment = new Payment();
        $expense = new Expense();
        $tenantModel = new Tenant();
        $billModel = new Bill();
        $income = $payment->income($month, $year);
        $expenses = $expense->all($month, $year);
        $chart = array_fill(1, 12, ['income' => 0, 'expense' => 0]);
        foreach ($payment->monthlyChart($year) as $row) {
            $chart[(int) $row['month']]['income'] = (float) $row['total'];
        }
        foreach ($expense->monthlyChart($year) as $row) {
            $chart[(int) $row['month']]['expense'] = (float) $row['total'];
        }

        $this->view('owner/dashboard', [
            'title' => 'Dashboard Owner',
            'incomeTotal' => array_sum(array_column($income, 'amount')),
            'expenseTotal' => array_sum(array_column($expenses, 'amount')),
            'roomStats' => (new Room())->stats(),
            'pendingComplaints' => (new Complaint())->pendingCount(),
            'dueTenants' => $tenantModel->dueSoon(30),
            'overdueBills' => $billModel->overdue(),
            'arrearsTotal' => $billModel->totalArrearsAll(),
            'arrearsReport' => $billModel->arrearsReport(),
            'chart' => $chart,
        ]);
    }

    public function rooms(): void
    {
        $this->guard();
        $this->view('owner/rooms', ['title' => 'Manajemen Kamar', 'rooms' => (new Room())->all()]);
    }

    public function storeRoom(): void
    {
        $this->guard();
        try {
            (new Room())->create([
                'room_number' => trim($_POST['room_number'] ?? ''),
                'type' => $_POST['type'] ?? 'NON-AC',
                'price' => require_number($_POST['price'] ?? '', 'Harga sewa'),
                'image' => $this->uploadRoomImage(),
            ]);
            flash('success', 'Kamar baru berhasil ditambahkan.');
        } catch (Throwable $e) {
            flash('error', $e->getMessage());
        }
        redirect('owner/rooms');
    }

    public function updateRoom(): void
    {
        $this->guard();
        try {
            (new Room())->update((int) $_POST['id'], [
                'room_number' => trim($_POST['room_number'] ?? ''),
                'type' => $_POST['type'] ?? 'NON-AC',
                'price' => require_number($_POST['price'] ?? '', 'Harga sewa'),
                'image' => $this->uploadRoomImage(),
            ]);
            flash('success', 'Data kamar berhasil diperbarui.');
        } catch (Throwable $e) {
            flash('error', $e->getMessage());
        }
        redirect('owner/rooms');
    }

    public function deleteRoom(): void
    {
        $this->guard();
        try {
            (new Room())->delete((int) $_POST['id']);
            flash('success', 'Data kamar berhasil dihapus.');
        } catch (Throwable $e) {
            flash('error', $e->getMessage());
        }
        redirect('owner/rooms');
    }

    public function tenants(): void
    {
        $this->guard();
        $this->view('owner/tenants', ['title' => 'Manajemen Penghuni', 'tenants' => (new Tenant())->all()]);
    }

    public function checkoutTenant(): void
    {
        $this->guard();
        try {
            (new Tenant())->checkout((int) $_POST['id']);
            flash('success', 'Penghuni berhasil dikeluarkan dan kamar dikosongkan.');
        } catch (Throwable $e) {
            flash('error', $e->getMessage());
        }
        redirect('owner/tenants');
    }

    public function finance(): void
    {
        $this->guard();
        $month = (int) ($_GET['month'] ?? date('n'));
        $year = (int) ($_GET['year'] ?? date('Y'));
        $payment = new Payment();
        $expense = new Expense();
        $income = $payment->income($month, $year);
        $expenses = $expense->all($month, $year);
        $this->view('owner/finance', [
            'title' => 'Laporan Keuangan',
            'month' => $month,
            'year' => $year,
            'income' => $income,
            'expenses' => $expenses,
            'incomeTotal' => array_sum(array_column($income, 'amount')),
            'expenseTotal' => array_sum(array_column($expenses, 'amount')),
        ]);
    }

    public function storeExpense(): void
    {
        $this->guard();
        try {
            (new Expense())->create(trim($_POST['description'] ?? ''), $_POST['expense_date'] ?? date('Y-m-d'), require_number($_POST['amount'] ?? '', 'Nominal pengeluaran'));
            flash('success', 'Pengeluaran berhasil ditambahkan.');
        } catch (Throwable $e) {
            flash('error', $e->getMessage());
        }
        redirect('owner/finance&month=' . (int) date('n') . '&year=' . (int) date('Y'));
    }

    public function deleteExpense(): void
    {
        $this->guard();
        (new Expense())->delete((int) $_POST['id']);
        flash('success', 'Pengeluaran berhasil dihapus.');
        redirect('owner/finance');
    }

    public function complaints(): void
    {
        $this->guard();
        $this->view('owner/complaints', ['title' => 'Keluhan Penghuni', 'complaints' => (new Complaint())->all()]);
    }

    public function respondComplaint(): void
    {
        $this->guard();
        $response = trim($_POST['response'] ?? '');
        if ($response === '') {
            flash('error', 'Tanggapan wajib diisi.');
        } else {
            (new Complaint())->respond((int) $_POST['id'], $response);
            flash('success', 'Keluhan berhasil ditanggapi.');
        }
        redirect('owner/complaints');
    }

    public function deleteComplaint(): void
    {
        $this->guard();
        (new Complaint())->delete((int) $_POST['id']);
        flash('success', 'Keluhan berhasil dihapus.');
        redirect('owner/complaints');
    }

    public function profile(): void
    {
        $this->guard();
        $this->view('owner/profile', ['title' => 'Profil Admin', 'user' => (new User())->find((int) Auth::user()['id'])]);
    }

    public function updateProfile(): void
    {
        $this->guard();
        (new User())->updateProfile((int) Auth::user()['id'], trim($_POST['full_name'] ?? ''), trim($_POST['phone'] ?? ''));
        $_SESSION['user']['full_name'] = trim($_POST['full_name'] ?? '');
        flash('success', 'Informasi admin berhasil diperbarui.');
        redirect('owner/profile');
    }

    public function updatePassword(): void
    {
        $this->guard();
        $password = $_POST['password'] ?? '';
        if (strlen($password) < 6 || $password !== ($_POST['confirm_password'] ?? '')) {
            flash('error', 'Password minimal 6 karakter dan konfirmasi harus sama.');
            redirect('owner/profile');
        }
        (new User())->updatePassword((int) Auth::user()['id'], $password);
        flash('success', 'Password admin berhasil diperbarui.');
        redirect('owner/profile');
    }

    private function uploadRoomImage(): ?string
    {
        if (empty($_FILES['image']['name'])) {
            return null;
        }
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $mime = mime_content_type($_FILES['image']['tmp_name']);
        if (!isset($allowed[$mime])) {
            throw new RuntimeException('Gambar harus berformat JPG, PNG, atau WEBP.');
        }
        $name = uniqid('room_', true) . '.' . $allowed[$mime];
        $target = __DIR__ . '/../../public/uploads/rooms/' . $name;
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            throw new RuntimeException('Upload gambar gagal.');
        }
        return $name;
    }
}
