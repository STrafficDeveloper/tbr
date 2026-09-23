<?php

declare(strict_types=1);

use App\Core\Config;
use App\Core\Csrf;

/**
 * One business in the Port Rider directory. The design has no detail page, so
 * the card links out to the map and carries an id anchor for deep links.
 *
 * The heart is a real form: it works with no JavaScript (post, then back to
 * this card) and app.js upgrades it to update in place.
 *
 * @var array<string,mixed> $place a port_riders row
 * @var bool|null $liked whether the current viewer has liked it
 */
$liked ??= false;
$states = Config::get('site.states', []);
$location = implode(', ', array_filter([$place['city'] ?? null, $states[$place['state']] ?? $place['state']]));
$mapsUrl = $place['maps_url'] ?: 'https://www.google.com/maps/search/?api=1&query='
    . rawurlencode($place['name'] . ', ' . $location);
$likes = (int) $place['likes_count'];
$views = (int) $place['views_count'];
$slug = (string) $place['slug'];
?>
<article class="card card--place" id="<?= e($slug) ?>">
    <div class="card__media">
        <img src="<?= e(uploaded($place['image'] ?? null)) ?>" alt=""
             loading="lazy" decoding="async" width="400" height="260">
    </div>
    <div class="card__body">
        <h3 class="card__title"><?= e($place['name']) ?></h3>
        <p class="card__meta"><?= icon('pin', 'icon icon--sm') ?> <?= e($location) ?></p>

        <div class="counts">
            <form class="like-form" method="post" action="/port-rider/<?= e(rawurlencode($slug)) ?>/suka" data-like-form>
                <?= Csrf::field() ?>
                <button class="like-button" type="submit" aria-pressed="<?= $liked ? 'true' : 'false' ?>">
                    <?= icon('heart', 'icon icon--sm') ?>
                    <span data-like-count aria-hidden="true"><?= e(formatCount($likes)) ?></span>
                    <span class="visually-hidden">Suka <?= e($place['name']) ?>, <span data-like-total><?= number_format($likes) ?></span> suka</span>
                </button>
            </form>
<?php if ($views > 0): ?>
            <span class="counts__item">
                <?= icon('eye', 'icon icon--sm') ?>
                <span aria-hidden="true"><?= e(formatCount($views)) ?></span>
                <span class="visually-hidden"><?= number_format($views) ?> kali lokasi dilihat</span>
            </span>
<?php endif; ?>
        </div>

        <a class="card__action" href="<?= e($mapsUrl) ?>" target="_blank" rel="noopener noreferrer"
           data-view-beacon="/port-rider/<?= e(rawurlencode($slug)) ?>/lihat">
            Click to View Location
            <span class="visually-hidden">bagi <?= e($place['name']) ?> (dibuka di tab baharu)</span>
        </a>
    </div>
</article>
