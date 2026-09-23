<?php

declare(strict_types=1);

use App\Core\View;

/**
 * Sections run in the same order as the approved desktop design.
 *
 * @var \App\Repositories\SiteRepository $site
 * @var list<array<string,mixed>> $heroSlides
 * @var list<array<string,mixed>> $sponsorBanners
 * @var list<array{value:string,label:string}> $stats
 * @var list<array<string,mixed>> $events
 * @var list<array<string,mixed>> $portRiders
 * @var list<string> $portRiderStates
 * @var array<string,mixed>|null $hero
 * @var array<string,mixed>|null $featuredVideo
 */
?>
<?= View::partial('pages/home/hero', ['site' => $site, 'slides' => $heroSlides, 'stats' => $stats]) ?>
<?= View::partial('pages/home/gathering', ['site' => $site]) ?>
<?= View::partial('pages/home/sponsors', ['banners' => $sponsorBanners]) ?>
<?= View::partial('pages/home/highlights', ['events' => $events]) ?>
<?= View::partial('pages/home/port-rider', ['places' => $portRiders, 'states' => $portRiderStates]) ?>
<?= View::partial('pages/home/features', ['hero' => $hero, 'video' => $featuredVideo]) ?>
