<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\HomeController;
use App\Controllers\PortRiderController;
use App\Core\Router;

/** @var Router $router */

$router->get('/', [HomeController::class, 'index']);

$router->get('/daftar', [AuthController::class, 'showSignup']);
$router->post('/daftar', [AuthController::class, 'register']);
$router->post('/log-keluar', [AuthController::class, 'logout']);

$router->get('/port-rider', [PortRiderController::class, 'index']);
