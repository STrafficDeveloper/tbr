<?php

declare(strict_types=1);

use App\Core\Config;

/**
 * An upcoming pit stop: date, place, how many riders are in, and the
 * "Ready Nak Ride?" button through to the registration form for that stop.
 *
 * @var array<string,mixed> $event a pitstop_events row with registrations_count
 */
$states = Config::get('site.states', []);
$place = implode(', ', array_filter([$event['location_name'], $states[$event['state']] ?? $event['state']]));
$registered = (int) ($event['registrations_count'] ?? 0);
$timestamp = strtotime((string) $event['starts_at']);
?>
<article class="card card--event">
    <div class="card__media">
        <img src="<?= e(uploaded($event['banner_image'] ?? null)) ?>" alt=""
             loading="lazy" decoding="async" width="400" height="260">
    </div>
    <div class="card__body">
        <h3 class="card__title"><a href="/pit-stop/<?= e(rawurlencode((string) $event['slug'])) ?>"><?= e($event['title']) ?></a></h3>
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
        <a class="btn btn--primary card__cta" href="/pit-stop/daftar?acara=<?= e(rawurlencode((string) $event['slug'])) ?>">
            Ready Nak Ride? Klik Sini!
            <span class="visually-hidden">Daftar untuk <?= e($event['title']) ?></span>
        </a>
    </div>
</article>
