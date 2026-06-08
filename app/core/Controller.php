<?php

abstract class Controller
{
    protected function view(string $view, array $data = []): void
    {
        extract($data);
        $viewFile = __DIR__ . '/../view/' . $view . '.php';
        require __DIR__ . '/../view/layouts/header.php';
        require $viewFile;
        require __DIR__ . '/../view/layouts/footer.php';
    }

    protected function json(array $data): void
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
