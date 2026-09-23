<?php

declare(strict_types=1);

use App\Core\Config;

/**
 * One business in the Port Rider directory. The design has no detail page, so
 * the card links out to the map and carries an id anchor for deep links.
 *
 * @var array<string,mixed> $place a port_riders row
 */
$states = Config::get('site.states', []);
$location = implode(', ', array_filter([$place['city'] ?? null, $states[$place['state']] ?? $place['state']]));
$mapsUrl = $place['maps_url'] ?: 'https://www.google.com/maps/search/?api=1&query='
    . rawurlencode($place['name'] . ', ' . $location);
?>
<article class="card card--place" id="<?= e($place['slug']) ?>">
    <div class="card__media">
        <img src="<?= e(uploaded($place['image'] ?? null)) ?>" alt=""
             loading="lazy" decoding="async" width="400" height="260">
    </div>
    <div class="card__body">
        <h3 class="card__title"><?= e($place['name']) ?></h3>
        <p class="card__meta"><?= icon('pin', 'icon icon--sm') ?> <?= e($location) ?></p>
        <?= component('counts', ['likes' => (int) $place['likes_count'], 'views' => (int) $place['views_count']]) ?>
        <a class="card__action" href="<?= e($mapsUrl) ?>" target="_blank" rel="noopener noreferrer">
            Click to View Location
            <span class="visually-hidden">bagi <?= e($place['name']) ?> (dibuka di tab baharu)</span>
        </a>
    </div>
</article>
