<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Config;
use App\Core\Paginator;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\UserRepository;
use App\Services\Csv;

final class MemberController extends AdminController
{
    private const PER_PAGE = 50;
    private const FILTERS = ['' => 'Semua ahli', 'whatsapp' => 'Setuju terima WhatsApp', 'suspended' => 'Digantung'];

    public function index(Request $request): string
    {
        [$search, $filter] = $this->filters($request);
        $users = new UserRepository();

        $paginator = new Paginator(
            $users->adminCount($search, $filter),
            self::PER_PAGE,
            $request->page(),
            '/admin/ahli',
            ['q' => $search, 'tapis' => (string) $filter],
        );

        return $this->adminView('members', 'Ahli', [
            'rows' => $users->adminList($search, $filter, self::PER_PAGE, $paginator->offset()),
            'paginator' => $paginator,
            'search' => $search,
            'filter' => $filter,
            'filters' => self::FILTERS,
        ]);
    }

    public function updateStatus(Request $request): never
    {
        $this->verifyCsrf($request);

        $id = (int) $request->routeParam('id');
        $status = (string) $request->input('status');

        if (!in_array($status, ['active', 'suspended'], true) || $id === Auth::id()) {
            Response::notFound();
        }

        // Admin accounts aren't listed here and can't be suspended from here.
        $member = (new UserRepository())->find($id);
        if ($member === null || $member['role'] !== 'member') {
            Response::notFound();
        }

        (new UserRepository())->setStatus($id, $status);

        Response::back('/admin/ahli');
    }

    /**
     * WhatsApp broadcast list. Only members who ticked the WhatsApp opt-in are
     * exported, because PDPA consent covers exactly that use.
     */
    public function exportWhatsapp(Request $request): never
    {
        $rows = (new UserRepository())->adminList('', 'whatsapp', 100000, 0);
        $states = Config::get('site.states', []);

        Csv::download(
            'ahli-whatsapp-' . date('Ymd-His') . '.csv',
            ['Nama', 'Telefon', 'Negeri', 'Follow TBR', 'Follow Raja Kapcai', 'Sertai peraduan', 'Tarikh daftar'],
            array_map(static fn (array $r): array => [
                $r['name'],
                $r['phone'],
                $states[$r['state']] ?? $r['state'],
                $r['follows_tbr'] ? 'Ya' : 'Tidak',
                $r['follows_raja_kapcai'] ? 'Ya' : 'Tidak',
                $r['contest_opt_in'] ? 'Ya' : 'Tidak',
                $r['created_at'],
            ], array_filter($rows, static fn (array $r): bool => $r['status'] === 'active')),
        );
    }

    /** @return array{0:string,1:?string} */
    private function filters(Request $request): array
    {
        $filter = (string) ($request->input('tapis') ?? '');

        return [
            mb_substr((string) ($request->input('q') ?? ''), 0, 100),
            array_key_exists($filter, self::FILTERS) && $filter !== '' ? $filter : null,
        ];
    }
}
