<?php

$sessionPath = __DIR__ . '/storage/sessions';
if (!is_dir($sessionPath)) {
    mkdir($sessionPath, 0777, true);
}
session_save_path($sessionPath);
session_start();

define('APP_ROOT', __DIR__ . '/app');

require_once APP_ROOT . '/config/config.php';
require_once APP_ROOT . '/config/Database.php';
require_once APP_ROOT . '/core/helpers.php';
require_once APP_ROOT . '/core/Model.php';
require_once APP_ROOT . '/core/Controller.php';
require_once APP_ROOT . '/core/Auth.php';

spl_autoload_register(function ($class) {
    foreach (['controllers', 'models'] as $folder) {
        $file = APP_ROOT . '/' . $folder . '/' . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

Auth::guardTimeout();

$route = $_GET['route'] ?? 'auth/login';
[$controllerName, $method] = array_pad(explode('/', $route), 2, 'index');
$controllerClass = ucfirst($controllerName) . 'Controller';

if (!class_exists($controllerClass) || !method_exists($controllerClass, $method)) {
    http_response_code(404);
    echo '404 - Halaman tidak ditemukan';
    exit;
}

(new $controllerClass())->$method();
