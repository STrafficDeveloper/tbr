<?php

declare(strict_types=1);

use App\Core\Config;
use App\Core\ErrorHandler;
use App\Core\Request;
use App\Core\Router;
use App\Core\SecurityHeaders;
use App\Core\Session;

require dirname(__DIR__) . '/bootstrap.php';

ErrorHandler::register(Config::get('app.debug') === true);
SecurityHeaders::send();
Session::start();

$router = new Router();
require BASE_PATH . '/routes/web.php';

$router->dispatch(new Request());
