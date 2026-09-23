<?php

declare(strict_types=1);

define('BASE_PATH', __DIR__);

require BASE_PATH . '/app/Core/Autoloader.php';

(new App\Core\Autoloader(['App\\' => BASE_PATH . '/app/']))->register();

require BASE_PATH . '/app/Core/helpers.php';

App\Core\Env::load(BASE_PATH . '/.env');
App\Core\Config::load(BASE_PATH . '/config');

if (App\Core\Config::get('app.debug') === true) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', BASE_PATH . '/storage/logs/php-error.log');
}

date_default_timezone_set('Asia/Kuala_Lumpur');
