<?php

define('APP_NAME', 'KOSTRACK');
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/AAACLAN_PEMPROGAMAN-WEB-T/public/index.php')), '/');
$basePath = $scriptDir === '/' ? '' : $scriptDir;
define('BASE_URL', $scheme . '://' . $host . $basePath);
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'kostrack');
define('DB_USER', 'root');
define('DB_PASS', '');
define('SESSION_TIMEOUT', 1800);
define('OWNER_REGISTER_CODE', 'KOSTRACK-OWNER-2026');
