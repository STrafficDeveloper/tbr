<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\HomeController;
use App\Controllers\PasswordResetController;
use App\Controllers\PortRiderController;
use App\Controllers\ProfileController;
use App\Core\Router;

/** @var Router $router */

$router->get('/', [HomeController::class, 'index']);

// Membership
$router->get('/daftar', [AuthController::class, 'showSignup']);
$router->post('/daftar', [AuthController::class, 'register']);
$router->get('/log-masuk', [AuthController::class, 'showLogin']);
$router->post('/log-masuk', [AuthController::class, 'login']);
$router->post('/log-keluar', [AuthController::class, 'logout']);

$router->get('/lupa-kata-laluan', [PasswordResetController::class, 'showRequest']);
$router->post('/lupa-kata-laluan', [PasswordResetController::class, 'sendLink']);
$router->get('/reset-kata-laluan/{token}', [PasswordResetController::class, 'showReset']);
$router->post('/reset-kata-laluan/{token}', [PasswordResetController::class, 'reset']);

$router->get('/tetapan', [ProfileController::class, 'show']);
$router->post('/tetapan', [ProfileController::class, 'update']);
$router->post('/tetapan/gambar', [ProfileController::class, 'updateAvatar']);

// Directory
$router->get('/port-rider', [PortRiderController::class, 'index']);
