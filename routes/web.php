<?php

declare(strict_types=1);

use App\Controllers\HomeController;
use App\Core\Router;

/** @var Router $router */

$router->get('/', [HomeController::class, 'index']);
