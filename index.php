<?php

// PRODUCTION MODE
// ini_set('display_errors', 0);
// ini_set('display_startup_errors', 0);
// error_reporting(E_ALL);

// Aktifkan logging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/storage/logs/php-error.log');
ini_set('display_errors', 1);
error_reporting(E_ALL);

define('BASE_PATH', __DIR__);


require_once 'helpers.php';

/*
|--------------------------------------------------------------------------
| Ambil Action
|--------------------------------------------------------------------------
*/
$action = $_GET['action'] ?? 'dashboard';
$isApi  = str_starts_with($action, 'api.');

/*
|--------------------------------------------------------------------------
| Load Routes
|--------------------------------------------------------------------------
*/
if ($isApi) {
    require_once 'routes/api.php';
} else {
    require_once 'routes/web.php';
}
