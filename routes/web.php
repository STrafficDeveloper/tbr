<?php

declare(strict_types=1);

use App\Controllers\AktivitiController;
use App\Controllers\AuthController;
use App\Controllers\HallOfFameController;
use App\Controllers\HomeController;
use App\Controllers\PanasAtasJalanController;
use App\Controllers\PasswordResetController;
use App\Controllers\PitStopController;
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

// Pit stops. The fixed /daftar paths must come before /{slug}, which would
// otherwise treat "daftar" as an event slug.
$router->get('/pit-stop', [PitStopController::class, 'index']);
$router->get('/pit-stop/daftar', [PitStopController::class, 'showRegister']);
$router->post('/pit-stop/daftar', [PitStopController::class, 'register']);
$router->get('/pit-stop/daftar/berjaya', [PitStopController::class, 'confirmation']);
$router->get('/pit-stop/{slug}', [PitStopController::class, 'show']);

// Aktiviti TBR hub
$router->get('/aktiviti/event', [AktivitiController::class, 'event']);
$router->get('/aktiviti/galeri', [AktivitiController::class, 'galeri']);
$router->get('/aktiviti/galeri/{slug}', [AktivitiController::class, 'album']);
$router->get('/aktiviti/peraduan', [AktivitiController::class, 'peraduan']);
$router->get('/aktiviti/peraduan/{slug}', [AktivitiController::class, 'contest']);
$router->get('/aktiviti/pemenang', [AktivitiController::class, 'pemenang']);
$router->get('/aktiviti/pemenang/{slug}', [AktivitiController::class, 'winners']);

// Hall of Fame: each tab is its own URL
$router->get('/hall-of-fame', [HallOfFameController::class, 'index']);
$router->get('/hall-of-fame/{slug}', [HallOfFameController::class, 'biodata']);
$router->get('/hall-of-fame/{slug}/konten', [HallOfFameController::class, 'content']);
$router->get('/hall-of-fame/{slug}/galeri', [HallOfFameController::class, 'gallery']);

$router->get('/panas-atas-jalan', [PanasAtasJalanController::class, 'index']);

// Directory
$router->get('/port-rider', [PortRiderController::class, 'index']);
$router->post('/port-rider/{slug}/suka', [PortRiderController::class, 'like']);
$router->post('/port-rider/{slug}/lihat', [PortRiderController::class, 'recordView']);
