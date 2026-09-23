<?php

declare(strict_types=1);

use App\Core\View;

/**
 * @var array<string,mixed> $event
 * @var string $stateName
 * @var bool $isOpen
 * @var list<array<string,mixed>> $places
 * @var list<int> $likedIds
 * @var \App\Repositories\SiteRepository $site
 */
$timestamp = strtotime((string) $event['starts_at']);
$place = implode(', ', array_filter([$event['location_name'], $stateName]));
$mapsUrl = $event['maps_url'] ?: 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($place);
$taken = (int) $event['registrations_count'];
$capacity = $event['capacity'] === null ? null : (int) $event['capacity'];

$closedReason = match (true) {
    $isOpen => null,
    $event['status'] === 'closed' => 'Pendaftaran untuk pit stop ini telah ditutup.',
    $timestamp !== false && $timestamp <= time() => 'Pit stop ini telah berlangsung.',
    default => 'Maaf, slot untuk pit stop ini sudah penuh.',
};
?>
<section class="event-hero" aria-labelledby="page-title">
    <div class="container event-hero__grid">
        <div class="event-hero__media">
            <img src="<?= e(uploaded($event['banner_image'] ?? null)) ?>" alt="" width="640" height="416" fetchpriority="high">
        </div>
        <div class="event-hero__body">
            <p class="section-heading__eyebrow">Pit Stop</p>
            <h1 class="event-hero__title" id="page-title"><?= e($event['title']) ?></h1>

            <ul class="event-hero__facts">
                <li>
                    <?= icon('calendar') ?>
                    <time datetime="<?= e($timestamp === false ? '' : date('c', $timestamp)) ?>"><?= e(formatDate((string) $event['starts_at'], true)) ?></time>
                </li>
                <li>
                    <?= icon('pin') ?>
                    <span><?= e($place) ?> &middot; <a href="<?= e($mapsUrl) ?>" target="_blank" rel="noopener noreferrer">Lihat peta<span class="visually-hidden"> (dibuka di tab baharu)</span></a></span>
                </li>
<?php if ($capacity !== null || $taken > 0): ?>
                <li>
                    <?= icon('users') ?>
                    <span><?= number_format($taken) ?><?= $capacity !== null ? ' / ' . number_format($capacity) . ' slot' : ' rider' ?> telah mendaftar</span>
                </li>
<?php endif; ?>
            </ul>

<?php if (!empty($event['description'])): ?>
            <p class="event-hero__description"><?= nl2br(e((string) $event['description'])) ?></p>
<?php endif; ?>

<?php if ($closedReason === null): ?>
            <a class="btn btn--primary" href="/pit-stop/daftar?acara=<?= e(rawurlencode((string) $event['slug'])) ?>">Ready Nak Ride? Daftar Slot</a>
            <p class="event-hero__note">Jemputan diperlukan - tiada walk-in. Lokasi &amp; masa tepat dihantar melalui WhatsApp.</p>
<?php else: ?>
            <p class="event-hero__closed" role="status"><?= e($closedReason) ?></p>
            <a class="btn btn--ghost" href="/pit-stop">Lihat pit stop lain</a>
<?php endif; ?>
        </div>
    </div>
</section>

<?= View::partial('partials/gathering', ['site' => $site]) ?>

<?php if ($places !== []): ?>
<section class="section" aria-labelledby="nearby-title">
    <div class="container">
        <?= component('section-heading', [
            'eyebrow' => 'bike shop & pitstop',
            'title' => 'Port Rider ' . $stateName,
            'id' => 'nearby-title',
            'linkUrl' => '/port-rider?negeri=' . rawurlencode((string) $event['state']),
            'linkLabel' => 'Lihat Semua',
        ]) ?>
        <div class="grid grid--rail">
<?php foreach ($places as $placeRow): ?>
            <?= component('card-port-rider', ['place' => $placeRow, 'liked' => in_array((int) $placeRow['id'], $likedIds, true)]) ?>
<?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
