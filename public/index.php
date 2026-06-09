<?php

$sessionPath = session_save_path();
if ($sessionPath !== '' && !is_writable($sessionPath)) {
    $fallbackSessionPath = __DIR__ . '/../storage/sessions';
    if (!is_dir($fallbackSessionPath)) {
        mkdir($fallbackSessionPath, 0777, true);
    }
    session_save_path($fallbackSessionPath);
}

session_start();

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/config/Database.php';
require_once __DIR__ . '/../app/core/helpers.php';
require_once __DIR__ . '/../app/core/Model.php';
require_once __DIR__ . '/../app/core/Controller.php';
require_once __DIR__ . '/../app/core/Auth.php';

spl_autoload_register(function ($class) {
    foreach (['controllers', 'models'] as $folder) {
        $file = __DIR__ . '/../app/' . $folder . '/' . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

Auth::guardTimeout();

$route = $_GET['route'] ?? 'home/index';
[$controllerName, $method] = array_pad(explode('/', $route), 2, 'index');
$controllerClass = ucfirst($controllerName) . 'Controller';

if (!class_exists($controllerClass) || !method_exists($controllerClass, $method)) {
    http_response_code(404);
    echo '404 - Halaman tidak ditemukan';
    exit;
}

(new $controllerClass())->$method();
