<?php

class ApiController extends Controller
{
    public function rooms(): void
    {
        Auth::requireRole('owner');
        $this->json(['rows' => (new Room())->all(trim($_GET['q'] ?? ''))]);
    }

    public function finance(): void
    {
        Auth::requireRole('owner');
        $month = (int) ($_GET['month'] ?? date('n'));
        $year = (int) ($_GET['year'] ?? date('Y'));
        $search = trim($_GET['q'] ?? '');
        $income = (new Payment())->income($month, $year, $search);
        $expenses = (new Expense())->all($month, $year);
        $this->json([
            'income' => $income,
            'expenses' => $expenses,
            'incomeTotal' => array_sum(array_column($income, 'amount')),
            'expenseTotal' => array_sum(array_column($expenses, 'amount')),
        ]);
    }

    public function history(): void
    {
        Auth::requireRole('penghuni');
        $tenant = (new Tenant())->activeByUser((int) Auth::user()['id']);
        $month = (int) ($_GET['month'] ?? 0);
        $year = (int) ($_GET['year'] ?? 0);
        $this->json(['rows' => (new Bill())->history((int) $tenant['id'], $month ?: null, $year ?: null)]);
    }
}
