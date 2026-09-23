<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Paginator;
use App\Core\Request;
use App\Repositories\VideoRepository;
use App\Services\Schema;

final class PanasAtasJalanController extends Controller
{
    private const PER_PAGE = 12;
    private const SECTION = 'panas_atas_jalan';

    public function index(Request $request): string
    {
        $repository = new VideoRepository();
        $paginator = new Paginator($repository->count(self::SECTION), self::PER_PAGE, $request->page(), '/panas-atas-jalan');
        $videos = $repository->latest(self::SECTION, self::PER_PAGE, $paginator->offset());

        $seo = $this->seo()
            ->setTitle('Panas Atas Jalan' . ($paginator->page > 1 ? " (Halaman {$paginator->page})" : ''))
            ->setDescription('Content menarik dari komuniti bikers: video Panas Atas Jalan bersama The Bikers Ranger.')
            ->setCanonical($paginator->url($paginator->page))
            ->addJsonLd(Schema::breadcrumbs([['Home', '/'], ['Panas Atas Jalan', '/panas-atas-jalan']]));

        // VideoObject needs something playable; skip entries still waiting for their link.
        foreach ($videos as $video) {
            if (!empty($video['video_id']) || !empty($video['video_url'])) {
                $seo->addJsonLd(Schema::video($video));
            }
        }

        return $this->view('pages/panas-atas-jalan/index', [
            'seo' => $seo,
            'videos' => $videos,
            'paginator' => $paginator,
        ]);
    }
}
