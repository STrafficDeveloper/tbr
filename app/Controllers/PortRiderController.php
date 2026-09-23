<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Config;
use App\Core\Controller;
use App\Core\Paginator;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\PortRiderRepository;
use App\Services\RateLimiter;
use App\Services\Schema;
use App\Services\Visitor;

final class PortRiderController extends Controller
{
    private const PER_PAGE = 12;
    private const LIKES_PER_IP_PER_HOUR = 120;

    public function index(Request $request): string
    {
        $repository = new PortRiderRepository();
        $stateNames = Config::get('site.states', []);
        $availableStates = $repository->statesWithListings();

        // Unknown states are ignored rather than trusted, so ?negeri=<junk> is just "All".
        $state = (string) ($request->input('negeri') ?? '');
        $state = in_array($state, $availableStates, true) ? $state : '';
        $term = mb_substr((string) ($request->input('q') ?? ''), 0, 100);

        $total = $repository->count($state, $term);
        $paginator = new Paginator($total, self::PER_PAGE, $request->page(), '/port-rider', [
            'negeri' => $state,
            'q' => $term,
        ]);
        $places = $repository->search($state, $term, self::PER_PAGE, $paginator->offset());

        $stateLabel = $stateNames[$state] ?? '';
        $title = $stateLabel === '' ? 'Port Rider: Bike Shop & Pit Stop' : "Port Rider {$stateLabel}: Bike Shop & Pit Stop";

        $seo = $this->seo()
            ->setTitle($title . ($paginator->page > 1 ? " (Halaman {$paginator->page})" : ''))
            ->setDescription(
                'Senarai bike shop, bengkel, stesen minyak dan port makan pilihan komuniti The Bikers Ranger'
                . ($stateLabel === '' ? ' di seluruh Malaysia.' : " di {$stateLabel}.")
                . ' Cari lokasi dan terus buka di peta.',
            )
            ->setCanonical($paginator->url($paginator->page))
            ->addJsonLd(Schema::breadcrumbs([['Home', '/'], ['Port Rider', '/port-rider']]));

        // Search results are endless near-duplicates; keep them out of the index.
        if ($term !== '') {
            $seo->noIndex();
        }

        foreach ($places as $place) {
            $seo->addJsonLd(Schema::localBusiness($place));
        }

        $stateOptions = ['' => 'All'];
        foreach ($availableStates as $key) {
            $stateOptions[$key] = $stateNames[$key] ?? $key;
        }

        return $this->view('pages/port-rider/index', [
            'seo' => $seo,
            'places' => $places,
            'likedIds' => self::likedIdsFor($repository, $places),
            'paginator' => $paginator,
            'state' => $state,
            'stateLabel' => $stateLabel,
            'stateOptions' => $stateOptions,
            'term' => $term,
        ]);
    }

    /** Heart button. JSON for the in-page script, a redirect back without it. */
    public function like(Request $request): never
    {
        $this->verifyCsrf($request);

        $repository = new PortRiderRepository();
        $place = $repository->findPublishedBySlug((string) $request->routeParam('slug'));

        if ($place === null) {
            Response::notFound();
        }

        $limiter = new RateLimiter();
        $bucket = RateLimiter::bucket('like', $request->ip());

        if ($limiter->tooManyAttempts($bucket, self::LIKES_PER_IP_PER_HOUR, 3600)) {
            Response::wantsJson()
                ? Response::json(['error' => 'Terlalu banyak cubaan. Sila cuba sebentar lagi.'], 429)
                : Response::back('/port-rider', (string) $place['slug']);
        }

        $limiter->hit($bucket);

        $userId = Auth::id();
        $result = $repository->toggleLike((int) $place['id'], $userId, $userId === null ? Visitor::hash() : null);

        if (Response::wantsJson()) {
            Response::json(['liked' => $result['liked'], 'count' => $result['count'], 'label' => formatCount($result['count'])]);
        }

        Response::back('/port-rider', (string) $place['slug']);
    }

    /**
     * Beacon sent when "View Location" is opened. Counted once per listing
     * per visitor session, so reloading or re-clicking doesn't inflate it.
     */
    public function recordView(Request $request): never
    {
        $this->verifyCsrf($request);

        $repository = new PortRiderRepository();
        $place = $repository->findPublishedBySlug((string) $request->routeParam('slug'));

        if ($place !== null) {
            $seen = Session::get('_viewed_places', []);
            $seen = is_array($seen) ? $seen : [];

            if (!in_array((int) $place['id'], $seen, true)) {
                $repository->incrementViews((int) $place['id']);
                $seen[] = (int) $place['id'];
                Session::put('_viewed_places', array_slice($seen, -500));
            }
        }

        http_response_code(204);
        exit;
    }

    /**
     * @param list<array<string,mixed>> $places
     * @return list<int>
     */
    public static function likedIdsFor(PortRiderRepository $repository, array $places): array
    {
        $userId = Auth::id();

        return $repository->likedIds(
            array_map(static fn (array $p): int => (int) $p['id'], $places),
            $userId,
            $userId === null ? Visitor::existingHash() : null,
        );
    }
}
