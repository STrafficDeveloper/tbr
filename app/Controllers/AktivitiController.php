<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Config;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Seo;
use App\Repositories\ContestRepository;
use App\Repositories\GalleryRepository;
use App\Repositories\PitStopEventRepository;
use App\Services\Schema;

/**
 * The Aktiviti TBR hub: Event (pit stops), Galeri, Peraduan and Pemenang.
 * The design shows details in pop-ups; here each one is a real page, so it
 * can be linked, shared and indexed, and still works without JavaScript.
 */
final class AktivitiController extends Controller
{
    public function event(Request $request): string
    {
        $events = (new PitStopEventRepository())->upcoming(100);

        $seo = $this->hubSeo('Event', '/aktiviti/event', 'Pit stop dan event komuniti The Bikers Ranger yang akan datang.');

        foreach ($events as $event) {
            $seo->addJsonLd(Schema::event($event));
        }

        return $this->view('pages/aktiviti/event', ['seo' => $seo, 'events' => $events]);
    }

    public function galeri(Request $request): string
    {
        return $this->view('pages/aktiviti/galeri', [
            'seo' => $this->hubSeo('Galeri', '/aktiviti/galeri', 'Galeri gambar komuniti The Bikers Ranger: bike paling hensem, meriah, raya dan sempoi.'),
            'albums' => (new GalleryRepository())->published(),
        ]);
    }

    public function album(Request $request): string
    {
        $galleries = new GalleryRepository();
        $album = $galleries->findPublishedBySlug((string) $request->routeParam('slug'));

        if ($album === null) {
            Response::notFound();
        }

        $path = '/aktiviti/galeri/' . $album['slug'];
        $seo = $this->seo()
            ->setTitle($album['title'] . ' | Galeri')
            ->setDescription((string) ($album['description'] ?: 'Galeri gambar komuniti The Bikers Ranger.'))
            ->setCanonical($path)
            ->addJsonLd(Schema::breadcrumbs([['Home', '/'], ['Galeri', '/aktiviti/galeri'], [(string) $album['title'], $path]]));

        if (!empty($album['cover_image'])) {
            $seo->setImage(uploaded($album['cover_image']));
        }

        return $this->view('pages/aktiviti/album', [
            'seo' => $seo,
            'album' => $album,
            'albums' => $galleries->published(),
            'images' => $galleries->images((int) $album['id']),
        ]);
    }

    public function peraduan(Request $request): string
    {
        return $this->view('pages/aktiviti/peraduan', [
            'seo' => $this->hubSeo('Peraduan', '/aktiviti/peraduan', 'Sertai peraduan SNAP-JE-MENANG dan menangi hadiah menarik bersama The Bikers Ranger.'),
            'contests' => (new ContestRepository())->published(),
        ]);
    }

    public function contest(Request $request): string
    {
        $contests = new ContestRepository();
        $contest = $contests->findPublishedBySlug((string) $request->routeParam('slug'));

        if ($contest === null) {
            Response::notFound();
        }

        $path = '/aktiviti/peraduan/' . $contest['slug'];
        $whatsapp = normalizePhone((string) ($contest['whatsapp_number'] ?: Config::get('site.whatsapp')));
        $message = (string) ($contest['whatsapp_message'] ?: "Saya ingin menyertai peraduan {$contest['title']}.");

        $seo = $this->seo()
            ->setTitle($contest['title'] . ' | Peraduan')
            ->setDescription($contest['title'] . ' (' . formatDateRange($contest['starts_on'], $contest['ends_on']) . '). '
                . ($contest['tagline'] ?? ''))
            ->setCanonical($path)
            ->addJsonLd(Schema::breadcrumbs([['Home', '/'], ['Peraduan', '/aktiviti/peraduan'], [(string) $contest['title'], $path]]));

        return $this->view('pages/aktiviti/contest', [
            'seo' => $seo,
            'contest' => $contest,
            'phase' => ContestRepository::phase($contest),
            'prizes' => $contests->prizes((int) $contest['id']),
            'steps' => array_values(array_filter(array_map('trim', explode("\n", (string) $contest['rules'])))),
            'whatsappUrl' => 'https://wa.me/' . rawurlencode($whatsapp) . '?text=' . rawurlencode($message),
        ]);
    }

    public function pemenang(Request $request): string
    {
        return $this->view('pages/aktiviti/pemenang', [
            'seo' => $this->hubSeo('Pemenang', '/aktiviti/pemenang', 'Pentas juara: senarai pemenang peraduan The Bikers Ranger.'),
            'contests' => (new ContestRepository())->withAnnouncedWinners(),
        ]);
    }

    public function winners(Request $request): string
    {
        $contests = new ContestRepository();
        $contest = $contests->findPublishedBySlug((string) $request->routeParam('slug'));

        // Before announce_on the winners page doesn't exist yet, so it can't leak early.
        if ($contest === null || !$contest['winners_announced']) {
            Response::notFound();
        }

        $path = '/aktiviti/pemenang/' . $contest['slug'];
        $seo = $this->seo()
            ->setTitle('Pemenang ' . $contest['title'])
            ->setDescription('Tahniah kepada semua pemenang ' . $contest['title'] . ' bersama The Bikers Ranger.')
            ->setCanonical($path)
            ->addJsonLd(Schema::breadcrumbs([['Home', '/'], ['Pemenang', '/aktiviti/pemenang'], [(string) $contest['title'], $path]]));

        return $this->view('pages/aktiviti/winners', [
            'seo' => $seo,
            'contest' => $contest,
            'winners' => $contests->winners((int) $contest['id']),
        ]);
    }

    private function hubSeo(string $tab, string $path, string $description): Seo
    {
        return $this->seo()
            ->setTitle("{$tab} | Aktiviti TBR")
            ->setDescription($description)
            ->setCanonical($path)
            ->addJsonLd(Schema::breadcrumbs([['Home', '/'], [$tab, $path]]));
    }
}
