<?php

class Expense extends Model
{
    public function all(int $month, int $year): array
    {
        $stmt = $this->db->prepare('SELECT * FROM expenses WHERE MONTH(expense_date) = ? AND YEAR(expense_date) = ? ORDER BY expense_date DESC');
        $stmt->execute([$month, $year]);
        return $stmt->fetchAll();
    }

    public function create(string $description, string $date, int $amount): bool
    {
        $stmt = $this->db->prepare('INSERT INTO expenses (description, expense_date, amount) VALUES (?, ?, ?)');
        return $stmt->execute([$description, $date, $amount]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM expenses WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function monthlyChart(int $year): array
    {
        $stmt = $this->db->prepare('SELECT MONTH(expense_date) AS month, SUM(amount) AS total FROM expenses WHERE YEAR(expense_date) = ? GROUP BY MONTH(expense_date)');
        $stmt->execute([$year]);
        return $stmt->fetchAll();
    }
}
