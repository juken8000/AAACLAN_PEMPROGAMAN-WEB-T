<?php

define('APP_NAME', 'KOSTRACK');
$host = $_SERVER['HTTP_HOST'] ?? '';
if ($host === '') {
    $host = $_SERVER['SERVER_NAME'] ?? 'localhost';
}
if ($host === '' || $host === '.') {
    $host = 'localhost';
}
$host = rtrim($host, '.');
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
if ($scriptName === '' || $scriptName[0] !== '/') {
    $scriptName = '/' . $scriptName;
}
$scriptDir = str_replace('\\', '/', dirname($scriptName));
$scriptDir = preg_replace('#/\.$#', '', $scriptDir);
$scriptDir = rtrim($scriptDir, '/');
$scriptDir = $scriptDir === '.' ? '' : $scriptDir;
$basePath = $scriptDir === '/' ? '' : $scriptDir;
$scriptFileDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_FILENAME'] ?? ''));
$isPublicEntry = $scriptDir === '/public'
    || substr($scriptDir, -7) === '/public'
    || substr($scriptFileDir, -7) === '/public';
define('BASE_URL', $scheme . '://' . $host . $basePath);
define('PUBLIC_URL_PREFIX', $isPublicEntry ? '' : 'public/');
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'kostrack');
define('DB_USER', 'root');
define('DB_PASS', '');
define('SESSION_TIMEOUT', 1800);
define('OWNER_REGISTER_CODE', 'KOSTRACK-OWNER-2026');
