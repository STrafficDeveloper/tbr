<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Config;
use App\Core\Controller;
use App\Core\Request;
use App\Repositories\BannerRepository;
use App\Repositories\HallOfFameRepository;
use App\Repositories\PitStopEventRepository;
use App\Repositories\PortRiderRepository;
use App\Repositories\SiteRepository;
use App\Repositories\VideoRepository;
use App\Services\Schema;

final class HomeController extends Controller
{
    private const EVENTS_SHOWN = 8;
    private const PORT_RIDERS_SHOWN = 8;

    public function index(Request $request): string
    {
        $site = new SiteRepository();
        $banners = new BannerRepository();
        $portRiders = new PortRiderRepository();
        $events = (new PitStopEventRepository())->upcoming(self::EVENTS_SHOWN);

        $seo = $this->seo()
            ->setTitle('Komuniti Biker Malaysia')
            ->setDescription((string) Config::get('site.tagline')
                . ' Daftar slot pit stop, cari bike shop & pit stop, dan sertai peraduan komuniti.')
            ->setCanonical('/')
            ->addJsonLd(Schema::organization())
            ->addJsonLd(Schema::website());

        foreach ($events as $event) {
            $seo->addJsonLd(Schema::event($event));
        }

        return $this->view('pages/home', [
            'seo' => $seo,
            'hidePromos' => true,
            'site' => $site,
            'heroSlides' => $banners->forPlacement('home_hero'),
            'sponsorBanners' => $banners->forPlacement('home_sponsor'),
            'stats' => $site->stats(),
            'events' => $events,
            'portRiders' => $portRiders->search(null, null, self::PORT_RIDERS_SHOWN),
            'portRiderStates' => $portRiders->statesWithListings(),
            'hero' => (new HallOfFameRepository())->heroOfMonth(),
            'featuredVideo' => (new VideoRepository())->latest('panas_atas_jalan', 1)[0] ?? null,
        ]);
    }
}
