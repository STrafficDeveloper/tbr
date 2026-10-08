<?php

declare(strict_types=1);

use App\Core\Config;

/**
 * One business in the Port Rider directory. "Click to View Location" opens
 * the design's pop-up (photo, place, Google Maps / Waze directions), cloned
 * from the <template>; without JavaScript it goes straight to Google Maps.
 * Opening a map from the pop-up counts as a location view.
 *
 * @var array<string,mixed> $place a port_riders row
 */
$states = Config::get('site.states', []);
$location = implode(', ', array_filter([$place['city'] ?? null, $states[$place['state']] ?? $place['state']]));
$directions = directionLinks($place);
$views = (int) $place['views_count'];
$slug = (string) $place['slug'];
$image = uploaded($place['image'] ?? null);
$detailId = 'detail-place-' . $slug;
$beacon = '/port-rider/' . rawurlencode($slug) . '/lihat';
?>
<article class="card card--place" id="<?= e($slug) ?>">
    <div class="card__media">
        <img src="<?= e($image) ?>" alt="" loading="lazy" decoding="async" width="400" height="260">
    </div>
    <div class="card__body">
        <h3 class="card__title"><?= e($place['name']) ?></h3>
        <p class="card__meta"><?= icon('pin', 'icon icon--sm') ?> <?= e($location) ?></p>

<?php if ($views > 0): ?>
        <div class="counts">
            <span class="counts__item">
                <?= icon('eye', 'icon icon--sm') ?>
                <span aria-hidden="true"><?= e(formatCount($views)) ?></span>
                <span class="visually-hidden"><?= number_format($views) ?> kali lokasi dilihat</span>
            </span>
        </div>
<?php endif; ?>

        <a class="card__action card__link" href="<?= e($directions['google']) ?>" target="_blank" rel="noopener noreferrer"
           data-detail="<?= e($detailId) ?>">
            Click to View Location
            <span class="visually-hidden">bagi <?= e($place['name']) ?></span>
        </a>
    </div>

    <template id="<?= e($detailId) ?>">
        <div class="detail">
            <img class="detail__media" src="<?= e($image) ?>" alt="" width="640" height="400">
            <div class="detail__body">
                <h2 class="detail__title"><?= e($place['name']) ?></h2>
                <dl class="detail__facts">
                    <div><dt>Lokasi</dt><dd><?= e($location) ?></dd></div>
                </dl>
                <p class="detail__label">Get direction now</p>
                <div class="detail__actions">
                    <a class="btn btn--primary" href="<?= e($directions['google']) ?>" target="_blank" rel="noopener noreferrer"
                       data-view-beacon="<?= e($beacon) ?>">Google Maps</a>
                    <a class="btn btn--ghost" href="<?= e($directions['waze']) ?>" target="_blank" rel="noopener noreferrer"
                       data-view-beacon="<?= e($beacon) ?>">Waze</a>
                </div>
            </div>
        </div>
    </template>
</article>
