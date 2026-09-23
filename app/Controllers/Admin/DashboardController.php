<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\Request;
use App\Repositories\PitStopEventRepository;
use App\Repositories\PitStopRegistrationRepository;
use App\Repositories\UserRepository;

final class DashboardController extends AdminController
{
    public function index(Request $request): string
    {
        $registrations = new PitStopRegistrationRepository();
        $listings = Database::selectOne("SELECT COUNT(*) AS n FROM port_riders WHERE status = 'published'");

        return $this->adminView('dashboard', 'Papan Pemuka', [
            'members' => (new UserRepository())->stats(),
            'registrationCounts' => $registrations->countsByStatus(),
            'pending' => $registrations->adminList(null, 'pending', '', 8, 0),
            'upcoming' => (new PitStopEventRepository())->upcoming(5),
            'listingCount' => (int) ($listings['n'] ?? 0),
        ]);
    }
}
