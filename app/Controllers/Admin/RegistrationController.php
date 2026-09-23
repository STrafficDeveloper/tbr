<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Config;
use App\Core\Database;
use App\Core\Paginator;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\PitStopRegistrationRepository;
use App\Services\Csv;
use App\Services\Mailer;
use Throwable;

/** Reviewing pit stop bookings: filter, approve or reject, export. */
final class RegistrationController extends AdminController
{
    private const PER_PAGE = 50;
    private const STATUSES = ['pending' => 'Dalam semakan', 'approved' => 'Disahkan', 'rejected' => 'Ditolak'];

    public function index(Request $request): string
    {
        [$eventId, $status, $search] = $this->filters($request);
        $repository = new PitStopRegistrationRepository();

        $paginator = new Paginator(
            $repository->adminCount($eventId, $status, $search),
            self::PER_PAGE,
            $request->page(),
            '/admin/pendaftaran',
            ['acara' => (string) ($eventId ?? ''), 'status' => (string) $status, 'q' => $search],
        );

        return $this->adminView('registrations', 'Pendaftaran Pit Stop', [
            'rows' => $repository->adminList($eventId, $status, $search, self::PER_PAGE, $paginator->offset()),
            'paginator' => $paginator,
            'events' => Database::select('SELECT id, title, starts_at FROM pitstop_events ORDER BY starts_at DESC'),
            'eventId' => $eventId,
            'status' => $status,
            'search' => $search,
            'statuses' => self::STATUSES,
            'exportUrl' => '/admin/pendaftaran/eksport' . (($query = http_build_query(array_filter([
                'acara' => $eventId, 'status' => $status, 'q' => $search,
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

    /** CSV of the current filter, for WhatsApp invitations and on-site check-in. */
    public function export(Request $request): never
    {
        [$eventId, $status, $search] = $this->filters($request);
        $rows = (new PitStopRegistrationRepository())->adminList($eventId, $status, $search, 100000, 0);

        Csv::download(
            'pendaftaran-pit-stop-' . date('Ymd-His') . '.csv',
            ['Pit stop', 'Tarikh pit stop', 'Nama', 'Telefon', 'E-mel', 'Nombor plat', 'Negeri', 'Status', 'Didaftar', 'Persetujuan PDPA'],
            array_map(static fn (array $r): array => [
                $r['event_title'],
                $r['starts_at'],
                $r['name'],
                $r['phone'],
                $r['email'],
                $r['plate_no'],
                Config::get('site.states')[$r['state']] ?? $r['state'],
                self::STATUSES[$r['status']] ?? $r['status'],
                $r['created_at'],
                $r['consent_at'],
            ], $rows),
        );
    }

    /** @return array{0:?int,1:?string,2:string} */
    private function filters(Request $request): array
    {
        $eventId = (int) ($request->input('acara') ?? 0);
        $status = (string) ($request->input('status') ?? '');

        return [
            $eventId > 0 ? $eventId : null,
            array_key_exists($status, self::STATUSES) ? $status : null,
            mb_substr((string) ($request->input('q') ?? ''), 0, 100),
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
            . "Maklumat lokasi dan masa tepat akan dihantar melalui WhatsApp. Jumpa di sana!\n\n"
            . "— {$siteName}\n";

        try {
            (new Mailer())->send((string) $registration['email'], "Slot disahkan: {$registration['event_title']}", $body);
        } catch (Throwable $exception) {
            error_log('Approval email failed: ' . $exception->getMessage());
        }
    }
}
