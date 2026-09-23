<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\HallOfFameRepository;
use App\Repositories\VideoRepository;
use App\Services\Schema;

final class HallOfFameController extends Controller
{
    private const TABS = [
        'konten' => 'Content',
        'galeri' => 'Galeri',
        'biodata' => 'Biodata',
    ];

    /** The nav link always leads to whoever is hero of the month right now. */
    public function index(Request $request): never
    {
        $hero = (new HallOfFameRepository())->heroOfMonth();

        if ($hero === null) {
            Response::notFound();
        }

        // 302, not 301: next month this points at someone else.
        Response::redirect('/hall-of-fame/' . $hero['slug']);
    }

    public function biodata(Request $request): string
    {
        return $this->render($request, 'biodata');
    }

    public function content(Request $request): string
    {
        return $this->render($request, 'konten');
    }

    public function gallery(Request $request): string
    {
        return $this->render($request, 'galeri');
    }

    private function render(Request $request, string $tab): string
    {
        $repository = new HallOfFameRepository();
        $profile = $repository->findPublishedBySlug((string) $request->routeParam('slug'));

        if ($profile === null) {
            Response::notFound();
        }

        $base = '/hall-of-fame/' . $profile['slug'];
        $path = $tab === 'biodata' ? $base : "{$base}/{$tab}";
        $videos = $tab === 'konten' ? (new VideoRepository())->forProfile((int) $profile['id']) : [];

        $seo = $this->seo()
            ->setTitle($profile['name'] . ($tab === 'biodata' ? '' : ' | ' . self::TABS[$tab]) . ' | Hall of Fame')
            ->setDescription((string) ($profile['summary'] ?: "{$profile['name']}, Hall of Fame The Bikers Ranger."))
            ->setCanonical($path)
            ->addJsonLd(Schema::person($profile))
            ->addJsonLd(Schema::breadcrumbs([['Home', '/'], ['Hall of Fame', '/hall-of-fame'], [(string) $profile['name'], $base]]));

        if (!empty($profile['avatar'])) {
            $seo->setImage(uploaded($profile['avatar']));
        }

        foreach ($videos as $video) {
            if (!empty($video['video_id']) || !empty($video['video_url'])) {
                $seo->addJsonLd(Schema::video($video));
            }
        }

        $tabs = [];
        foreach (self::TABS as $key => $label) {
            $tabs[] = ['label' => $label, 'url' => $key === 'biodata' ? $base : "{$base}/{$key}"];
        }

        return $this->view('pages/hall-of-fame/show', [
            'seo' => $seo,
            'profile' => $profile,
            'tab' => $tab,
            'tabs' => $tabs,
            'sections' => $tab === 'biodata' ? $repository->sections((int) $profile['id']) : [],
            'images' => $tab === 'galeri' ? $repository->images((int) $profile['id']) : [],
            'videos' => $videos,
        ]);
    }
}
