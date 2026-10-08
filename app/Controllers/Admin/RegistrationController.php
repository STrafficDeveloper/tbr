<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\PitStopController;
use App\Core\Config;
use App\Core\Database;
use App\Core\Paginator;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\PitStopRegistrationRepository;
use App\Services\Csv;
use App\Services\Mailer;
use Throwable;

/** Reviewing pit stop bookings: filter, approve or reject, tick off attendance, export. */
final class RegistrationController extends AdminController
{
    private const PER_PAGE = 50;
    private const STATUSES = ['pending' => 'Dalam semakan', 'approved' => 'Disahkan', 'rejected' => 'Ditolak'];
    private const ATTENDANCE = ['ya' => 'Sudah hadir', 'belum' => 'Belum hadir'];

    public function index(Request $request): string
    {
        [$eventId, $status, $attended, $search] = $this->filters($request);
        $hadir = $attended === null ? '' : ($attended ? 'ya' : 'belum');
        $repository = new PitStopRegistrationRepository();

        $paginator = new Paginator(
            $repository->adminCount($eventId, $status, $attended, $search),
            self::PER_PAGE,
            $request->page(),
            '/admin/pendaftaran',
            ['acara' => (string) ($eventId ?? ''), 'status' => (string) $status, 'hadir' => $hadir, 'q' => $search],
        );

        return $this->adminView('registrations', 'Pendaftaran Pit Stop', [
            'rows' => $repository->adminList($eventId, $status, $attended, $search, self::PER_PAGE, $paginator->offset()),
            'paginator' => $paginator,
            'events' => Database::select('SELECT id, title, starts_at FROM pitstop_events ORDER BY starts_at DESC'),
            'eventId' => $eventId,
            'status' => $status,
            'search' => $search,
            'statuses' => self::STATUSES,
            'hadir' => $hadir,
            'attendanceOptions' => self::ATTENDANCE,
            'summary' => $eventId !== null ? $repository->attendanceSummary($eventId) : null,
            'exportUrl' => '/admin/pendaftaran/eksport' . (($query = http_build_query(array_filter([
                'acara' => $eventId, 'status' => $status, 'hadir' => $hadir, 'q' => $search,
            ]))) !== '' ? '?' . $query : ''),
        ]);
    }

    public function updateStatus(Request $request): never
    {
        $this->verifyCsrf($request);

        $id = (int) $request->routeParam('id');
        $status = (string) $request->input('status');
        $repository = new PitStopRegistrationRepository();
        $registration = $repository->findWithEvent($id);

        if ($registration === null || !array_key_exists($status, self::STATUSES)) {
            Response::notFound();
        }

        $repository->setStatus($id, $status);

        if ($status === 'approved' && $registration['status'] !== 'approved') {
            $this->sendApprovalEmail($registration);
        }

        Response::back('/admin/pendaftaran');
    }

    /** Ticks a rider in at the pit stop (or clears a mistaken tick). */
    public function updateAttendance(Request $request): never
    {
        $this->verifyCsrf($request);

        $id = (int) $request->routeParam('id');
        $repository = new PitStopRegistrationRepository();

        if ($repository->findWithEvent($id) === null) {
            Response::notFound();
        }

        $repository->setAttended($id, $request->input('hadir') === '1');

        Response::back('/admin/pendaftaran', 'pendaftaran-' . $id);
    }

    /** CSV of the current filter, for invitations and the on-site checklist. */
    public function export(Request $request): never
    {
        [$eventId, $status, $attended, $search] = $this->filters($request);
        $rows = (new PitStopRegistrationRepository())->adminList($eventId, $status, $attended, $search, 100000, 0);

        Csv::download(
            'pendaftaran-pit-stop-' . date('Ymd-His') . '.csv',
            ['Nombor pendaftaran', 'Pit stop', 'Tarikh pit stop', 'Nama', 'Telefon', 'E-mel', 'Nombor plat', 'Negeri', 'Status', 'Hadir', 'Didaftar', 'Persetujuan PDPA'],
            array_map(static fn (array $r): array => [
                PitStopController::reference((int) $r['id']),
                $r['event_title'],
                $r['starts_at'],
                $r['name'],
                $r['phone'],
                $r['email'],
                $r['plate_no'],
                Config::get('site.states')[$r['state']] ?? $r['state'],
                self::STATUSES[$r['status']] ?? $r['status'],
                $r['attended_at'] !== null ? 'Ya (' . $r['attended_at'] . ')' : 'Belum',
                $r['created_at'],
                $r['consent_at'],
            ], $rows),
        );
    }

    /** @return array{0:?int,1:?string,2:?bool,3:string} */
    private function filters(Request $request): array
    {
        $eventId = (int) ($request->input('acara') ?? 0);
        $status = (string) ($request->input('status') ?? '');
        $hadir = (string) ($request->input('hadir') ?? '');

        return [
            $eventId > 0 ? $eventId : null,
            array_key_exists($status, self::STATUSES) ? $status : null,
            array_key_exists($hadir, self::ATTENDANCE) ? $hadir === 'ya' : null,
            trim(mb_substr((string) ($request->input('q') ?? ''), 0, 100)),
        ];
    }

    /** @param array<string,mixed> $registration */
    private function sendApprovalEmail(array $registration): void
    {
        if (empty($registration['email'])) {
            return;
        }

        $siteName = (string) Config::get('app.name');
        $body = "Hai {$registration['name']},\n\n"
            . "Berita baik! Pendaftaran pit stop anda telah disahkan.\n\n"
            . "Pit stop: {$registration['event_title']}\n"
            . 'Tarikh: ' . formatDate((string) $registration['starts_at'], true) . "\n"
            . "Nombor plat: {$registration['plate_no']}\n\n"
            . 'Nombor pendaftaran: ' . PitStopController::reference((int) $registration['id']) . "\n"
            . "Lokasi: {$registration['location_name']}\n\n"
            . "Tunjukkan nombor pendaftaran ini semasa tiba. Jumpa di sana!\n\n"
            . "— {$siteName}\n";

        try {
            (new Mailer())->send((string) $registration['email'], "Slot disahkan: {$registration['event_title']}", $body);
        } catch (Throwable $exception) {
            error_log('Approval email failed: ' . $exception->getMessage());
        }
    }
}
