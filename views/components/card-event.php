<?php

declare(strict_types=1);

use App\Core\Config;

/**
 * An upcoming pit stop: date, place, how many riders are in, and the
 * "Ready Nak Ride?" button through to the registration form for that stop.
 * The title opens the design's pop-up (cloned from the <template>); without
 * JavaScript it is a plain link to the stop's own page.
 *
 * @var array<string,mixed> $event a pitstop_events row with registrations_count
 */
$states = Config::get('site.states', []);
$place = implode(', ', array_filter([$event['location_name'], $states[$event['state']] ?? $event['state']]));
$registered = (int) ($event['registrations_count'] ?? 0);
$timestamp = strtotime((string) $event['starts_at']);
$slug = (string) $event['slug'];
$image = uploaded($event['banner_image'] ?? null, asset('img/pitstop-event.webp'));
$registerUrl = '/pit-stop/daftar?acara=' . rawurlencode($slug);
$detailId = 'detail-event-' . $slug;
?>
<article class="card card--event">
    <div class="card__media">
        <img src="<?= e($image) ?>" alt=""
             loading="lazy" decoding="async" width="400" height="260">
    </div>
    <div class="card__body">
        <h3 class="card__title">
            <a class="card__link" href="/pit-stop/<?= e(rawurlencode($slug)) ?>" data-detail="<?= e($detailId) ?>"><?= e($event['title']) ?></a>
        </h3>
        <p class="card__meta">
            <?= icon('calendar', 'icon icon--sm') ?>
            <time datetime="<?= e($timestamp === false ? '' : date('c', $timestamp)) ?>"><?= e(formatDate((string) $event['starts_at'], true)) ?></time>
        </p>
        <p class="card__meta"><?= icon('pin', 'icon icon--sm') ?> <?= e($place) ?></p>
<?php if ($registered > 0): ?>
        <p class="card__meta">
            <?= icon('users', 'icon icon--sm') ?>
            <span aria-hidden="true"><?= e(formatCount($registered)) ?> rider</span>
            <span class="visually-hidden"><?= number_format($registered) ?> rider telah mendaftar</span>
        </p>
<?php endif; ?>
        <a class="btn btn--primary card__cta" href="<?= e($registerUrl) ?>">
            Ready Nak Ride? Klik Sini!
            <span class="visually-hidden">Daftar untuk <?= e($event['title']) ?></span>
        </a>
    </div>

    <template id="<?= e($detailId) ?>">
        <div class="detail">
            <img class="detail__media" src="<?= e($image) ?>" alt="" width="640" height="400">
            <div class="detail__body">
                <h2 class="detail__title"><?= e($event['title']) ?></h2>
                <dl class="detail__facts">
                    <div><dt>Tarikh</dt><dd><?= e(formatDate((string) $event['starts_at'])) ?></dd></div>
                    <div><dt>Masa</dt><dd><?= e(formatTime((string) $event['starts_at'], $event['ends_at'] ?? null)) ?></dd></div>
                    <div><dt>Lokasi</dt><dd><?= e($place) ?></dd></div>
                </dl>
                <a class="btn btn--primary" href="<?= e($registerUrl) ?>">Daftar Pitstop Event Sekarang</a>
            </div>
        </div>
    </template>
</article>
